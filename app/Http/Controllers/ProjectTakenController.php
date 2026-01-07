<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectTakenController extends Controller
{
    private function assignees()
    {
        // Als je rollen in een kolom hebt:
        return User::query()
            ->whereIn('rol', ['team-manager', 'client-manager', 'fotograaf', 'admin'])
            ->orderBy('name')
            ->get(['id','name','rol']);

        // Spatie variant:
        // return User::role(['team-manager','client-manager','fotograaf','admin'])
        //   ->orderBy('name')->get(['id','name']);
    }

    public function updateStatus(Request $request, Project $project, ProjectTask $task)
    {
        if ((int) $task->project_id !== (int) $project->id) abort(404);

        $data = $request->validate([
            'status' => ['required', 'string', Rule::in(['pending','active','done','cancelled','archived'])],
        ]);

        $newStatus = $data['status'];
        $task->status = $newStatus;

        if ($newStatus === 'done') {
            $task->completed_at = $task->completed_at ?? now();
        } else {
            $task->completed_at = null;
        }

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

    public function bulkUpdateStatus(Request $request, Project $project)
    {
        $data = $request->validate([
            'status' => ['required', 'string', Rule::in(['pending','active','done','cancelled','archived'])],
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
        ]);

        $due = $data['due_date'] ?? null;

        if ($due !== null) {
            $date = Carbon::createFromFormat('Y-m-d', $due);
            if ($date->isWeekend()) {
                return back()->withErrors(['due_date' => 'Kies een werkdag (maandag t/m vrijdag).']);
            }
        }

        $task->due_date = $due;
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

    // ✅ NIEUW: verantwoordelijke updaten
    public function updateAssignee(Request $request, Project $project, ProjectTask $task)
    {
        if ((int) $task->project_id !== (int) $project->id) abort(404);

        $data = $request->validate([
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $userId = $data['assigned_user_id'] ?? null;

        // Security: alleen toestaan voor bepaalde rollen
        if ($userId !== null) {
            $allowed = User::query()
                ->where('id', $userId)
                ->whereIn('rol', ['team-manager', 'client-manager', 'fotograaf', 'admin'])
                ->exists();

            if (!$allowed) {
                abort(403);
            }
        }

        $task->assigned_user_id = $userId;
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
}