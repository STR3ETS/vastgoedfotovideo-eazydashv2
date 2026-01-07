<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\User;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $q    = trim((string) $request->query('q', ''));
        $sort = (string) $request->query('sort', 'newest');

        $projects = Project::query()
            ->with([
                'client',
                'onboardingRequest',
            ]);

        // Search (project title + klant naam/email + contactpersoon uit onboarding)
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

        // Sorteren (zelfde opties als onboarding)
        if ($sort === 'oldest') {
            $projects->orderBy('created_at', 'asc');
        } elseif ($sort === 'title_asc') {
            $projects->orderByRaw('LOWER(title) asc');
        } elseif ($sort === 'title_desc') {
            $projects->orderByRaw('LOWER(title) desc');
        } elseif ($sort === 'status') {
            $projects->orderBy('status', 'asc')->orderBy('created_at', 'desc');
        } else {
            $projects->orderBy('created_at', 'desc'); // newest
        }

        $rows = $projects->paginate(20)->withQueryString();

        return view('hub.projects.index', [
            'user' => $request->user(),
            'rows' => $rows,
            'q'    => $q,
            'sort' => $sort,
        ]);
    }

    public function show(Request $request, Project $project)
    {
        // ✅ alles wat we nodig hebben voor show.blade + dropdown status update
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

        // ✅ optioneel: consistente sortering in de view (als je sort_order gebruikt)
        if ($project->relationLoaded('tasks') && $project->tasks) {
            $project->setRelation(
                'tasks',
                $project->tasks->sortBy(function ($t) {
                    return [$t->sort_order ?? 999999, $t->id];
                })->values()
            );
        }

        $assignees = User::query()
        ->whereIn('rol', ['team-manager', 'client-manager', 'fotograaf', 'admin'])
        ->orderBy('name')
        ->get(['id', 'name', 'rol']);

        return view('hub.projects.show', [
            'user'    => $request->user(),
            'project' => $project,
            'assignees' => $assignees,
        ]);
    }
}
