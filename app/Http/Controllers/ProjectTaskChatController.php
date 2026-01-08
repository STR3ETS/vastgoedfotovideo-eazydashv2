<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTaskMessage;
use App\Models\ProjectTaskMessageAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectTaskChatController extends Controller
{
    public function messages(Request $request, Project $project, ProjectTask $task)
    {
        if ((int) $task->project_id !== (int) $project->id) abort(404);

        $messages = ProjectTaskMessage::query()
            ->where('project_id', $project->id)
            ->where('project_task_id', $task->id)
            ->with(['user', 'attachments'])
            ->orderBy('id', 'asc')
            ->limit(200)
            ->get();

        return view('hub.projects.tasks.partials.chat-messages', [
            'project' => $project,
            'task' => $task,
            'messages' => $messages,
        ]);
    }

    public function store(Request $request, Project $project, ProjectTask $task)
    {
        if ((int) $task->project_id !== (int) $project->id) abort(404);

        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
            'attachments' => ['sometimes', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:10240'], // 10MB per file
        ]);

        $body = trim((string) ($data['body'] ?? ''));
        $body = $body === '' ? null : $body;

        $files = $request->file('attachments', []);

        if ($body === null && empty($files)) {
            // niets te versturen
            return $request->header('HX-Request') === 'true'
                ? response('', 204)
                : back();
        }

        $msg = ProjectTaskMessage::create([
            'project_id' => $project->id,
            'project_task_id' => $task->id,
            'user_id' => $request->user()->id,
            'body' => $body,
        ]);

        foreach ($files as $file) {
            if (!$file) continue;

            $path = $file->store('task-chat/' . $task->id, 'public');

            ProjectTaskMessageAttachment::create([
                'project_task_message_id' => $msg->id,
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => (int) $file->getSize(),
            ]);
        }

        // Return refreshed message list (HTMX)
        return $this->messages($request, $project, $task);
    }

    public function download(Request $request, Project $project, ProjectTask $task, ProjectTaskMessageAttachment $attachment)
    {
        if ((int) $task->project_id !== (int) $project->id) abort(404);

        // extra guard: attachment moet bij deze task horen
        $message = $attachment->message;
        if (!$message || (int) $message->project_task_id !== (int) $task->id) abort(404);

        $disk = $attachment->disk ?: 'public';
        if (!Storage::disk($disk)->exists($attachment->path)) abort(404);

        return Storage::disk($disk)->download($attachment->path, $attachment->original_name);
    }
}
