<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectInvoice;
use App\Models\ProjectInvoiceItem;
use App\Support\ProjectLogger;
use App\Support\Toast;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProjectInvoiceController extends Controller
{
    private function renderFinanceForInvoices(
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
            'project'       => $project,
            'financeErrors' => null,
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
            'invoice_date' => ['required', 'date'],
            'due_date'     => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'status'       => ['required', Rule::in(['draft', 'sent', 'paid', 'cancelled'])],
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

        $invoice = null;

        DB::transaction(function () use ($project, $data, $items, $subTotal, $vatCents, $total, &$invoice) {
            $date          = Carbon::parse($data['invoice_date']);
            $invoiceNumber = $this->nextInvoiceNumber($date);

            $invoice = ProjectInvoice::create([
                'project_id'      => $project->id,
                'created_by'      => auth()->id(),
                'invoice_number'  => $invoiceNumber,
                'invoice_date'    => $data['invoice_date'],
                'due_date'        => $data['due_date'] ?? null,
                'status'          => $data['status'],
                'notes'           => $data['notes'] ?? null,
                'vat_rate'        => (int) $data['vat_rate'],
                'sub_total_cents' => $subTotal,
                'vat_cents'       => $vatCents,
                'total_cents'     => $total,
            ]);

            foreach ($items as $idx => $it) {
                ProjectInvoiceItem::create([
                    'project_invoice_id' => $invoice->id,
                    'position'           => $idx,
                    'description'        => $it['description'],
                    'quantity'           => $it['quantity'],
                    'unit_price_cents'   => $it['unit_price_cents'],
                    'line_total_cents'   => $it['line_total_cents'],
                ]);
            }
        });

        ProjectLogger::add(
            $project,
            $request->user(),
            'invoice.created',
            'Factuur aangemaakt',
            [
                'invoice_id'     => (int) ($invoice->id ?? 0),
                'invoice_number' => (string) ($invoice->invoice_number ?? ''),
                'status'         => (string) ($data['status'] ?? ''),
                'items_count'    => (int) count($data['items'] ?? []),
                'total_cents'    => (int) ($invoice->total_cents ?? 0),
            ]
        );

        return $this->renderFinanceForInvoices($request, $project, 200, 'Factuur aangemaakt');
    }

    public function updateStatus(Request $request, Project $project, ProjectInvoice $invoice)
    {
        abort_unless((int) $invoice->project_id === (int) $project->id, 404);

        $data = $request->validate([
            'status'        => ['required', 'string', Rule::in(['draft', 'sent', 'paid', 'cancelled'])],
            'invoice_ids'   => ['sometimes', 'array', 'min:1'],
            'invoice_ids.*' => ['integer'],
        ]);

        $newStatus = (string) $data['status'];

        $ids = !empty($data['invoice_ids'])
            ? array_map('intval', (array) $data['invoice_ids'])
            : [(int) $invoice->id];

        ProjectInvoice::query()
            ->where('project_id', $project->id)
            ->whereIn('id', $ids)
            ->update(['status' => $newStatus]);

        ProjectLogger::add(
            $project,
            $request->user(),
            'invoice.status_updated',
            'Factuurstatus bijgewerkt',
            ['status' => $newStatus, 'count' => count($ids), 'ids' => $ids]
        );

        return $this->renderFinanceForInvoices($request, $project, 200, 'Factuurstatus bijgewerkt');
    }

    public function bulkUpdateStatus(Request $request, Project $project)
    {
        $data = $request->validate([
            'status'        => ['required', 'string', Rule::in(['draft', 'sent', 'paid', 'cancelled'])],
            'invoice_ids'   => ['required', 'array', 'min:1'],
            'invoice_ids.*' => ['integer'],
        ]);

        $ids = array_map('intval', (array) $data['invoice_ids']);

        ProjectInvoice::query()
            ->where('project_id', $project->id)
            ->whereIn('id', $ids)
            ->update(['status' => (string) $data['status']]);

        ProjectLogger::add(
            $project,
            $request->user(),
            'invoice.status_updated',
            'Factuurstatus bijgewerkt',
            ['status' => (string) $data['status'], 'count' => count($ids), 'ids' => $ids]
        );

        return $this->renderFinanceForInvoices($request, $project, 200, 'Factuurstatus bijgewerkt');
    }

    public function pdf(Project $project, ProjectInvoice $invoice)
    {
        abort_unless((int) $invoice->project_id === (int) $project->id, 404);

        $invoice->load(['items', 'project', 'creator']);

        $pdf = app('dompdf.wrapper')->loadView('hub.projects.invoices.pdf', [
            'project' => $project,
            'invoice' => $invoice,
        ]);

        return $pdf->download('factuur-' . $invoice->invoice_number . '.pdf');
    }

    public function bulkDestroy(Request $request, Project $project)
    {
        $data = $request->validate([
            'invoice_ids'   => ['required', 'array', 'min:1'],
            'invoice_ids.*' => ['integer'],
        ]);

        $ids = array_map('intval', (array) $data['invoice_ids']);

        ProjectLogger::add(
            $project,
            $request->user(),
            'invoice.bulk_deleted',
            'Facturen verwijderd',
            ['count' => count($ids), 'ids' => $ids]
        );

        ProjectInvoice::query()
            ->where('project_id', $project->id)
            ->whereIn('id', $ids)
            ->delete(); // items cascaden via FK

        return $this->renderFinanceForInvoices($request, $project, 200, 'Facturen verwijderd');
    }

    public function destroy(Request $request, Project $project, ProjectInvoice $invoice)
    {
        abort_unless((int) $invoice->project_id === (int) $project->id, 404);

        ProjectLogger::add(
            $project,
            $request->user(),
            'invoice.deleted',
            'Factuur verwijderd',
            [
                'invoice_id'     => (int) $invoice->id,
                'invoice_number' => (string) $invoice->invoice_number,
            ]
        );

        DB::transaction(function () use ($invoice) {
            $invoice->items()->delete();
            $invoice->delete();
        });

        return $this->renderFinanceForInvoices($request, $project, 200, 'Factuur verwijderd');
    }

    private function nextInvoiceNumber(Carbon $date): string
    {
        $prefix = 'F' . $date->format('Ym'); // F202601

        $last = DB::table('project_invoices')
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->lockForUpdate()
            ->value('invoice_number');

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
