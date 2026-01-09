<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectFinanceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectFinancienController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $v = Validator::make($request->all(), [
            'description'     => ['required', 'string', 'max:190'],
            'quantity'        => ['required', 'integer', 'min:1', 'max:9999'],
            'unit_price_eur'  => ['required', 'string', 'max:32'],
        ]);

        if ($v->fails()) {
            return $this->renderFinance($request, $project, 422, $v->errors());
        }

        $data = $v->validated();

        $qty       = (int) $data['quantity'];
        $unitCents = $this->eurToCents((string) $data['unit_price_eur']);
        $unitCents = max(0, $unitCents);

        $project->financeItems()->create([
            'description'      => $data['description'],
            'quantity'         => $qty,
            'unit'             => 'pcs',
            'unit_price_cents' => $unitCents,
            'total_cents'      => $qty * $unitCents,
        ]);

        return $this->renderFinance($request, $project);
    }

    public function update(Request $request, Project $project, ProjectFinanceItem $financeItem)
    {
        if ((int) $financeItem->project_id !== (int) $project->id) abort(404);

        $v = Validator::make($request->all(), [
            'description'     => ['required', 'string', 'max:190'],
            'quantity'        => ['required', 'integer', 'min:1', 'max:9999'],
            'unit_price_eur'  => ['required', 'string', 'max:32'],
        ]);

        if ($v->fails()) {
            return $this->renderFinance($request, $project, 422, $v->errors());
        }

        $data = $v->validated();

        $qty       = (int) $data['quantity'];
        $unitCents = max(0, $this->eurToCents((string) $data['unit_price_eur']));

        $financeItem->description      = $data['description'];
        $financeItem->quantity         = $qty;
        $financeItem->unit             = $financeItem->unit ?: 'pcs';
        $financeItem->unit_price_cents = $unitCents;
        $financeItem->total_cents      = $qty * $unitCents;
        $financeItem->save();

        return $this->renderFinance($request, $project);
    }

    public function destroy(Request $request, Project $project, ProjectFinanceItem $financeItem)
    {
        if ((int) $financeItem->project_id !== (int) $project->id) abort(404);

        $financeItem->delete();

        return $this->renderFinance($request, $project);
    }

    /**
     * ✅ Bulk delete (zelfde patroon als taken)
     */
    public function bulkDestroy(Request $request, Project $project)
    {
        $v = Validator::make($request->all(), [
            'finance_item_ids' => ['required', 'array', 'min:1'],
            'finance_item_ids.*' => ['integer'],
        ]);

        if ($v->fails()) {
            return $this->renderFinance($request, $project, 422, $v->errors());
        }

        $ids = array_map('intval', (array) $v->validated()['finance_item_ids']);

        ProjectFinanceItem::query()
            ->where('project_id', $project->id)
            ->whereIn('id', $ids)
            ->delete();

        return $this->renderFinance($request, $project);
    }

    private function renderFinance(Request $request, Project $project, int $status = 200, $errors = null)
    {
        if ($request->header('HX-Request') === 'true') {
            $project->refresh()->load(['financeItems']);

            return response()->view('hub.projects.partials.finance', [
                'project'       => $project,
                'financeErrors' => $errors,

                'sectionWrap'   => "overflow-hidden rounded-2xl",
                'sectionHeader' => 'shrink-0 px-6 py-4 bg-[#191D38]/10',
                'sectionBody'   => 'bg-[#191D38]/5',
            ], $status);
        }

        return back();
    }

    private function eurToCents(string $value): int
    {
        $v = trim(str_replace(['€', ' '], '', $value));
        $v = str_replace(',', '.', $v);

        if ($v === '' || !is_numeric($v)) return 0;

        return (int) round(((float) $v) * 100);
    }
}