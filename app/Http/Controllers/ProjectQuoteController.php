<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectQuote;
use App\Models\ProjectQuoteItem;
use App\Support\ProjectLogger;
use App\Support\Toast;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProjectQuoteController extends Controller
{
    private function renderFinanceForQuotes(
        Request $request,
        Project $project,
        int $status = 200,
        ?string $toastMessage = null,
        string $toastType = 'success'
    ) {
        $project->refresh()->load([
            'financeItems',
            'quotes'   => fn ($q) => $q->orderBy('quote_number', 'asc'),
            'invoices' => fn ($q) => $q->orderBy('invoice_number', 'asc'),

            'logs' => fn ($q) => $q->latest()->limit(80),
            'logs.user',
        ]);

        $resp = response()->view('hub.projects.partials.finance_response', [
            'project' => $project,
            'sectionWrap'   => "overflow-hidden rounded-2xl",
            'sectionHeader' => 'shrink-0 px-6 py-4 bg-[#191D38]/10',
            'sectionBody'   => 'bg-[#191D38]/5',
        ], $status);

        if (Toast::isHtmx() && $toastMessage) {
            return Toast::attach($resp, $toastMessage, $toastType);
        }

        if (!Toast::isHtmx() && $toastMessage) {
            Toast::flash($toastMessage, $toastType);
            return back();
        }

        return $resp;
    }

    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'quote_date'   => ['required', 'date'],
            'expire_date'  => ['nullable', 'date', 'after_or_equal:quote_date'],
            'status'       => ['required', Rule::in(['draft', 'sent', 'accepted', 'cancelled'])],
            'notes'        => ['nullable', 'string', 'max:5000'],
            'vat_rate'     => ['required', 'numeric', 'min:0', 'max:100'],

            'items'                    => ['array'],
            'items.*.description'      => ['required', 'string', 'max:255'],
            'items.*.quantity'         => ['required', 'integer', 'min:1'],
            'items.*.unit_price_eur'   => ['nullable', 'string', 'max:50'],
            'items.*.unit_price_cents' => ['nullable', 'integer', 'min:0'],
        ]);

        $items = collect($data['items'] ?? [])
            ->map(function ($it) {
                $qty = max(1, (int) ($it['quantity'] ?? 1));

                $unitCents = array_key_exists('unit_price_cents', $it) && $it['unit_price_cents'] !== null
                    ? max(0, (int) $it['unit_price_cents'])
                    : $this->eurToCents((string) ($it['unit_price_eur'] ?? '0'));

                return [
                    'description'      => (string) ($it['description'] ?? ''),
                    'quantity'         => $qty,
                    'unit_price_cents' => $unitCents,
                    'line_total_cents' => $qty * $unitCents,
                ];
            })
            ->values();

        $subTotal = (int) $items->sum('line_total_cents');
        $vatRate  = (float) ($data['vat_rate'] ?? 0);
        $vatCents = (int) round($subTotal * $vatRate / 100);
        $total    = $subTotal + $vatCents;

        $createdQuote = null;

        DB::transaction(function () use ($project, $data, $items, $subTotal, $vatCents, $total, &$createdQuote) {
            $quoteDate   = Carbon::parse($data['quote_date']);
            $quoteNumber = $this->nextQuoteNumber($quoteDate);

            $createdQuote = ProjectQuote::create([
                'project_id'      => $project->id,
                'created_by'      => auth()->id(),
                'quote_number'    => $quoteNumber,
                'quote_date'      => $data['quote_date'],
                'expire_date'     => $data['expire_date'] ?? null,
                'status'          => $data['status'],
                'notes'           => $data['notes'] ?? null,
                'vat_rate'        => (int) $data['vat_rate'],
                'sub_total_cents' => $subTotal,
                'vat_cents'       => $vatCents,
                'total_cents'     => $total,
            ]);

            foreach ($items as $idx => $it) {
                ProjectQuoteItem::create([
                    'project_quote_id' => $createdQuote->id,
                    'position'         => $idx,
                    'description'      => $it['description'],
                    'quantity'         => $it['quantity'],
                    'unit_price_cents' => $it['unit_price_cents'],
                    'line_total_cents' => $it['line_total_cents'],
                ]);
            }
        });

        ProjectLogger::add(
            $project,
            $request->user(),
            'quote.created',
            'Offerte aangemaakt',
            [
                'quote_id'      => (int) ($createdQuote?->id ?? 0),
                'quote_number'  => (string) ($createdQuote?->quote_number ?? ''),
                'total_cents'   => (int) $total,
                'vat_cents'     => (int) $vatCents,
                'sub_total'     => (int) $subTotal,
            ]
        );

        return $this->renderFinanceForQuotes($request, $project, 200, 'Offerte aangemaakt');
    }

    public function updateStatus(Request $request, Project $project, ProjectQuote $quote)
    {
        abort_unless((int) $quote->project_id === (int) $project->id, 404);

        $data = $request->validate([
            'status'      => ['required', 'string', Rule::in(['draft', 'sent', 'accepted', 'cancelled'])],
            'quote_ids'   => ['sometimes', 'array', 'min:1'],
            'quote_ids.*' => ['integer'],
        ]);

        $newStatus = $data['status'];

        if (!empty($data['quote_ids'])) {
            $ids = array_map('intval', (array) $data['quote_ids']);

            ProjectQuote::query()
                ->where('project_id', $project->id)
                ->whereIn('id', $ids)
                ->update(['status' => $newStatus]);

            ProjectLogger::add($project, $request->user(), 'quote.bulk_status_updated', 'Offertestatus bijgewerkt', [
                'count' => count($ids),
                'ids'   => $ids,
                'status'=> $newStatus,
            ]);
        } else {
            $oldStatus = (string) $quote->status;
            $quote->status = $newStatus;
            $quote->save();

            ProjectLogger::add($project, $request->user(), 'quote.status_updated', 'Offertestatus bijgewerkt', [
                'quote_id' => (int) $quote->id,
                'from'     => $oldStatus,
                'to'       => $newStatus,
            ]);
        }

        return $this->renderFinanceForQuotes($request, $project, 200, 'Offertestatus bijgewerkt');
    }

    public function bulkUpdateStatus(Request $request, Project $project)
    {
        $data = $request->validate([
            'status'      => ['required', 'string', Rule::in(['draft', 'sent', 'accepted', 'cancelled'])],
            'quote_ids'   => ['required', 'array', 'min:1'],
            'quote_ids.*' => ['integer'],
        ]);

        $ids = array_map('intval', (array) $data['quote_ids']);

        ProjectQuote::query()
            ->where('project_id', $project->id)
            ->whereIn('id', $ids)
            ->update(['status' => $data['status']]);

        ProjectLogger::add($project, $request->user(), 'quote.bulk_status_updated', 'Offertestatus bijgewerkt', [
            'count' => count($ids),
            'ids'   => $ids,
            'status'=> (string) $data['status'],
        ]);

        return $this->renderFinanceForQuotes($request, $project, 200, 'Offertestatus bijgewerkt');
    }

    public function bulkDestroy(Request $request, Project $project)
    {
        $data = $request->validate([
            'quote_ids'   => ['required', 'array', 'min:1'],
            'quote_ids.*' => ['integer'],
        ]);

        $ids = array_map('intval', (array) $data['quote_ids']);

        ProjectLogger::add($project, $request->user(), 'quote.bulk_deleted', 'Offertes verwijderd', [
            'count' => count($ids),
            'ids'   => $ids,
        ]);

        ProjectQuote::query()
            ->where('project_id', $project->id)
            ->whereIn('id', $ids)
            ->delete();

        return $this->renderFinanceForQuotes($request, $project, 200, 'Offertes verwijderd');
    }

    public function destroy(Request $request, Project $project, ProjectQuote $quote)
    {
        abort_unless((int) $quote->project_id === (int) $project->id, 404);

        ProjectLogger::add($project, $request->user(), 'quote.deleted', 'Offerte verwijderd', [
            'quote_id'     => (int) $quote->id,
            'quote_number' => (string) $quote->quote_number,
        ]);

        DB::transaction(function () use ($quote) {
            $quote->items()->delete();
            $quote->delete();
        });

        return $this->renderFinanceForQuotes($request, $project, 200, 'Offerte verwijderd');
    }

    public function pdf(Project $project, ProjectQuote $quote)
    {
        abort_unless((int) $quote->project_id === (int) $project->id, 404);

        $quote->load(['items', 'project', 'creator']);

        $pdf = app('dompdf.wrapper')->loadView('hub.projects.quotes.pdf', [
            'project' => $project,
            'quote'   => $quote,
        ]);

        return $pdf->download('offerte-' . $quote->quote_number . '.pdf');
    }

    private function nextQuoteNumber(Carbon $date): string
    {
        $prefix = $date->format('Ym'); // 202601

        $last = DB::table('project_quotes')
            ->where('quote_number', 'like', $prefix . '%')
            ->orderByDesc('quote_number')
            ->lockForUpdate()
            ->value('quote_number');

        $seq = 1;
        if ($last) {
            $suffix = substr((string) $last, -4);
            if (ctype_digit($suffix)) {
                $seq = ((int) $suffix) + 1;
            }
        }

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    private function eurToCents(string $value): int
    {
        $v = trim((string) $value);
        $v = str_replace(['€', ' '], '', $v);
        $v = str_replace('.', '', $v);
        $v = str_replace(',', '.', $v);

        $f = is_numeric($v) ? (float) $v : 0.0;
        if (!is_finite($f)) return 0;

        return (int) round($f * 100);
    }
}
