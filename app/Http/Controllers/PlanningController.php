<?php

namespace App\Http\Controllers;

use App\Models\ProjectPlanningItem;
use App\Models\ProjectTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PlanningController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // today | planning | qc | team
        $section = $request->query('section', 'today');
        $baseUrl = url('/app/planning-management');

        // ✅ Alleen fotograaf (niet admin) ziet alleen eigen dingen
        $restrictToMe = ($user->rol === 'fotograaf') && !((bool) ($user->is_company_admin ?? false));

        // =========================
        // Helpers
        // =========================
        $statusPill = function (?string $status) {
            $status = strtolower(trim((string) $status));

            return match ($status) {
                'new', 'nieuw' => ['label' => 'Nieuw', 'class' => 'text-[#2A324B] bg-[#2A324B]/15'],
                'planned', 'ingepland' => ['label' => 'Ingepland', 'class' => 'text-[#87A878] bg-[#87A878]/15'],
                'done', 'afgerond' => ['label' => 'Afgerond', 'class' => 'text-[#009AC3] bg-[#009AC3]/15'],
                'active' => ['label' => 'Actief', 'class' => 'text-[#009AC3] bg-[#009AC3]/15'],
                'accepted' => ['label' => 'Geaccepteerd', 'class' => 'text-[#87A878] bg-[#87A878]/15'],
                'cancelled', 'canceled', 'geannuleerd' => ['label' => 'Geannuleerd', 'class' => 'text-[#DF2935] bg-[#DF2935]/15'],
                default => ['label' => $status !== '' ? ucfirst($status) : 'Onbekend', 'class' => 'text-[#2A324B] bg-[#2A324B]/15'],
            };
        };

        $formatTime = function ($dt) {
            if (!$dt) return '-';
            return Carbon::parse($dt)->format('H:i');
        };

        $applyOverlapWindow = function ($query, Carbon $start, Carbon $end) {
            return $query->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_at', [$start, $end])
                    ->orWhereBetween('end_at', [$start, $end])
                    ->orWhere(function ($qq) use ($start, $end) {
                        $qq->where('start_at', '<=', $start)
                           ->where('end_at', '>=', $end);
                    });
            });
        };

        // =========================
        // ✅ Scope: fotograaf ziet alleen eigen items/projecten
        // =========================
        $scopePlanningForUser = function ($q) use ($restrictToMe, $user) {
            if (!$restrictToMe) return $q;

            return $q->where(function ($qq) use ($user) {
                $qq->where('assignee_user_id', $user->id)
                   ->orWhereHas('project.members', function ($m) use ($user) {
                       $m->where('users.id', $user->id);
                   });
            });
        };

        $scopeTasksForUser = function ($q) use ($restrictToMe, $user) {
            if (!$restrictToMe) return $q;

            return $q->where(function ($qq) use ($user) {
                $qq->where('assigned_user_id', $user->id)
                   ->orWhereHas('project.members', function ($m) use ($user) {
                       $m->where('users.id', $user->id);
                   });
            });
        };

        // =========================
        // ✅ Eager loads
        // =========================
        $planningWith = [
            'project.onboardingRequest',
            'project.client',
            'assignee',

            // taken in project (voor detail in UI als je wil)
            'project.tasks' => function ($q) {
                $q->select([
                        'id', 'project_id', 'name', 'due_date', 'location',
                        'assigned_user_id', 'status', 'sort_order', 'completed_at', 'description',
                    ])
                    ->orderByRaw('completed_at IS NULL DESC')
                    ->orderByRaw('due_date IS NULL')
                    ->orderBy('due_date')
                    ->orderBy('sort_order')
                    ->orderBy('id');
            },
            'project.tasks.assignedUser:id,name',
        ];

        $taskWith = [
            'project.onboardingRequest',
            'project.client',
            'assignedUser',
            'project.members', // voor scope
        ];

        // =========================
        // TODAY
        // =========================
        $selectedDate  = null;
        $prevDate      = null;
        $nextDate      = null;
        $dateLabel     = null;

        $todayRequests = collect(); // planning items
        $todayTasks    = collect(); // project_tasks due vandaag

        if ($section === 'today') {
            $dateParam = $request->query('date');

            try {
                $selectedDate = $dateParam ? Carbon::parse($dateParam)->startOfDay() : now()->startOfDay();
            } catch (\Throwable $e) {
                $selectedDate = now()->startOfDay();
            }

            $prevDate  = $selectedDate->copy()->subDay();
            $nextDate  = $selectedDate->copy()->addDay();
            $dateLabel = $selectedDate->copy()->locale('nl')->translatedFormat('l j F Y');

            $dayStart = $selectedDate->copy()->startOfDay();
            $dayEnd   = $selectedDate->copy()->endOfDay();

            // planning items (project_planning_items)
            $qPlanning = ProjectPlanningItem::query()
                ->with($planningWith);

            $qPlanning = $scopePlanningForUser($qPlanning);
            $todayRequests = $applyOverlapWindow($qPlanning, $dayStart, $dayEnd)
                ->orderBy('start_at', 'asc')
                ->get();

            // taken (project_tasks) met due_date = vandaag
            $qTasks = ProjectTask::query()->with($taskWith);
            $qTasks = $scopeTasksForUser($qTasks);

            $todayTasks = $qTasks
                ->whereDate('due_date', $selectedDate->toDateString())
                ->orderByRaw("CASE WHEN completed_at IS NULL THEN 0 ELSE 1 END")
                ->orderBy('due_date')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        }

        // =========================
        // PLANNING
        // =========================
        $filterDate = null;

        $perPage = (int) $request->query('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) $perPage = 10;

        $planningItems = collect(); // planning items
        $planningTasks = collect(); // tasks in window

        if ($section === 'planning') {
            $view = $request->query('view', 'list');
            if (!in_array($view, ['list', 'map', 'calendar'], true)) $view = 'list';

            $range = $request->query('range', 'all');
            if (!in_array($range, ['all', 'today', 'this_week', 'this_month', 'this_year', 'future_only'], true)) {
                $range = 'all';
            }

            $filterDateParam = $request->query('date');
            try {
                $filterDate = $filterDateParam ? Carbon::parse($filterDateParam)->startOfDay() : null;
            } catch (\Throwable $e) {
                $filterDate = null;
            }

            // =========================
            // AJAX feed FullCalendar (planning items + tasks)
            // =========================
            if ($view === 'calendar' && $request->boolean('ajax')) {
                $startParam = $request->query('start');
                $endParam   = $request->query('end');

                try {
                    $windowStart = $startParam ? Carbon::parse($startParam) : now()->startOfMonth();
                } catch (\Throwable $e) {
                    $windowStart = now()->startOfMonth();
                }

                try {
                    $windowEnd = $endParam ? Carbon::parse($endParam) : now()->endOfMonth()->addDay();
                } catch (\Throwable $e) {
                    $windowEnd = now()->endOfMonth()->addDay();
                }

                $windowStart = $windowStart->copy()->startOfDay();
                $windowEnd   = $windowEnd->copy()->endOfDay();

                // exacte datum heeft voorrang
                if ($filterDate) {
                    $windowStart = $filterDate->copy()->startOfDay();
                    $windowEnd   = $filterDate->copy()->endOfDay();
                } else {
                    $now = now();
                    if ($range === 'today') {
                        $windowStart = $now->copy()->startOfDay();
                        $windowEnd   = $now->copy()->endOfDay();
                    } elseif ($range === 'this_week') {
                        $windowStart = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
                        $windowEnd   = $now->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
                    } elseif ($range === 'this_month') {
                        $windowStart = $now->copy()->startOfMonth()->startOfDay();
                        $windowEnd   = $now->copy()->endOfMonth()->endOfDay();
                    } elseif ($range === 'this_year') {
                        $windowStart = $now->copy()->startOfYear()->startOfDay();
                        $windowEnd   = $now->copy()->endOfYear()->endOfDay();
                    } elseif ($range === 'future_only') {
                        $windowStart = $now->copy()->startOfDay();
                        // windowEnd laten zoals FullCalendar vraagt
                    }
                }

                // planning items
                $qItems = ProjectPlanningItem::query()->with($planningWith);
                $qItems = $scopePlanningForUser($qItems);

                $items = $applyOverlapWindow($qItems, $windowStart, $windowEnd)
                    ->orderBy('start_at', 'asc')
                    ->get();

                $planningEvents = $items->map(function (ProjectPlanningItem $pi) {
                    $project = $pi->project;

                    $title = trim(($project?->title ?: 'Project') . ' · ' . ($project?->client?->name ?: ''));
                    if ($title === '·' || $title === '') $title = 'Planning';

                    return [
                        'id' => 'pi:' . $pi->id,
                        'title' => $title,
                        'start' => $pi->start_at ? Carbon::parse($pi->start_at)->toIso8601String() : null,
                        'end' => $pi->end_at ? Carbon::parse($pi->end_at)->toIso8601String() : null,
                        'url' => $project ? route('support.projecten.show', $project) : null,
                        'extendedProps' => [
                            'type' => 'planning',
                            'location' => (string) ($pi->location ?? ''),
                            'client' => (string) ($project?->client?->name ?? ''),
                            'assignee' => (string) ($pi->assignee?->name ?? 'Niet toegewezen'),
                        ],
                    ];
                });

                // tasks (all-day)
                $qTasks = ProjectTask::query()->with($taskWith);
                $qTasks = $scopeTasksForUser($qTasks);

                $tasks = $qTasks
                    ->whereDate('due_date', '>=', $windowStart->toDateString())
                    ->whereDate('due_date', '<=', $windowEnd->toDateString())
                    ->orderBy('due_date')
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get();

                $taskEvents = $tasks->map(function (ProjectTask $t) {
                    $project = $t->project;
                    $title = trim('Taak: ' . ($t->name ?: 'Taak') . ' · ' . ($project?->title ?: 'Project'));

                    $start = $t->due_date ? Carbon::parse($t->due_date)->startOfDay() : null;
                    $end = $start ? $start->copy()->addDay() : null; // all-day end is exclusive

                    return [
                        'id' => 'task:' . $t->id,
                        'title' => $title,
                        'start' => $start?->toIso8601String(),
                        'end' => $end?->toIso8601String(),
                        'allDay' => true,
                        'url' => ($project && $t->id)
                            ? route('support.projecten.taken.show', [$project, $t])
                            : null,
                        'extendedProps' => [
                            'type' => 'task',
                            'location' => (string) ($t->location ?? ''),
                            'client' => (string) ($project?->client?->name ?? ''),
                            'assignee' => (string) ($t->assignedUser?->name ?? 'Niet toegewezen'),
                            'status' => (string) ($t->status ?? ''),
                        ],
                    ];
                });

                return response()->json(
                    $planningEvents->concat($taskEvents)->values()
                );
            }

            // =========================
            // Normale list/map data
            // =========================
            $q = ProjectPlanningItem::query()->with($planningWith);
            $q = $scopePlanningForUser($q);

            if ($filterDate) {
                $dayStart = $filterDate->copy()->startOfDay();
                $dayEnd   = $filterDate->copy()->endOfDay();
                $q = $applyOverlapWindow($q, $dayStart, $dayEnd);
            } else {
                $now = now();

                if ($range === 'today') {
                    $q = $applyOverlapWindow($q, $now->copy()->startOfDay(), $now->copy()->endOfDay());
                } elseif ($range === 'this_week') {
                    $q = $applyOverlapWindow(
                        $q,
                        $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay(),
                        $now->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay()
                    );
                } elseif ($range === 'this_month') {
                    $q = $applyOverlapWindow(
                        $q,
                        $now->copy()->startOfMonth()->startOfDay(),
                        $now->copy()->endOfMonth()->endOfDay()
                    );
                } elseif ($range === 'this_year') {
                    $q = $applyOverlapWindow(
                        $q,
                        $now->copy()->startOfYear()->startOfDay(),
                        $now->copy()->endOfYear()->endOfDay()
                    );
                } elseif ($range === 'future_only') {
                    $q->where(function ($qq) use ($now) {
                        $qq->where('start_at', '>=', $now)
                           ->orWhere('end_at', '>=', $now);
                    });
                }
            }

            $now = now();
            $q->orderByRaw("
                CASE
                    WHEN start_at IS NULL THEN 2
                    WHEN start_at >= ? THEN 0
                    ELSE 1
                END
            ", [$now])->orderBy('start_at', 'asc');

            if ($view !== 'calendar') {
                $q->limit($perPage);
            }

            $planningItems = $q->get();

            // ✅ tasks ophalen voor dezelfde filter/range
            $qt = ProjectTask::query()->with($taskWith);
            $qt = $scopeTasksForUser($qt);

            if ($filterDate) {
                $qt->whereDate('due_date', $filterDate->toDateString());
            } else {
                $now = now();

                if ($range === 'today') {
                    $qt->whereDate('due_date', $now->toDateString());
                } elseif ($range === 'this_week') {
                    $start = $now->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
                    $end   = $now->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();
                    $qt->whereBetween('due_date', [$start, $end]);
                } elseif ($range === 'this_month') {
                    $start = $now->copy()->startOfMonth()->toDateString();
                    $end   = $now->copy()->endOfMonth()->toDateString();
                    $qt->whereBetween('due_date', [$start, $end]);
                } elseif ($range === 'this_year') {
                    $start = $now->copy()->startOfYear()->toDateString();
                    $end   = $now->copy()->endOfYear()->toDateString();
                    $qt->whereBetween('due_date', [$start, $end]);
                } elseif ($range === 'future_only') {
                    $qt->whereDate('due_date', '>=', $now->toDateString());
                } else {
                    // all -> geen filter
                }
            }

            $planningTasks = $qt
                ->orderByRaw("CASE WHEN completed_at IS NULL THEN 0 ELSE 1 END")
                ->orderByRaw("due_date IS NULL")
                ->orderBy('due_date')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit($perPage)
                ->get();
        }

        // =========================
        // TEAM (ongewijzigd)
        // =========================
        $teamDate = null;
        $teamPrevDate = null;
        $teamNextDate = null;
        $teamDateLabel = null;
        $teamMembers = collect();

        if ($section === 'team') {
            $dateParam = $request->query('date');

            try {
                $teamDate = $dateParam ? Carbon::parse($dateParam)->startOfDay() : now()->startOfDay();
            } catch (\Throwable $e) {
                $teamDate = now()->startOfDay();
            }

            $teamPrevDate  = $teamDate->copy()->subDay();
            $teamNextDate  = $teamDate->copy()->addDay();
            $teamDateLabel = $teamDate->copy()->locale('nl')->translatedFormat('l j F Y');

            $dayStart = $teamDate->copy()->startOfDay();
            $dayEnd   = $teamDate->copy()->endOfDay();

            $photographers = User::query()
                ->where('rol', 'fotograaf')
                ->orderBy('name')
                ->get();

            $itemsThatDay = ProjectPlanningItem::query()
                ->with(['project', 'assignee']);

            $itemsThatDay = $applyOverlapWindow($itemsThatDay, $dayStart, $dayEnd)
                ->get()
                ->groupBy('assignee_user_id');

            $getWorkHoursForDate = function (User $u, Carbon $date): ?array {
                $wh = $u->work_hours;
                if (!is_array($wh)) return null;

                $dowEn = strtolower($date->englishDayOfWeek);
                $dowEn3 = substr($dowEn, 0, 3);

                $nlMap = [
                    'monday'    => ['maandag', 'ma'],
                    'tuesday'   => ['dinsdag', 'di'],
                    'wednesday' => ['woensdag', 'wo'],
                    'thursday'  => ['donderdag', 'do'],
                    'friday'    => ['vrijdag', 'vr'],
                    'saturday'  => ['zaterdag', 'za'],
                    'sunday'    => ['zondag', 'zo'],
                ];

                $candidates = array_merge([$dowEn, $dowEn3], $nlMap[$dowEn] ?? []);
                $dayData = null;

                foreach ($candidates as $key) {
                    if (array_key_exists($key, $wh)) {
                        $dayData = $wh[$key];
                        break;
                    }
                }

                if ($dayData === null && array_is_list($wh)) {
                    $idxIso = $date->dayOfWeekIso - 1;
                    if (isset($wh[$idxIso])) {
                        $dayData = $wh[$idxIso];
                    } elseif (isset($wh[$date->dayOfWeek])) {
                        $dayData = $wh[$date->dayOfWeek];
                    }
                }

                if ($dayData === null || !is_array($dayData)) return null;

                $start = $dayData['start'] ?? $dayData['from'] ?? $dayData['begin'] ?? $dayData['start_time'] ?? null;
                $end   = $dayData['end']   ?? $dayData['to']   ?? $dayData['until'] ?? $dayData['end_time']   ?? null;

                $start = is_string($start) ? trim($start) : null;
                $end   = is_string($end) ? trim($end) : null;

                if (!$start || !$end) return null;

                if (!preg_match('/^\d{1,2}:\d{2}$/', $start)) return null;
                if (!preg_match('/^\d{1,2}:\d{2}$/', $end)) return null;

                return ['start' => $start, 'end' => $end];
            };

            $teamMembers = $photographers
                ->map(function (User $u) use ($itemsThatDay, $teamDate, $getWorkHoursForDate) {
                    $work = $getWorkHoursForDate($u, $teamDate);
                    if (!$work) return null;

                    $items = $itemsThatDay->get($u->id, collect());

                    $availableStart = $work['start'];
                    $availableEnd   = $work['end'];

                    $startAt = Carbon::parse($teamDate->toDateString() . ' ' . $availableStart);
                    $endAt   = Carbon::parse($teamDate->toDateString() . ' ' . $availableEnd);

                    if ($endAt->lessThanOrEqualTo($startAt)) return null;

                    $availableMinutes = $startAt->diffInMinutes($endAt);

                    $assignedMinutes = $items->sum(function (ProjectPlanningItem $pi) {
                        if (!$pi->start_at || !$pi->end_at) return 0;
                        $s = Carbon::parse($pi->start_at);
                        $e = Carbon::parse($pi->end_at);
                        if ($e->lessThanOrEqualTo($s)) return 0;
                        return $s->diffInMinutes($e);
                    });

                    $percent = $availableMinutes > 0 ? (int) round(($assignedMinutes / $availableMinutes) * 100) : 0;
                    $percent = max(0, min(100, $percent));

                    $usedHours = round($assignedMinutes / 60, 1);
                    $totalHours = round($availableMinutes / 60, 1);

                    $firstLocation = $items->first()?->location ?: '-';

                    return [
                        'user' => $u,
                        'location' => $firstLocation,
                        'work_start' => $availableStart,
                        'work_end' => $availableEnd,
                        'tasks_count' => $items->count(),
                        'workload_percent' => $percent,
                        'workload_used_hours' => $usedHours,
                        'workload_total_hours' => $totalHours,
                    ];
                })
                ->filter()
                ->values();
        }

        return view('hub.planning.index', compact(
            'user',
            'section',
            'baseUrl',
            'statusPill',
            'formatTime',
            'selectedDate',
            'prevDate',
            'nextDate',
            'dateLabel',
            'todayRequests',
            'todayTasks',
            'filterDate',
            'perPage',
            'planningItems',
            'planningTasks',
            'teamDate',
            'teamPrevDate',
            'teamNextDate',
            'teamDateLabel',
            'teamMembers'
        ));
    }

    public function edit(ProjectPlanningItem $planningItem)
    {
        $user = auth()->user();

        $photographers = User::query()
            ->where('rol', 'fotograaf')
            ->orderBy('name')
            ->get();

        $planningItem->load(['project.client', 'assignee']);

        return view('hub.planning.edit', compact('user', 'planningItem', 'photographers'));
    }

    public function update(Request $request, ProjectPlanningItem $planningItem)
    {
        $validated = $request->validate([
            'start_at' => ['required', 'date'],
            'end_at'   => ['required', 'date', 'after:start_at'],
            'assignee_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes'    => ['nullable', 'string'],
        ]);

        $planningItem->update($validated);

        $date = Carbon::parse($validated['start_at'])->toDateString();

        return redirect()
            ->route('support.planning.index', ['section' => 'today', 'date' => $date])
            ->with('success', 'Planning item is bijgewerkt.');
    }

    public function destroy(ProjectPlanningItem $planningItem)
    {
        $date = $planningItem->start_at ? Carbon::parse($planningItem->start_at)->toDateString() : now()->toDateString();

        $planningItem->delete();

        return redirect()
            ->route('support.planning.index', ['section' => 'today', 'date' => $date])
            ->with('success', 'Planning item is verwijderd.');
    }
}
