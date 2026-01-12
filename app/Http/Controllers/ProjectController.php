<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\OnboardingRequest;
use App\Models\ProjectTask;
use App\Models\ProjectFinanceItem;
use App\Models\ProjectPlanningItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $q    = trim((string) $request->query('q', ''));
        $sort = (string) $request->query('sort', 'newest');

        $projects = Project::query()->with(['client', 'onboardingRequest']);

        if ($q !== '') {
            $projects->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhereHas('client', function ($u) use ($q) {
                        $u->where('name', 'like', "%{$q}%")
                          ->orWhere('email', 'like', "%{$q}%");
                    })
                    ->orWhereHas('onboardingRequest', function ($o) use ($q) {
                        $o->where('contact_first_name', 'like', "%{$q}%")
                          ->orWhere('contact_last_name', 'like', "%{$q}%")
                          ->orWhere('contact_email', 'like', "%{$q}%")
                          ->orWhere('address', 'like', "%{$q}%")
                          ->orWhere('postcode', 'like', "%{$q}%")
                          ->orWhere('city', 'like', "%{$q}%");
                    });
            });
        }

        if ($sort === 'oldest') {
            $projects->orderBy('created_at', 'asc');
        } elseif ($sort === 'title_asc') {
            $projects->orderByRaw('LOWER(title) asc');
        } elseif ($sort === 'title_desc') {
            $projects->orderByRaw('LOWER(title) desc');
        } elseif ($sort === 'status') {
            $projects->orderBy('status', 'asc')->orderBy('created_at', 'desc');
        } else {
            $projects->orderBy('created_at', 'desc');
        }

        $rows = $projects->paginate(20)->withQueryString();

        return view('hub.projects.index', [
            'user' => $request->user(),
            'rows' => $rows,
            'q'    => $q,
            'sort' => $sort,
        ]);
    }

    public function create(Request $request)
    {
        $templates = Project::query()
            ->where('template', 1)
            ->orderBy('title')
            ->get(['id', 'title']);

        $projects = Project::query()
            ->orderByDesc('created_at')
            ->limit(200)
            ->get(['id', 'title']);

        return view('hub.projects.create', [
            'user'      => $request->user(),
            'templates' => $templates,
            'projects'  => $projects,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'mode'  => ['required', Rule::in(['empty','template_create','template_use','copy_existing'])],
            'template_project_id' => ['nullable', 'integer'],
            'copy_project_id'     => ['nullable', 'integer'],
        ]);

        if ($data['mode'] === 'template_use') {
            $request->validate([
                'template_project_id' => [
                    'required',
                    'integer',
                    Rule::exists('projects', 'id')->where(fn ($q) => $q->where('template', 1)),
                ],
            ]);
        }

        if ($data['mode'] === 'copy_existing') {
            $request->validate([
                'copy_project_id' => ['required','integer', Rule::exists('projects', 'id')],
            ]);
        }

        return DB::transaction(function () use ($request, $data) {

            $sourceProject = null;

            if ($data['mode'] === 'copy_existing') {
                $sourceProject = Project::query()
                    ->with(['onboardingRequest','tasks.subtasks','financeItems','planningItems'])
                    ->select(['id', 'client_user_id', 'onboarding_request_id'])
                    ->find((int) $data['copy_project_id']);
            }

            if ($data['mode'] === 'template_use') {
                $sourceProject = Project::query()
                    ->with(['onboardingRequest','tasks.subtasks','financeItems','planningItems'])
                    ->select(['id', 'client_user_id', 'onboarding_request_id'])
                    ->find((int) $data['template_project_id']);
            }

            // ✅ client_user_id is NOT NULL in jouw DB
            $clientUserId = (int) ($sourceProject?->client_user_id ?? $request->user()->id);

            // ✅ onboarding_request_id is NOT NULL in jouw DB → altijd maken (clone als source bestaat)
            $onboardingRequest = $this->makeOnboardingRequestCloneOrEmpty(
                $sourceProject?->onboardingRequest,
                $clientUserId,
                (int) $request->user()->id
            );

            // ✅ Project aanmaken (alle NOT NULL velden gevuld)
            $project = Project::create([
                'title'                 => $data['title'],
                'status'                => 'active',
                'category'              => 'project',
                'template'              => $data['mode'] === 'template_create' ? 1 : 0,

                'client_user_id'        => $clientUserId,
                'created_by_user_id'    => (int) $request->user()->id,
                'onboarding_request_id' => (int) $onboardingRequest->id,
            ]);

            if ($sourceProject && in_array($data['mode'], ['copy_existing','template_use'], true)) {
                $this->cloneTasks($sourceProject, $project);
                $this->cloneFinanceItems($sourceProject, $project);
                $this->clonePlanningItems($sourceProject, $project);
            }

            return redirect()
                ->route('support.projecten.show', $project)
                ->with('success', 'Project aangemaakt.');
        });
    }

    public function show(Request $request, Project $project)
    {
        $project->load([
            'onboardingRequest',
            'client',
            'createdBy',
            'members',
            'followers',
            'tasks.assignedUser',
            'financeItems',
            'planningItems.assignee',
            'comments.user',
        ]);

        if ($project->relationLoaded('tasks') && $project->tasks) {
            $project->setRelation(
                'tasks',
                $project->tasks->sortBy(fn ($t) => [$t->sort_order ?? 999999, $t->id])->values()
            );
        }

        $assignees = User::query()
            ->whereIn('rol', ['team-manager', 'client-manager', 'fotograaf', 'admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'rol']);

        return view('hub.projects.show', [
            'user'      => $request->user(),
            'project'   => $project,
            'assignees' => $assignees,
        ]);
    }

    public function updateOnboarding(Request $request, Project $project)
    {
        $project->load('onboardingRequest');

        $req = $project->onboardingRequest;
        abort_unless($req, 404);

        $data = $request->validate([
            'field' => ['required', 'string', Rule::in([
                'address','postcode','city','surface_home','surface_outbuildings','surface_plot','shoot_date','shoot_slot',
                'contact_first_name','contact_last_name','contact_email','contact_phone','contact_updates',
                'agency_first_name','agency_last_name','agency_email','agency_phone',
            ])],
            'value'   => ['nullable'],
            'section' => ['required', 'string', Rule::in(['location','contact','agency'])],
        ]);

        $field = $data['field'];
        $value = $data['value'];

        if (in_array($field, ['surface_home','surface_outbuildings','surface_plot'], true)) {
            $value = (int) ($value ?? 0);
        }

        if ($field === 'contact_updates') {
            $value = (int) ((string) $value === '1');
        }

        if ($field === 'shoot_date') {
            $value = $value ?: null;
        }

        if ($field === 'contact_email' || $field === 'agency_email') {
            $request->validate(['value' => ['nullable', 'email']]);
        }

        $req->{$field} = $value;
        $req->save();

        $project->load('onboardingRequest');

        if ($data['section'] === 'location') {
            return view('hub.projects.partials.onboarding.location', ['project' => $project]);
        }

        if ($data['section'] === 'contact') {
            return view('hub.projects.partials.onboarding.contact', ['project' => $project]);
        }

        return view('hub.projects.partials.onboarding.agency', ['project' => $project]);
    }

    private function makeOnboardingRequestCloneOrEmpty(?OnboardingRequest $src, int $clientUserId, int $createdByUserId): OnboardingRequest
    {
        // ✅ Als er een source is: clone ALLES (incl package en toekomstige velden)
        $o = $src ? $src->replicate() : new OnboardingRequest();

        // ✅ Required / belangrijke defaults (allemaal veilig via hasColumn)
        if (Schema::hasColumn('onboarding_requests', 'user_id')) {
            $o->user_id = $clientUserId;
        }

        // ✅ JOUW ERROR FIX: package is NOT NULL → altijd vullen
        if (Schema::hasColumn('onboarding_requests', 'package')) {
            // Pak package van source als die er is, anders fallback
            // ⚠️ Zet hier jouw "standaard" package string neer als je met vaste waarden werkt
            $o->package = $src?->package ?? 'basic';
        }

        if (Schema::hasColumn('onboarding_requests', 'company_id')) {
            $o->company_id = $o->company_id ?? 1;
        }

        if (Schema::hasColumn('onboarding_requests', 'client_user_id')) {
            $o->client_user_id = $clientUserId;
        }

        if (Schema::hasColumn('onboarding_requests', 'created_by_user_id')) {
            $o->created_by_user_id = $createdByUserId;
        }

        // ✅ handige defaults als je "empty" maakt
        if (Schema::hasColumn('onboarding_requests', 'contact_updates') && $o->contact_updates === null) {
            $o->contact_updates = 1;
        }

        if (Schema::hasColumn('onboarding_requests', 'surface_home') && $o->surface_home === null) {
            $o->surface_home = 0;
        }
        if (Schema::hasColumn('onboarding_requests', 'surface_outbuildings') && $o->surface_outbuildings === null) {
            $o->surface_outbuildings = 0;
        }
        if (Schema::hasColumn('onboarding_requests', 'surface_plot') && $o->surface_plot === null) {
            $o->surface_plot = 0;
        }

        $o->save();

        return $o;
    }

    private function cloneTasks(Project $src, Project $dst): void
    {
        // Laad subtaken mee
        $src->loadMissing('tasks.subtasks');

        foreach (($src->tasks ?? collect()) as $task) {
            // 1) Clone hoofdtaak
            $newTask = $task->replicate();
            $newTask->project_id = $dst->id;

            if (Schema::hasColumn($newTask->getTable(), 'company_id') && empty($newTask->company_id)) {
                $newTask->company_id = 1;
            }

            $newTask->save();

            // 2) Clone subtaken van deze hoofdtaak
            foreach (($task->subtasks ?? collect()) as $sub) {
                $newSub = $sub->replicate();

                // zet FK naar de NIEUWE taak (verschilt soms per schema)
                if (Schema::hasColumn($newSub->getTable(), 'task_id')) {
                    $newSub->task_id = $newTask->id;
                }
                if (Schema::hasColumn($newSub->getTable(), 'project_task_id')) {
                    $newSub->project_task_id = $newTask->id;
                }

                // als subtasks ook project_id hebben
                if (Schema::hasColumn($newSub->getTable(), 'project_id')) {
                    $newSub->project_id = $dst->id;
                }

                if (Schema::hasColumn($newSub->getTable(), 'company_id') && empty($newSub->company_id)) {
                    $newSub->company_id = 1;
                }

                $newSub->save();
            }
        }
    }

    private function cloneFinanceItems(Project $src, Project $dst): void
    {
        $src->loadMissing('financeItems');

        foreach (($src->financeItems ?? collect()) as $item) {
            /** @var \App\Models\ProjectFinanceItem $item */
            $new = $item->replicate();
            $new->project_id = $dst->id;

            if (Schema::hasColumn($new->getTable(), 'company_id') && empty($new->company_id)) {
                $new->company_id = 1;
            }

            $new->save();
        }
    }

    private function clonePlanningItems(Project $src, Project $dst): void
    {
        $src->loadMissing('planningItems');

        foreach (($src->planningItems ?? collect()) as $plan) {
            /** @var \App\Models\ProjectPlanningItem $plan */
            $new = $plan->replicate();
            $new->project_id = $dst->id;

            if (Schema::hasColumn($new->getTable(), 'company_id') && empty($new->company_id)) {
                $new->company_id = 1;
            }

            $new->save();
        }
    }
}
