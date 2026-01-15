<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTaskLog;
use App\Models\User;
use App\Support\ProjectLogger;
use App\Support\Toast;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class ProjectTakenController extends Controller
{
    private function assignees()
    {
        return User::query()
            ->whereIn('rol', ['team-manager', 'client-manager', 'fotograaf', 'admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'rol']);
    }

    private function renderTasks(Project $project, int $status = 200)
    {
        $project->refresh()->load([
            'tasks.assignedUser',

            // ✅ logboek voor OOB update
            'logs' => fn ($q) => $q->latest()->limit(80),
            'logs.user',
        ]);

        return response()->view('hub.projects.partials.tasks_response', [
            'project'       => $project,
            'assignees'     => $this->assignees(),
            'sectionHeader' => 'shrink-0 px-6 py-4 bg-[#191D38]/10',
            'sectionBody'   => 'bg-[#191D38]/5',
            'sectionWrap'   => "overflow-hidden rounded-2xl",
        ], $status);
    }

    private function respondTasks(
        Request $request,
        Project $project,
        string $message,
        string $type = 'success',
        int $status = 200,
        ?string $event = null,
        array $meta = []
    ) {
        if ($event) {
            ProjectLogger::add($project, $request->user(), $event, $message, $meta + ['level' => $type]);
        }

        if (Toast::isHtmx()) {
            return Toast::attach($this->renderTasks($project, $status), $message, $type);
        }

        Toast::flash($message, $type);
        return back();
    }

    private function respondTasksError(
        Request $request,
        Project $project,
        string $message,
        string $field = 'general',
        int $status = 422,
        ?string $event = null,
        array $meta = []
    ) {
        if ($event) {
            ProjectLogger::add($project, $request->user(), $event, $message, $meta + ['level' => 'error']);
        }

        if (Toast::isHtmx()) {
            return Toast::attach($this->renderTasks($project, $status), $message, 'error');
        }

        return back()->withErrors([$field => $message])->withInput();
    }

    public function updateStatus(Request $request, Project $project, ProjectTask $task)
    {
        if ((int) $task->project_id !== (int) $project->id) abort(404);

        $data = $request->validate([
            'status'     => ['required', 'string', Rule::in(['pending', 'active', 'done', 'cancelled', 'archived'])],
            'task_ids'   => ['sometimes', 'array', 'min:1'],
            'task_ids.*' => ['integer'],
        ]);

        $newStatus = (string) $data['status'];

        $ids = !empty($data['task_ids'])
            ? array_map('intval', (array) $data['task_ids'])
            : [(int) $task->id];

        $tasks = ProjectTask::query()
            ->where('project_id', $project->id)
            ->whereIn('id', $ids)
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

        return $this->respondTasks(
            $request,
            $project,
            'Status bijgewerkt',
            'success',
            200,
            'task.status_updated',
            ['status' => $newStatus, 'count' => count($ids), 'ids' => $ids]
        );
    }

    public function bulkUpdateStatus(Request $request, Project $project)
    {
        $data = $request->validate([
            'status'     => ['required', 'string', Rule::in(['pending', 'active', 'done', 'cancelled', 'archived'])],
            'task_ids'   => ['required', 'array', 'min:1'],
            'task_ids.*' => ['integer'],
        ]);

        $ids       = array_map('intval', (array) $data['task_ids']);
        $newStatus = (string) $data['status'];

        $tasks = ProjectTask::query()
            ->where('project_id', $project->id)
            ->whereIn('id', $ids)
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

        return $this->respondTasks(
            $request,
            $project,
            'Status bijgewerkt',
            'success',
            200,
            'task.status_updated',
            ['status' => $newStatus, 'count' => count($ids), 'ids' => $ids]
        );
    }

    public function updateDueDate(Request $request, Project $project, ProjectTask $task)
    {
        if ((int) $task->project_id !== (int) $project->id) abort(404);

        $data = $request->validate([
            'due_date'   => ['nullable', 'date_format:Y-m-d'],
            'task_ids'   => ['sometimes', 'array', 'min:1'],
            'task_ids.*' => ['integer'],
        ]);

        $due = $data['due_date'] ?? null;

        if ($due !== null) {
            $date = Carbon::createFromFormat('Y-m-d', $due);

            if ($date->isWeekend()) {
                return $this->respondTasksError(
                    $request,
                    $project,
                    'Kies een werkdag (maandag t/m vrijdag).',
                    'due_date',
                    422,
                    'task.due_date_invalid',
                    ['due_date' => $due]
                );
            }

            if ($date->lt(Carbon::today())) {
                return $this->respondTasksError(
                    $request,
                    $project,
                    'Kies een datum vanaf vandaag.',
                    'due_date',
                    422,
                    'task.due_date_invalid',
                    ['due_date' => $due]
                );
            }
        }

        $ids = !empty($data['task_ids'])
            ? array_map('intval', (array) $data['task_ids'])
            : [(int) $task->id];

        ProjectTask::query()
            ->where('project_id', $project->id)
            ->whereIn('id', $ids)
            ->update(['due_date' => $due]);

        $msg = $due ? 'Vervaldatum bijgewerkt' : 'Vervaldatum gereset';

        return $this->respondTasks(
            $request,
            $project,
            $msg,
            'success',
            200,
            'task.due_date_updated',
            ['due_date' => $due, 'count' => count($ids), 'ids' => $ids]
        );
    }

    public function updateAssignee(Request $request, Project $project, ProjectTask $task)
    {
        if ((int) $task->project_id !== (int) $project->id) abort(404);

        $data = $request->validate([
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'task_ids'         => ['sometimes', 'array', 'min:1'],
            'task_ids.*'       => ['integer'],
        ]);

        $userId = $data['assigned_user_id'] ?? null;

        if ($userId !== null) {
            $allowed = User::query()
                ->where('id', $userId)
                ->whereIn('rol', ['team-manager', 'client-manager', 'fotograaf', 'admin'])
                ->exists();

            if (!$allowed) abort(403);
        }

        $ids = !empty($data['task_ids'])
            ? array_map('intval', (array) $data['task_ids'])
            : [(int) $task->id];

        ProjectTask::query()
            ->where('project_id', $project->id)
            ->whereIn('id', $ids)
            ->update(['assigned_user_id' => $userId]);

        $msg = $userId ? 'Verantwoordelijke bijgewerkt' : 'Verantwoordelijke ontkoppeld';

        return $this->respondTasks(
            $request,
            $project,
            $msg,
            'success',
            200,
            'task.assignee_updated',
            ['assigned_user_id' => $userId, 'count' => count($ids), 'ids' => $ids]
        );
    }

    public function locationSuggest(Request $request, Project $project)
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 3) {
            return response()->json(['items' => []]);
        }

        $res = Http::timeout(5)->get('https://api.pdok.nl/bzk/locatieserver/search/v3_1/suggest', [
            'q'    => $q,
            'fq'   => 'type:adres',
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
            'location'   => ['nullable', 'string', 'max:255'],
            'task_ids'   => ['sometimes', 'array', 'min:1'],
            'task_ids.*' => ['integer'],
        ]);

        $location = trim((string) ($data['location'] ?? ''));
        $location = $location === '' ? null : $location;

        $ids = !empty($data['task_ids'])
            ? array_map('intval', (array) $data['task_ids'])
            : [(int) $task->id];

        ProjectTask::query()
            ->where('project_id', $project->id)
            ->whereIn('id', $ids)
            ->update(['location' => $location]);

        $msg = $location ? 'Locatie bijgewerkt' : 'Locatie gereset';

        return $this->respondTasks(
            $request,
            $project,
            $msg,
            'success',
            200,
            'task.location_updated',
            ['location' => $location, 'count' => count($ids), 'ids' => $ids]
        );
    }

    public function updateName(Request $request, Project $project, ProjectTask $task)
    {
        if ((int) $task->project_id !== (int) $project->id) abort(404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
        ]);

        $new = trim((string) $data['name']);
        if ($new === '') $new = (string) ($task->name ?? '');

        $task->name = $new;
        $task->save();

        return $this->respondTasks(
            $request,
            $project,
            'Taaknaam opgeslagen',
            'success',
            200,
            'task.name_updated',
            ['task_id' => (int) $task->id, 'name' => $new]
        );
    }

    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
        ]);

        $taskId = null;

        DB::transaction(function () use ($project, $data, &$taskId) {
            $max = ProjectTask::query()
                ->where('project_id', $project->id)
                ->lockForUpdate()
                ->max('sort_order');

            $nextOrder = is_null($max) ? 1 : ((int) $max + 1);

            $t = $project->tasks()->create([
                'name'             => $data['name'],
                'status'           => 'pending',
                'assigned_user_id' => null,
                'location'         => null,
                'due_date'         => null,
                'completed_at'     => null,
                'sort_order'       => $nextOrder,
                'description'      => null,
            ]);

            $taskId = (int) $t->id;
        });

        return $this->respondTasks(
            $request,
            $project,
            'Taak aangemaakt',
            'success',
            200,
            'task.created',
            ['task_id' => $taskId, 'name' => (string) $data['name']]
        );
    }

    public function destroy(Request $request, Project $project, ProjectTask $task)
    {
        if ((int) $task->project_id !== (int) $project->id) abort(404);

        $id = (int) $task->id;
        $name = (string) ($task->name ?? '');

        $task->delete();

        return $this->respondTasks(
            $request,
            $project,
            'Taak verwijderd',
            'success',
            200,
            'task.deleted',
            ['task_id' => $id, 'name' => $name]
        );
    }

    public function bulkDestroy(Request $request, Project $project)
    {
        $data = $request->validate([
            'task_ids'   => ['required', 'array', 'min:1'],
            'task_ids.*' => ['integer'],
        ]);

        $ids = array_map('intval', (array) $data['task_ids']);

        ProjectTask::query()
            ->where('project_id', $project->id)
            ->whereIn('id', $ids)
            ->delete();

        return $this->respondTasks(
            $request,
            $project,
            'Taken verwijderd',
            'success',
            200,
            'task.bulk_deleted',
            ['count' => count($ids), 'ids' => $ids]
        );
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

    public function updateDescription(Request $request, Project $project, ProjectTask $task)
    {
        if ((int) $task->project_id !== (int) $project->id) abort(404);

        $data = $request->validate([
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $before = (string) ($task->description ?? '');

        $desc  = trim((string) ($data['description'] ?? ''));
        $desc  = $desc === '' ? null : $desc;
        $after = (string) ($desc ?? '');

        if ($before === $after) {
            return $request->header('HX-Request') === 'true'
                ? response('', 204)
                : back();
        }

        $task->description = $desc;
        $task->save();

        $wasEmpty = trim($before) === '';
        $isEmpty  = trim($after) === '';

        $event = match (true) {
            $wasEmpty && !$isEmpty => 'description_filled',
            !$wasEmpty && $isEmpty => 'description_cleared',
            default                => 'description_updated',
        };

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
