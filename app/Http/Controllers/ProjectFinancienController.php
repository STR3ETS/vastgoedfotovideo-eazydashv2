<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectFinanceItem;
use App\Support\ProjectLogger;
use App\Support\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectFinancienController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $v = Validator::make($request->all(), [
            'description'    => ['required', 'string', 'max:190'],
            'quantity'       => ['required', 'integer', 'min:1', 'max:9999'],
            'unit_price_eur' => ['required', 'string', 'max:32'],
        ]);

        if ($v->fails()) {
            return $this->renderFinance($request, $project, 422, $v->errors(), 'Controleer de velden', 'error');
        }

        $data = $v->validated();

        $qty       = (int) $data['quantity'];
        $unitCents = max(0, $this->eurToCents((string) $data['unit_price_eur']));

        $item = $project->financeItems()->create([
            'description'      => (string) $data['description'],
            'quantity'         => $qty,
            'unit'             => 'pcs',
            'unit_price_cents' => $unitCents,
            'total_cents'      => $qty * $unitCents,
        ]);

        // ✅ Logboek = exact tegelijk met toast
        ProjectLogger::add(
            $project,
            $request->user(),
            'finance_item.created',
            'Financieel item toegevoegd',
            [
                'finance_item_id'  => (int) $item->id,
                'description'      => (string) $item->description,
                'quantity'         => (int) $item->quantity,
                'unit_price_cents' => (int) $item->unit_price_cents,
                'total_cents'      => (int) $item->total_cents,
            ]
        );

        return $this->renderFinance($request, $project, 200, null, 'Financieel item toegevoegd');
    }

    public function update(Request $request, Project $project, ProjectFinanceItem $financeItem)
    {
        abort_unless((int) $financeItem->project_id === (int) $project->id, 404);

        $v = Validator::make($request->all(), [
            'description'    => ['required', 'string', 'max:190'],
            'quantity'       => ['required', 'integer', 'min:1', 'max:9999'],
            'unit_price_eur' => ['required', 'string', 'max:32'],
        ]);

        if ($v->fails()) {
            return $this->renderFinance($request, $project, 422, $v->errors(), 'Controleer de velden', 'error');
        }

        $data = $v->validated();

        $old = [
            'description'      => (string) ($financeItem->description ?? ''),
            'quantity'         => (int) ($financeItem->quantity ?? 1),
            'unit_price_cents' => (int) ($financeItem->unit_price_cents ?? 0),
            'total_cents'      => (int) ($financeItem->total_cents ?? 0),
        ];

        $qty       = (int) $data['quantity'];
        $unitCents = max(0, $this->eurToCents((string) $data['unit_price_eur']));

        $financeItem->description      = (string) $data['description'];
        $financeItem->quantity         = $qty;
        $financeItem->unit             = $financeItem->unit ?: 'pcs';
        $financeItem->unit_price_cents = $unitCents;
        $financeItem->total_cents      = $qty * $unitCents;
        $financeItem->save();

        ProjectLogger::add(
            $project,
            $request->user(),
            'finance_item.updated',
            'Financieel item bijgewerkt',
            [
                'finance_item_id' => (int) $financeItem->id,
                'old' => $old,
                'new' => [
                    'description'      => (string) $financeItem->description,
                    'quantity'         => (int) $financeItem->quantity,
                    'unit_price_cents' => (int) $financeItem->unit_price_cents,
                    'total_cents'      => (int) $financeItem->total_cents,
                ],
            ]
        );

        return $this->renderFinance($request, $project, 200, null, 'Financieel item bijgewerkt');
    }

    public function destroy(Request $request, Project $project, ProjectFinanceItem $financeItem)
    {
        abort_unless((int) $financeItem->project_id === (int) $project->id, 404);

        // ✅ log vóór delete
        ProjectLogger::add(
            $project,
            $request->user(),
            'finance_item.deleted',
            'Financieel item verwijderd',
            [
                'finance_item_id'  => (int) $financeItem->id,
                'description'      => (string) ($financeItem->description ?? ''),
                'quantity'         => (int) ($financeItem->quantity ?? 1),
                'unit_price_cents' => (int) ($financeItem->unit_price_cents ?? 0),
                'total_cents'      => (int) ($financeItem->total_cents ?? 0),
            ]
        );

        $financeItem->delete();

        return $this->renderFinance($request, $project, 200, null, 'Financieel item verwijderd');
    }

    public function bulkDestroy(Request $request, Project $project)
    {
        $v = Validator::make($request->all(), [
            'finance_item_ids'   => ['required', 'array', 'min:1'],
            'finance_item_ids.*' => ['integer'],
        ]);

        if ($v->fails()) {
            return $this->renderFinance($request, $project, 422, $v->errors(), 'Selecteer minimaal 1 item', 'error');
        }

        $ids = array_values(array_unique(array_map('intval', (array) $v->validated()['finance_item_ids'])));

        ProjectLogger::add(
            $project,
            $request->user(),
            'finance_item.bulk_deleted',
            'Financiële items verwijderd',
            [
                'count' => count($ids),
                'ids'   => $ids,
            ]
        );

        ProjectFinanceItem::query()
            ->where('project_id', $project->id)
            ->whereIn('id', $ids)
            ->delete();

        return $this->renderFinance($request, $project, 200, null, 'Financiële items verwijderd');
    }

    private function renderFinance(
        Request $request,
        Project $project,
        int $status = 200,
        $errors = null,
        ?string $toastMessage = null,
        string $toastType = 'success'
    ) {
        if (Toast::isHtmx()) {
            $project->refresh()->load([
                'financeItems',
                'quotes'   => fn ($q) => $q->orderBy('quote_number', 'asc'),
                'invoices' => fn ($q) => $q->orderBy('invoice_number', 'asc'),

                // ✅ logboek data mee, zodat finance_response OOB kan updaten
                'logs' => fn ($q) => $q->latest()->limit(80),
                'logs.user',
            ]);

            $resp = response()->view('hub.projects.partials.finance_response', [
                'project'       => $project,
                'financeErrors' => $errors,
                'sectionWrap'   => "overflow-hidden rounded-2xl",
                'sectionHeader' => 'shrink-0 px-6 py-4 bg-[#191D38]/10',
                'sectionBody'   => 'bg-[#191D38]/5',
            ], $status);

            if ($toastMessage) {
                return Toast::attach($resp, $toastMessage, $toastType);
            }

            if ($errors) {
                $first = method_exists($errors, 'first') ? $errors->first() : null;
                if ($first) {
                    return Toast::attach($resp, (string) $first, 'error');
                }
            }

            return $resp;
        }

        if ($errors) {
            return back()->withErrors($errors)->withInput();
        }

        if ($toastMessage) {
            Toast::flash($toastMessage, $toastType);
        }

        return back();
    }

    private function eurToCents(string $value): int
    {
        $v = trim((string) $value);
        $v = str_replace(['€', ' '], '', $v);
        // EU notatie: 1.234,56 -> 1234.56
        $v = str_replace('.', '', $v);
        $v = str_replace(',', '.', $v);

        $f = is_numeric($v) ? (float) $v : 0.0;
        if (!is_finite($f)) return 0;

        return (int) round($f * 100);
    }
}
