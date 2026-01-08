<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTaskLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class ProjectTakenController extends Controller
{
    private function assignees()
    {
        // Als je rollen in een kolom hebt:
        return User::query()
            ->whereIn('rol', ['team-manager', 'client-manager', 'fotograaf', 'admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'rol']);

        // Spatie variant:
        // return User::role(['team-manager','client-manager','fotograaf','admin'])
        //   ->orderBy('name')->get(['id','name']);
    }

    public function updateStatus(Request $request, Project $project, ProjectTask $task)
    {
        if ((int) $task->project_id !== (int) $project->id) abort(404);

        $data = $request->validate([
            'status' => ['required', 'string', Rule::in(['pending', 'active', 'done', 'cancelled', 'archived'])],
            'task_ids' => ['sometimes', 'array', 'min:1'],
            'task_ids.*' => ['integer'],
        ]);

        $newStatus = $data['status'];

        // ✅ Als task_ids[] is meegestuurd: bulk update binnen dit project
        if (!empty($data['task_ids'])) {
            $taskIds = array_map('intval', $data['task_ids']);

            $tasks = ProjectTask::query()
                ->where('project_id', $project->id)
                ->whereIn('id', $taskIds)
                ->get();

            foreach ($tasks as $t) {
                $t->status = $newStatus;

                if ($newStatus === 'done') {
                    $t->completed_at = $t->completed_at ?? now();
                } else {
                    $t->completed_at = null;
                }

                $t->save();
            }
        } else {
            // ✅ Anders: single update (huidig gedrag)
            $task->status = $newStatus;

            if ($newStatus === 'done') {
                $task->completed_at = $task->completed_at ?? now();
            } else {
                $task->completed_at = null;
            }

            $task->save();
        }

        if ($request->header('HX-Request') === 'true') {
            $project->refresh()->load(['tasks.assignedUser']);

            return response()->view('hub.projects.partials.tasks', [
                'project' => $project,
                'assignees' => $this->assignees(),
                'sectionHeader' => 'shrink-0 px-6 py-4 bg-[#191D38]/10',
                'sectionBody'   => 'bg-[#191D38]/5',
            ]);
        }

        return back();
    }

    public function bulkUpdateStatus(Request $request, Project $project)
    {
        $data = $request->validate([
            'status' => ['required', 'string', Rule::in(['pending', 'active', 'done', 'cancelled', 'archived'])],
            'task_ids' => ['required', 'array', 'min:1'],
            'task_ids.*' => ['integer'],
        ]);

        $taskIds = array_map('intval', $data['task_ids']);
        $newStatus = $data['status'];

        $tasks = ProjectTask::query()
            ->where('project_id', $project->id)
            ->whereIn('id', $taskIds)
            ->get();

        foreach ($tasks as $task) {
            $task->status = $newStatus;

            if ($newStatus === 'done') {
                $task->completed_at = $task->completed_at ?? now();
            } else {
                $task->completed_at = null;
            }

            $task->save();
        }

        if ($request->header('HX-Request') === 'true') {
            $project->refresh()->load(['tasks.assignedUser']);

            return response()->view('hub.projects.partials.tasks', [
                'project' => $project,
                'assignees' => $this->assignees(),
                'sectionHeader' => 'shrink-0 px-6 py-4 bg-[#191D38]/10',
                'sectionBody'   => 'bg-[#191D38]/5',
            ]);
        }

        return back();
    }

    public function updateDueDate(Request $request, Project $project, ProjectTask $task)
    {
        if ((int) $task->project_id !== (int) $project->id) abort(404);

        $data = $request->validate([
            'due_date' => ['nullable', 'date_format:Y-m-d'],
            'task_ids' => ['sometimes', 'array', 'min:1'],
            'task_ids.*' => ['integer'],
        ]);

        $due = $data['due_date'] ?? null;

        // ✅ Server-side guard: weekend + verleden blokkeren
        if ($due !== null) {
            $date = Carbon::createFromFormat('Y-m-d', $due);

            if ($date->isWeekend()) {
                return back()->withErrors(['due_date' => 'Kies een werkdag (maandag t/m vrijdag).']);
            }

            if ($date->lt(Carbon::today())) {
                return back()->withErrors(['due_date' => 'Kies een datum vanaf vandaag.']);
            }
        }

        // ✅ Bulk als task_ids[] is meegestuurd
        if (!empty($data['task_ids'])) {
            $taskIds = array_map('intval', $data['task_ids']);

            ProjectTask::query()
                ->where('project_id', $project->id)
                ->whereIn('id', $taskIds)
                ->update(['due_date' => $due]);
        } else {
            $task->due_date = $due;
            $task->save();
        }

        if ($request->header('HX-Request') === 'true') {
            $project->refresh()->load(['tasks.assignedUser']);

            return response()->view('hub.projects.partials.tasks', [
                'project' => $project,
                'assignees' => $this->assignees(),
                'sectionHeader' => 'shrink-0 px-6 py-4 bg-[#191D38]/10',
                'sectionBody'   => 'bg-[#191D38]/5',
            ]);
        }

        return back();
    }

    public function updateAssignee(Request $request, Project $project, ProjectTask $task)
    {
        if ((int) $task->project_id !== (int) $project->id) abort(404);

        $data = $request->validate([
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'task_ids' => ['sometimes', 'array', 'min:1'],
            'task_ids.*' => ['integer'],
        ]);

        $userId = $data['assigned_user_id'] ?? null;

        // Security: alleen toestaan voor bepaalde rollen
        if ($userId !== null) {
            $allowed = User::query()
                ->where('id', $userId)
                ->whereIn('rol', ['team-manager', 'client-manager', 'fotograaf', 'admin'])
                ->exists();

            if (!$allowed) abort(403);
        }

        // ✅ Bulk als task_ids[] is meegestuurd
        if (!empty($data['task_ids'])) {
            $taskIds = array_map('intval', $data['task_ids']);

            ProjectTask::query()
                ->where('project_id', $project->id)
                ->whereIn('id', $taskIds)
                ->update(['assigned_user_id' => $userId]);
        } else {
            $task->assigned_user_id = $userId;
            $task->save();
        }

        if ($request->header('HX-Request') === 'true') {
            $project->refresh()->load(['tasks.assignedUser']);

            return response()->view('hub.projects.partials.tasks', [
                'project' => $project,
                'assignees' => $this->assignees(),
                'sectionHeader' => 'shrink-0 px-6 py-4 bg-[#191D38]/10',
                'sectionBody'   => 'bg-[#191D38]/5',
            ]);
        }

        return back();
    }

    public function locationSuggest(Request $request, Project $project)
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 3) {
            return response()->json(['items' => []]);
        }

        $res = Http::timeout(5)->get('https://api.pdok.nl/bzk/locatieserver/search/v3_1/suggest', [
            'q' => $q,
            'fq' => 'type:adres',
            'rows' => 8,
        ]);

        if (!$res->ok()) {
            return response()->json(['items' => []]);
        }

        $docs = data_get($res->json(), 'response.docs', []);

        $items = collect($docs)->map(function ($d) {
            $label = (string) ($d['weergavenaam'] ?? '');
            return [
                'label' => $label,
                'value' => $label,
            ];
        })->filter(fn ($x) => $x['label'] !== '')->values();

        return response()->json(['items' => $items]);
    }

    public function updateLocation(Request $request, Project $project, ProjectTask $task)
    {
        if ((int) $task->project_id !== (int) $project->id) abort(404);

        $data = $request->validate([
            'location' => ['nullable', 'string', 'max:255'],
            'task_ids' => ['sometimes', 'array', 'min:1'],
            'task_ids.*' => ['integer'],
        ]);

        $location = trim((string) ($data['location'] ?? ''));
        $location = $location === '' ? null : $location;

        if (!empty($data['task_ids'])) {
            $taskIds = array_map('intval', $data['task_ids']);

            ProjectTask::query()
                ->where('project_id', $project->id)
                ->whereIn('id', $taskIds)
                ->update(['location' => $location]);
        } else {
            $task->location = $location;
            $task->save();
        }

        if ($request->header('HX-Request') === 'true') {
            $project->refresh()->load(['tasks.assignedUser']);

            return response()->view('hub.projects.partials.tasks', [
                'project' => $project,
                'assignees' => $this->assignees(),
                'sectionHeader' => 'shrink-0 px-6 py-4 bg-[#191D38]/10',
                'sectionBody'   => 'bg-[#191D38]/5',
            ]);
        }

        return back();
    }

    public function updateName(Request $request, Project $project, ProjectTask $task)
    {
        if ((int) $task->project_id !== (int) $project->id) abort(404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
        ]);

        $new = trim((string) $data['name']);
        if ($new === '') $new = (string) ($task->name ?? '');

        // update
        $task->name = $new;
        $task->save();

        if ($request->header('HX-Request') === 'true') {
            $project->refresh()->load(['tasks.assignedUser']);

            return response()->view('hub.projects.partials.tasks', [
                'project' => $project,
                'assignees' => $this->assignees(),
                'sectionHeader' => 'shrink-0 px-6 py-4 bg-[#191D38]/10',
                'sectionBody'   => 'bg-[#191D38]/5',
            ]);
        }

        return back();
    }

    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
        ]);

        DB::transaction(function () use ($project, $data) {
            $max = ProjectTask::query()
                ->where('project_id', $project->id)
                ->lockForUpdate()
                ->max('sort_order');

            $nextOrder = is_null($max) ? 1 : ((int) $max + 1);

            $project->tasks()->create([
                'name' => $data['name'],
                'status' => 'pending',
                'assigned_user_id' => null,
                'location' => null,
                'due_date' => null,
                'completed_at' => null,
                'sort_order' => $nextOrder,
                'description' => null,
            ]);
        });

        if ($request->header('HX-Request') === 'true') {
            $project->refresh()->load(['tasks.assignedUser']);

            return response()->view('hub.projects.partials.tasks', [
                'project' => $project,
                'assignees' => $this->assignees(),
                'sectionHeader' => 'shrink-0 px-6 py-4 bg-[#191D38]/10',
                'sectionBody'   => 'bg-[#191D38]/5',
            ]);
        }

        return back();
    }

    public function destroy(Request $request, Project $project, ProjectTask $task)
    {
        if ((int) $task->project_id !== (int) $project->id) abort(404);

        $task->delete();

        if ($request->header('HX-Request') === 'true') {
            $project->refresh()->load(['tasks.assignedUser']);

            return response()->view('hub.projects.partials.tasks', [
                'project' => $project,
                'assignees' => $this->assignees(),
                'sectionHeader' => 'shrink-0 px-6 py-4 bg-[#191D38]/10',
                'sectionBody'   => 'bg-[#191D38]/5',
            ]);
        }

        return back();
    }

    public function bulkDestroy(Request $request, Project $project)
    {
        $data = $request->validate([
            'task_ids' => ['required', 'array', 'min:1'],
            'task_ids.*' => ['integer'],
        ]);

        $taskIds = array_map('intval', $data['task_ids']);

        ProjectTask::query()
            ->where('project_id', $project->id)
            ->whereIn('id', $taskIds)
            ->delete();

        if ($request->header('HX-Request') === 'true') {
            $project->refresh()->load(['tasks.assignedUser']);

            return response()->view('hub.projects.partials.tasks', [
                'project' => $project,
                'assignees' => $this->assignees(),
                'sectionHeader' => 'shrink-0 px-6 py-4 bg-[#191D38]/10',
                'sectionBody'   => 'bg-[#191D38]/5',
            ]);
        }

        return back();
    }

    public function show(Request $request, Project $project, ProjectTask $task)
    {
        if ((int) $task->project_id !== (int) $project->id) abort(404);

        $task->load(['assignedUser', 'logs.user']);
        $project->load(['client']);

        return view('hub.projects.tasks.show', [
            'user'    => $request->user(),
            'project' => $project,
            'task'    => $task,
        ]);
    }

    /**
     * ✅ Taakbeschrijving opslaan + loggen (wie/wanneer/wat).
     */
    public function updateDescription(Request $request, Project $project, ProjectTask $task)
    {
        if ((int) $task->project_id !== (int) $project->id) abort(404);

        $data = $request->validate([
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $before = (string) ($task->description ?? '');

        $desc = trim((string) ($data['description'] ?? ''));
        $desc = $desc === '' ? null : $desc;

        $after = (string) ($desc ?? '');

        // niets veranderd -> klaar
        if ($before === $after) {
            return $request->header('HX-Request') === 'true'
                ? response('', 204)
                : back();
        }

        $task->description = $desc;
        $task->save();

        // event bepalen
        $wasEmpty = trim($before) === '';
        $isEmpty  = trim($after) === '';

        $event = match (true) {
            $wasEmpty && !$isEmpty => 'description_filled',
            !$wasEmpty && $isEmpty => 'description_cleared',
            default                => 'description_updated',
        };

        // log schrijven
        ProjectTaskLog::create([
            'project_id'      => $project->id,
            'project_task_id' => $task->id,
            'user_id'         => $request->user()?->id,
            'event'           => $event,
            'field'           => 'description',
            'old_value'       => $before,
            'new_value'       => $after,
        ]);

        return $request->header('HX-Request') === 'true'
            ? response('', 204)
            : back();
    }
}