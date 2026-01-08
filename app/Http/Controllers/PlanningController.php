<?php

namespace App\Http\Controllers;

use App\Models\ProjectPlanningItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PlanningController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // today | planning | qc | team
        // ✅ Default = today (geen redirect, dus /planning-management werkt direct)
        $section = $request->query('section', 'today');

        $baseUrl = url('/app/planning-management');

        // =========================
        // Helpers
        // =========================
        $statusPill = function (?string $status) {
            $status = strtolower(trim((string) $status));

            return match ($status) {
                'new', 'nieuw' => [
                    'label' => 'Nieuw',
                    'class' => 'text-[#2A324B] bg-[#2A324B]/15',
                ],
                'planned', 'ingepland' => [
                    'label' => 'Ingepland',
                    'class' => 'text-[#87A878] bg-[#87A878]/15',
                ],
                'done', 'afgerond' => [
                    'label' => 'Afgerond',
                    'class' => 'text-[#009AC3] bg-[#009AC3]/15',
                ],
                'active' => [
                    'label' => 'Actief',
                    'class' => 'text-[#009AC3] bg-[#009AC3]/15',
                ],
                'accepted' => [
                    'label' => 'Geaccepteerd',
                    'class' => 'text-[#87A878] bg-[#87A878]/15',
                ],
                'cancelled', 'canceled', 'geannuleerd' => [
                    'label' => 'Geannuleerd',
                    'class' => 'text-[#DF2935] bg-[#DF2935]/15',
                ],
                default => [
                    'label' => $status !== '' ? ucfirst($status) : 'Onbekend',
                    'class' => 'text-[#2A324B] bg-[#2A324B]/15',
                ],
            };
        };

        $formatTime = function ($dt) {
            if (!$dt) return '-';
            return Carbon::parse($dt)->format('H:i');
        };

        $getWorkHoursForDate = function (User $u, Carbon $date): ?array {
            $wh = $u->work_hours;

            if (!is_array($wh)) return null;

            // dag keys
            $dowEn = strtolower($date->englishDayOfWeek); // monday
            $dowEn3 = substr($dowEn, 0, 3); // mon

            $nlMap = [
                'monday'    => ['maandag', 'ma'],
                'tuesday'   => ['dinsdag', 'di'],
                'wednesday' => ['woensdag', 'wo'],
                'thursday'  => ['donderdag', 'do'],
                'friday'    => ['vrijdag', 'vr'],
                'saturday'  => ['zaterdag', 'za'],
                'sunday'    => ['zondag', 'zo'],
            ];

            $candidates = array_merge(
                [$dowEn, $dowEn3],
                $nlMap[$dowEn] ?? []
            );

            $dayData = null;

            // support: array met keys per dag
            foreach ($candidates as $key) {
                if (array_key_exists($key, $wh)) {
                    $dayData = $wh[$key];
                    break;
                }
            }

            // support: indexed array 0..6 (ma..zo of zo..za)
            if ($dayData === null && array_is_list($wh)) {
                // ISO: ma=1..zo=7 -> index 0..6
                $idxIso = $date->dayOfWeekIso - 1;

                if (isset($wh[$idxIso])) {
                    $dayData = $wh[$idxIso];
                } elseif (isset($wh[$date->dayOfWeek])) {
                    // Carbon dayOfWeek: zo=0..za=6
                    $dayData = $wh[$date->dayOfWeek];
                }
            }

            if ($dayData === null || !is_array($dayData)) return null;

            $start = $dayData['start'] ?? $dayData['from'] ?? $dayData['begin'] ?? $dayData['start_time'] ?? null;
            $end   = $dayData['end']   ?? $dayData['to']   ?? $dayData['until'] ?? $dayData['end_time']   ?? null;

            $start = is_string($start) ? trim($start) : null;
            $end   = is_string($end) ? trim($end) : null;

            if (!$start || !$end) return null;

            // simpele validatie HH:MM
            if (!preg_match('/^\d{1,2}:\d{2}$/', $start)) return null;
            if (!preg_match('/^\d{1,2}:\d{2}$/', $end)) return null;

            return ['start' => $start, 'end' => $end];
        };

        $applyOverlapWindow = function ($query, Carbon $start, Carbon $end) {
            // Overlap logic: items die (deels) binnen window vallen
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
        // TODAY
        // =========================
        $selectedDate  = null;
        $prevDate      = null;
        $nextDate      = null;
        $dateLabel     = null;
        $todayRequests = collect();

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

            $todayRequests = ProjectPlanningItem::query()
                ->with([
                    'project.onboardingRequest',
                    'project.client',
                    'assignee',
                ]);

            $todayRequests = $applyOverlapWindow($todayRequests, $dayStart, $dayEnd)
                ->orderBy('start_at', 'asc')
                ->get();
        }

        // =========================
        // PLANNING (lijst / map / calendar)
        // =========================
        $filterDate = null;

        $perPage = (int) $request->query('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $planningItems = collect();

        if ($section === 'planning') {
            $filterDateParam = $request->query('date');

            try {
                $filterDate = $filterDateParam ? Carbon::parse($filterDateParam)->startOfDay() : null;
            } catch (\Throwable $e) {
                $filterDate = null;
            }

            $view = $request->query('view', 'list');
            if (!in_array($view, ['list','map','calendar'], true)) $view = 'list';

            $range = $request->query('range', 'all');
            if (!in_array($range, ['all','today','this_week','this_month','this_year','future_only'], true)) $range = 'all';

            // =========================================
            // ✅ AJAX feed voor FullCalendar
            // =========================================
            if ($view === 'calendar' && $request->boolean('ajax')) {
                // FullCalendar stuurt start/end (ISO)
                $startParam = $request->query('start');
                $endParam   = $request->query('end');

                // fallback window: deze maand
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

                // Exacte datum heeft altijd voorrang
                if ($filterDate) {
                    $windowStart = $filterDate->copy()->startOfDay();
                    $windowEnd   = $filterDate->copy()->endOfDay();
                }

                // Range boundaries (intersectie met window)
                $rangeStart = null;
                $rangeEnd = null;

                if (!$filterDate) {
                    if ($range === 'today') {
                        $rangeStart = now()->startOfDay();
                        $rangeEnd   = now()->endOfDay();
                    } elseif ($range === 'this_week') {
                        $rangeStart = now()->startOfWeek(Carbon::MONDAY)->startOfDay();
                        $rangeEnd   = now()->endOfWeek(Carbon::SUNDAY)->endOfDay();
                    } elseif ($range === 'this_month') {
                        $rangeStart = now()->startOfMonth()->startOfDay();
                        $rangeEnd   = now()->endOfMonth()->endOfDay();
                    } elseif ($range === 'this_year') {
                        $rangeStart = now()->startOfYear()->startOfDay();
                        $rangeEnd   = now()->endOfYear()->endOfDay();
                    } elseif ($range === 'future_only') {
                        $rangeStart = now()->startOfDay();
                        $rangeEnd   = null; // alleen vanaf nu
                    }
                }

                if ($rangeStart) {
                    $windowStart = $windowStart->greaterThan($rangeStart) ? $windowStart : $rangeStart;
                }
                if ($rangeEnd) {
                    $windowEnd = $windowEnd->lessThan($rangeEnd) ? $windowEnd : $rangeEnd;
                }

                if ($windowEnd->lessThan($windowStart)) {
                    return response()->json([]);
                }

                $items = ProjectPlanningItem::query()
                    ->with([
                        'project.onboardingRequest',
                        'project.client',
                        'assignee',
                    ])
                    ->where(function ($q) use ($windowStart, $windowEnd) {
                        $q->whereBetween('start_at', [$windowStart, $windowEnd])
                        ->orWhereBetween('end_at', [$windowStart, $windowEnd])
                        ->orWhere(function ($qq) use ($windowStart, $windowEnd) {
                            $qq->where('start_at', '<=', $windowStart)
                                ->where('end_at', '>=', $windowEnd);
                        });
                    })
                    ->orderBy('start_at', 'asc')
                    ->get();

                $events = $items->map(function (ProjectPlanningItem $pi) {
                    $project = $pi->project;

                    $title = trim(($project?->title ?: 'Project') . ' · ' . ($project?->client?->name ?: ''));

                    return [
                        'id' => (string) $pi->id,
                        'title' => $title !== '·' ? $title : 'Planning',
                        'start' => $pi->start_at ? Carbon::parse($pi->start_at)->toIso8601String() : null,
                        'end' => $pi->end_at ? Carbon::parse($pi->end_at)->toIso8601String() : null,
                        'url' => $project ? route('support.projecten.show', $project) : null,
                        'extendedProps' => [
                            'location' => (string) ($pi->location ?? ''),
                            'client' => (string) ($project?->client?->name ?? ''),
                            'assignee' => (string) ($pi->assignee?->name ?? 'Niet toegewezen'),
                        ],
                    ];
                })->values();

                return response()->json($events);
            }

            // =========================================
            // ✅ Normale list/map data (met filters)
            // =========================================
            $q = ProjectPlanningItem::query()
                ->with([
                    'project.onboardingRequest',
                    'project.client',
                    'assignee',
                ]);

            if ($filterDate) {
                $dayStart = $filterDate->copy()->startOfDay();
                $dayEnd   = $filterDate->copy()->endOfDay();

                $q->where(function ($qq) use ($dayStart, $dayEnd) {
                    $qq->whereBetween('start_at', [$dayStart, $dayEnd])
                    ->orWhereBetween('end_at', [$dayStart, $dayEnd])
                    ->orWhere(function ($qqq) use ($dayStart, $dayEnd) {
                        $qqq->where('start_at', '<=', $dayStart)
                            ->where('end_at', '>=', $dayEnd);
                    });
                });
            } else {
                // range filter als er geen exacte datum is
                if ($range !== 'all') {
                    $rangeStart = null;
                    $rangeEnd = null;

                    if ($range === 'today') {
                        $rangeStart = now()->startOfDay();
                        $rangeEnd   = now()->endOfDay();
                    } elseif ($range === 'this_week') {
                        $rangeStart = now()->startOfWeek(Carbon::MONDAY)->startOfDay();
                        $rangeEnd   = now()->endOfWeek(Carbon::SUNDAY)->endOfDay();
                    } elseif ($range === 'this_month') {
                        $rangeStart = now()->startOfMonth()->startOfDay();
                        $rangeEnd   = now()->endOfMonth()->endOfDay();
                    } elseif ($range === 'this_year') {
                        $rangeStart = now()->startOfYear()->startOfDay();
                        $rangeEnd   = now()->endOfYear()->endOfDay();
                    } elseif ($range === 'future_only') {
                        $rangeStart = now()->startOfDay();
                        $rangeEnd   = null;
                    }

                    if ($rangeStart || $rangeEnd) {
                        $rangeStart = $rangeStart ?: Carbon::create(1970, 1, 1)->startOfDay();
                        $rangeEnd   = $rangeEnd ?: Carbon::create(2100, 1, 1)->endOfDay();

                        $q->where(function ($qq) use ($rangeStart, $rangeEnd) {
                            $qq->whereBetween('start_at', [$rangeStart, $rangeEnd])
                            ->orWhereBetween('end_at', [$rangeStart, $rangeEnd])
                            ->orWhere(function ($qqq) use ($rangeStart, $rangeEnd) {
                                $qqq->where('start_at', '<=', $rangeStart)
                                    ->where('end_at', '>=', $rangeEnd);
                            });
                        });
                    }
                }
            }

            // Sorteer: eerst volgende planning bovenaan (handig voor kaartnummers en lijst)
            $planningItems = $q
                ->orderBy('start_at', 'asc')
                ->get();

            // per_page toepassen alleen voor list/map (niet calendar)
            if ($view !== 'calendar') {
                $planningItems = $planningItems->take($perPage)->values();
            }
        }

        $filterDate = null;

        $perPage = (int) $request->query('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $planningItems = collect();

        if ($section === 'planning') {
            // ✅ range uit dropdown (hier zat je bug: je gebruikte dit nergens)
            $range = $request->query('range', 'all');
            if (!in_array($range, ['all','today','this_week','this_month','this_year','future_only'], true)) {
                $range = 'all';
            }

            // ✅ exacte datum heeft voorrang
            $filterDateParam = $request->query('date');
            try {
                $filterDate = $filterDateParam ? Carbon::parse($filterDateParam)->startOfDay() : null;
            } catch (\Throwable $e) {
                $filterDate = null;
            }

            $q = ProjectPlanningItem::query()
                ->with([
                    'project.onboardingRequest',
                    'project.client',
                    'assignee',
                ]);

            if ($filterDate) {
                $dayStart = $filterDate->copy()->startOfDay();
                $dayEnd   = $filterDate->copy()->endOfDay();

                $q = $applyOverlapWindow($q, $dayStart, $dayEnd);
            } else {
                // ✅ range window
                $now = now();

                if ($range === 'today') {
                    $start = $now->copy()->startOfDay();
                    $end   = $now->copy()->endOfDay();
                    $q = $applyOverlapWindow($q, $start, $end);
                }

                if ($range === 'this_week') {
                    $start = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
                    $end   = $now->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
                    $q = $applyOverlapWindow($q, $start, $end);
                }

                if ($range === 'this_month') {
                    $start = $now->copy()->startOfMonth()->startOfDay();
                    $end   = $now->copy()->endOfMonth()->endOfDay();
                    $q = $applyOverlapWindow($q, $start, $end);
                }

                if ($range === 'this_year') {
                    $start = $now->copy()->startOfYear()->startOfDay();
                    $end   = $now->copy()->endOfYear()->endOfDay();
                    $q = $applyOverlapWindow($q, $start, $end);
                }

                if ($range === 'future_only') {
                    // alles wat nog niet voorbij is
                    $q->where(function ($qq) use ($now) {
                        $qq->where('start_at', '>=', $now)
                           ->orWhere('end_at', '>=', $now);
                    });
                }

                // range=all -> geen extra where
            }

            $now = now();

            $planningItems = $q
                ->orderByRaw("
                    CASE
                        WHEN start_at IS NULL THEN 2
                        WHEN start_at >= ? THEN 0
                        ELSE 1
                    END
                ", [$now])
                ->orderBy('start_at', 'asc')
                ->limit($perPage)
                ->get();

        }

        // =========================
        // TEAM
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

            $teamMembers = $photographers
                ->map(function (User $u) use ($itemsThatDay, $teamDate, $getWorkHoursForDate) {
                    $work = $getWorkHoursForDate($u, $teamDate);

                    // Alleen mensen die die dag werken
                    if (!$work) {
                        return null;
                    }

                    $items = $itemsThatDay->get($u->id, collect());

                    $availableStart = $work['start'];
                    $availableEnd   = $work['end'];

                    $startAt = Carbon::parse($teamDate->toDateString() . ' ' . $availableStart);
                    $endAt   = Carbon::parse($teamDate->toDateString() . ' ' . $availableEnd);

                    if ($endAt->lessThanOrEqualTo($startAt)) {
                        return null;
                    }

                    $availableMinutes = $startAt->diffInMinutes($endAt);

                    $assignedMinutes = $items->sum(function (ProjectPlanningItem $pi) {
                        if (!$pi->start_at || !$pi->end_at) return 0;

                        $s = Carbon::parse($pi->start_at);
                        $e = Carbon::parse($pi->end_at);

                        if ($e->lessThanOrEqualTo($s)) return 0;

                        return $s->diffInMinutes($e);
                    });

                    $percent = $availableMinutes > 0
                        ? (int) round(($assignedMinutes / $availableMinutes) * 100)
                        : 0;

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

            'filterDate',
            'perPage',
            'planningItems',

            'teamDate',
            'teamPrevDate',
            'teamNextDate',
            'teamDateLabel',
            'teamMembers'
        ));
    }

    // ✅ Edit / Update / Delete voor ProjectPlanningItem (want je overzichten komen daaruit)
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
