<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTaskSubtask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectTaskSubtaskController extends Controller
{
    private function assignees()
    {
        return User::query()
            ->whereIn('rol', ['team-manager', 'client-manager', 'fotograaf', 'admin'])
            ->orderBy('name')
            ->get(['id','name','rol']);
    }

    private function partial(Project $project, ProjectTask $task)
    {
        $assignees = $this->assignees();

        // zorg dat relatie geladen is
        $task->load(['subtasks.assignedUser']);

        return view('hub.projects.tasks.partials.subtasks', compact('project','task','assignees'));
    }

    public function store(Request $request, Project $project, ProjectTask $task)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
        ]);

        $max = (int) $task->subtasks()->max('sort_order');

        $task->subtasks()->create([
            'name' => $data['name'],
            'status' => 'active',
            'sort_order' => $max + 1,
        ]);

        return $this->partial($project, $task);
    }

    public function updateStatus(Request $request, Project $project, ProjectTask $task, ProjectTaskSubtask $subtask)
    {
        $data = $request->validate([
            'status' => ['required','string', Rule::in(['pending','active','done','cancelled','archived'])],
            'subtask_ids' => ['array'],
            'subtask_ids.*' => ['integer'],
        ]);

        $ids = $request->input('subtask_ids', []);
        if (!empty($ids)) {
            $task->subtasks()->whereIn('id', $ids)->update(['status' => $data['status']]);
        } else {
            $subtask->update(['status' => $data['status']]);
        }

        return $this->partial($project, $task);
    }

    public function updateAssignee(Request $request, Project $project, ProjectTask $task, ProjectTaskSubtask $subtask)
    {
        $data = $request->validate([
            'assigned_user_id' => ['nullable','integer','exists:users,id'],
            'subtask_ids' => ['array'],
            'subtask_ids.*' => ['integer'],
        ]);

        $ids = $request->input('subtask_ids', []);
        if (!empty($ids)) {
            $task->subtasks()->whereIn('id', $ids)->update(['assigned_user_id' => $data['assigned_user_id']]);
        } else {
            $subtask->update(['assigned_user_id' => $data['assigned_user_id']]);
        }

        return $this->partial($project, $task);
    }

    public function updateDueDate(Request $request, Project $project, ProjectTask $task, ProjectTaskSubtask $subtask)
    {
        $data = $request->validate([
            'due_date' => ['nullable','date'],
            'subtask_ids' => ['array'],
            'subtask_ids.*' => ['integer'],
        ]);

        $ids = $request->input('subtask_ids', []);
        if (!empty($ids)) {
            $task->subtasks()->whereIn('id', $ids)->update(['due_date' => $data['due_date']]);
        } else {
            $subtask->update(['due_date' => $data['due_date']]);
        }

        return $this->partial($project, $task);
    }

    public function destroy(Project $project, ProjectTask $task, ProjectTaskSubtask $subtask)
    {
        $subtask->delete();
        return $this->partial($project, $task);
    }

    public function bulkDestroy(Request $request, Project $project, ProjectTask $task)
    {
        $data = $request->validate([
            'subtask_ids' => ['required','array'],
            'subtask_ids.*' => ['integer'],
        ]);

        $task->subtasks()->whereIn('id', $data['subtask_ids'])->delete();

        return $this->partial($project, $task);
    }
}
