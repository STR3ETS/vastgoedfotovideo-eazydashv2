<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTaskQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectenController extends Controller
{
    public function index()
    {
        $projects = Project::with([
                'aanvraag.tasks.questions',
                'tasks.questions',
                'callLogs.user',
            ])
            ->latest()
            ->paginate(15);

        $statusMap = [
            'preview' => [
                'value' => 'preview',
                'label' => __('projecten.statuses.preview'),
            ],
            'waiting_customer' => [
                'value' => 'waiting_customer',
                'label' => __('projecten.statuses.waiting_customer'),
            ],
            'offerte' => [
                'value' => 'offerte',
                'label' => __('projecten.statuses.offerte'),
            ],
        ];

        $statusCounts = Project::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $statusByValue = collect($statusMap)
            ->mapWithKeys(fn ($s) => [$s['value'] => $s['label']])
            ->all();

        return view('hub.projecten.index', [
            'user'          => auth()->user(),
            'projects'      => $projects,
            'statusMap'     => $statusMap,
            'statusCounts'  => $statusCounts,
            'statusByValue' => $statusByValue,
        ]);
    }

    public function updateStatus(Request $request, Project $project)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:preview,waiting_customer,offerte'],
        ]);

        $oldStatus        = $project->status;
        $project->status  = $data['status'];
        $project->save();

        $offerteTaskData = null;

        if ($oldStatus !== 'offerte' && $project->status === 'offerte') {
            /** @var ProjectTask $task */
            $task = $this->ensureOfferteTask($project);

            /** @var ProjectTaskQuestion|null $question */
            $question = $task->questions()->where('order', 1)->first();

            $offerteTaskData = [
                'title'       => $task->title,
                'description' => $task->description,
                'notes'       => $question?->answer,
                'completed'   => (bool) $task->completed_at,
            ];
        }

        $label = __('projecten.statuses.' . $project->status);

        return response()->json([
            'success'      => true,
            'id'           => $project->id,
            'status'       => $project->status,
            'label'        => $label,
            'offerte_task' => $offerteTaskData,
        ]);
    }

    public function updatePreview(Request $request, Project $project)
    {
        $data = $request->validate([
            'preview_url' => ['nullable', 'string', 'max:2048'],
        ]);

        if (! $project->preview_token) {
            $project->preview_token = Project::generatePreviewToken();
        }

        $project->preview_url = $data['preview_url'] ?: null;

        if (! empty($project->preview_url)) {
            $project->status = 'waiting_customer';
        }

        $project->save();

        $label = __('projecten.statuses.' . $project->status);

        return response()->json([
            'success'      => true,
            'id'           => $project->id,
            'preview_url'  => $project->preview_url,
            'preview_link' => $project->preview_url && $project->preview_token
                ? route('preview.show', ['token' => $project->preview_token])
                : null,
            'status'       => $project->status,
            'label'        => $label,
        ]);
    }

    public function updateOfferteNotes(Request $request, Project $project)
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        // Zorg dat de taak bestaat (zelfde logica als bij status "offerte")
        /** @var ProjectTask $task */
        $task = $this->ensureOfferteTask($project);

        // Eén notitie-vraag koppelen (order = 1)
        /** @var ProjectTaskQuestion $question */
        $question = $task->questions()->firstOrCreate(
            ['order' => 1],
            [
                'question' => 'Notities offertegesprek',
                'required' => false,
            ]
        );

        $question->answer = $data['notes'] ?? null;
        $question->save();

        return response()->json([
            'success' => true,
            'notes'   => $question->answer,
        ]);
    }

    public function completeOfferteTask(Request $request, Project $project)
    {
        /** @var ProjectTask $task */
        $task = $this->ensureOfferteTask($project);

        if (! $task->completed_at) {
            $task->completed_at = now();
            $task->save();
        }

        $question = $task->questions()->where('order', 1)->first();

        return response()->json([
            'success'      => true,
            'offerte_task' => [
                'title'       => $task->title,
                'description' => $task->description,
                'notes'       => $question?->answer,
                'completed'   => (bool) $task->completed_at,
            ],
        ]);
    }

    public function storeCall(Request $request, Project $project)
    {
        $data = $request->validate([
            'outcome' => ['required', 'string', 'in:geen_antwoord,gesproken'],
            'note'    => ['nullable', 'string'],
        ]);

        $user = $request->user();

        /** @var ProjectCallLog $log */
        $log = $project->callLogs()->create([
            'user_id'   => $user?->id,
            'called_at' => now(),
            'outcome'   => $data['outcome'],
            'note'      => $data['note'] ?? null,
        ]);

        return response()->json([
            'success'    => true,
            'id'         => $log->id,
            'called_at'  => optional($log->called_at)->format('d-m-Y H:i'),
            'outcome'    => $log->outcome,
            'note'       => $log->note,
            'user_name'  => optional($log->user)->name,
        ]);
    }

    /**
     * Zorgt dat de "bellen met de klant"-taak bestaat en geeft die terug.
     */
    protected function ensureOfferteTask(Project $project): ProjectTask
    {
        return $project->tasks()->firstOrCreate(
            ['type' => 'call_customer'],
            [
                'title'       => 'Bellen met de klant',
                'description' => 'Bel de klant t.a.v. feedback/goedkeuring preview',
                'due_at'      => null,
            ]
        );
    }
}