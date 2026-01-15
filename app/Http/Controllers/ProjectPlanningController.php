<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectPlanningItem;
use App\Models\User;
use App\Support\ProjectLogger;
use App\Support\Toast;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectPlanningController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $v = Validator::make($request->all(), [
            'notes'            => ['required', 'string', 'max:190'],
            'location'         => ['nullable', 'string', 'max:255'],
            'assignee_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'start_at'         => ['required', 'date'],
            'end_at'           => ['required', 'date', 'after:start_at'],
        ]);

        if ($v->fails()) {
            return $this->renderPlanning($request, $project, 422, $v->errors(), 'Controleer de velden', 'error');
        }

        $data = $v->validated();

        $item = $project->planningItems()->create([
            'notes'            => (string) $data['notes'],
            'location'         => $data['location'] !== null ? (string) $data['location'] : null,
            'assignee_user_id' => $data['assignee_user_id'] !== null ? (int) $data['assignee_user_id'] : null,
            'start_at'         => Carbon::parse($data['start_at']),
            'end_at'           => Carbon::parse($data['end_at']),
        ]);

        ProjectLogger::add(
            $project,
            $request->user(),
            'planning_item.created',
            'Planning item toegevoegd',
            [
                'planning_item_id' => (int) $item->id,
                'notes'            => (string) $item->notes,
                'location'         => (string) ($item->location ?? ''),
                'assignee_user_id' => $item->assignee_user_id ? (int) $item->assignee_user_id : null,
                'start_at'         => $item->start_at ? Carbon::parse($item->start_at)->toIso8601String() : null,
                'end_at'           => $item->end_at ? Carbon::parse($item->end_at)->toIso8601String() : null,
            ]
        );

        return $this->renderPlanning($request, $project, 200, null, 'Planning item toegevoegd');
    }

    public function destroy(Request $request, Project $project, ProjectPlanningItem $planningItem)
    {
        abort_unless((int) $planningItem->project_id === (int) $project->id, 404);

        ProjectLogger::add(
            $project,
            $request->user(),
            'planning_item.deleted',
            'Planning item verwijderd',
            [
                'planning_item_id' => (int) $planningItem->id,
                'notes'            => (string) ($planningItem->notes ?? ''),
                'location'         => (string) ($planningItem->location ?? ''),
                'assignee_user_id' => $planningItem->assignee_user_id ? (int) $planningItem->assignee_user_id : null,
                'start_at'         => $planningItem->start_at ? Carbon::parse($planningItem->start_at)->toIso8601String() : null,
                'end_at'           => $planningItem->end_at ? Carbon::parse($planningItem->end_at)->toIso8601String() : null,
            ]
        );

        $planningItem->delete();

        return $this->renderPlanning($request, $project, 200, null, 'Planning item verwijderd');
    }

    private function renderPlanning(
        Request $request,
        Project $project,
        int $status = 200,
        $errors = null,
        ?string $toastMessage = null,
        string $toastType = 'success'
    ) {
        if (Toast::isHtmx()) {
            $project->refresh()->load([
                'planningItems' => fn ($q) => $q->orderBy('start_at', 'asc'),
                'planningItems.assignee',

                // logboek OOB update
                'logs' => fn ($q) => $q->latest()->limit(80),
                'logs.user',
            ]);

            // Assignees voor dropdown (pas evt. filter toe op rol)
            $planningAssignees = User::query()->orderBy('name')->get();

            $resp = response()->view('hub.projects.partials.planning_response', [
                'project'            => $project,
                'planningErrors'     => $errors,
                'planningAssignees'  => $planningAssignees,
                'sectionWrap'        => "overflow-hidden rounded-2xl",
                'sectionHeader'      => 'shrink-0 px-6 py-4 bg-[#191D38]/10',
                'sectionBody'        => 'bg-[#191D38]/5',
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
}
