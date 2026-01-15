<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectPlanningItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class GebruikersController extends Controller
{
    // ✅ Slugs zoals in DB
    private const ROLE_LABELS = [
        'admin'          => 'Admin',
        'klant'          => 'Klant',
        'team-manager'   => 'Team manager',
        'client-manager' => 'Klant manager',
        'fotograaf'      => 'Fotograaf',
    ];

    // ✅ Dagen (key => label)
    private const WORK_DAYS = [
        'monday'    => 'Maandag',
        'tuesday'   => 'Dinsdag',
        'wednesday' => 'Woensdag',
        'thursday'  => 'Donderdag',
        'friday'    => 'Vrijdag',
        'saturday'  => 'Zaterdag',
        'sunday'    => 'Zondag',
    ];

    // -----------------------------
    // Helpers
    // -----------------------------

    private function normalizeRole(?string $role): ?string
    {
        if ($role === null) return null;

        $role = trim($role);
        if ($role === '') return null;

        $lower = mb_strtolower($role);

        $map = [
            'admin' => 'admin',
            'klant' => 'klant',
            'fotograaf' => 'fotograaf',

            'team-manager' => 'team-manager',
            'team manager' => 'team-manager',
            'team_manager' => 'team-manager',

            'client-manager' => 'client-manager',
            'client manager' => 'client-manager',
            'client_manager' => 'client-manager',

            // label-variant
            'klant manager' => 'client-manager',
            'klant-manager' => 'client-manager',
            'klant_manager' => 'client-manager',
        ];

        return $map[$lower] ?? $role;
    }

    private function safeRoleOrDefault(?string $role): string
    {
        $role = $this->normalizeRole($role);
        return array_key_exists($role, self::ROLE_LABELS) ? $role : 'klant';
    }

    private function roleLabel(string $slug): string
    {
        return self::ROLE_LABELS[$slug] ?? $slug;
    }

    private function assertAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->rol === 'admin', 403);
    }

    protected function isHtmx(Request $request): bool
    {
        return $request->header('HX-Request') === 'true' || $request->ajax();
    }

    protected function extractQ(Request $request): ?string
    {
        $q = trim((string) $request->query('q', ''));
        if ($q !== '') return $q;

        $current = (string) $request->header('HX-Current-URL', '');
        if ($current) {
            $parts = parse_url($current);
            if (!empty($parts['query'])) {
                parse_str($parts['query'], $query);
                if (!empty($query['q'])) return trim((string) $query['q']);
            }
        }

        return null;
    }

    private function normalizeWorkHours(?array $input): ?array
    {
        if (!$input || !is_array($input)) {
            return null;
        }

        $out = [];

        foreach (self::WORK_DAYS as $dayKey => $dayLabel) {
            $start = trim((string) data_get($input, $dayKey . '.start', ''));
            $end   = trim((string) data_get($input, $dayKey . '.end', ''));

            if ($start === '' && $end === '') {
                $out[$dayKey] = null;
                continue;
            }

            $out[$dayKey] = [
                'start' => $start !== '' ? $start : null,
                'end'   => $end !== '' ? $end : null,
            ];
        }

        $hasAny = false;
        foreach ($out as $v) {
            if (is_array($v) && ($v['start'] || $v['end'])) {
                $hasAny = true;
                break;
            }
        }

        return $hasAny ? $out : null;
    }

    private function validateWorkHours(Request $request): array
    {
        $rules = [
            'work_hours' => ['nullable', 'array'],
        ];

        foreach (array_keys(self::WORK_DAYS) as $dayKey) {
            $rules["work_hours.$dayKey.start"] = ['nullable', 'date_format:H:i'];
            $rules["work_hours.$dayKey.end"]   = ['nullable', 'date_format:H:i'];
        }

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($v) use ($request) {
            $wh = $request->input('work_hours', []);
            foreach (array_keys(self::WORK_DAYS) as $dayKey) {
                $start = data_get($wh, "$dayKey.start");
                $end   = data_get($wh, "$dayKey.end");

                if (($start && !$end) || (!$start && $end)) {
                    $v->errors()->add("work_hours.$dayKey.start", 'Vul beide tijden in (start en eind).');
                    $v->errors()->add("work_hours.$dayKey.end", 'Vul beide tijden in (start en eind).');
                    continue;
                }

                if ($start && $end && $end <= $start) {
                    $v->errors()->add("work_hours.$dayKey.end", 'Eindtijd moet later zijn dan starttijd.');
                }
            }
        });

        return $validator->validate();
    }

    private function applySort($qb, string $sort)
    {
        return match ($sort) {
            'oldest'    => $qb->orderBy('created_at', 'asc')->orderBy('id', 'asc'),
            'name_asc'  => $qb->orderBy('name', 'asc')->orderBy('id', 'asc'),
            'name_desc' => $qb->orderBy('name', 'desc')->orderBy('id', 'desc'),
            'email'     => $qb->orderBy('email', 'asc')->orderBy('id', 'asc'),
            default     => $qb->orderBy('created_at', 'desc')->orderBy('id', 'desc'), // newest
        };
    }

    /**
     * ✅ Leest terug-navigatie context uit hidden fields:
     * _back[rol], _back[q], _back[sort], _back[tab]
     */
    private function backParamsFromRequest(Request $request): array
    {
        $b = (array) $request->input('_back', []);

        $rolRaw = trim((string) data_get($b, 'rol', ''));
        $rol    = $rolRaw !== '' ? $this->safeRoleOrDefault($rolRaw) : null;

        $q    = trim((string) data_get($b, 'q', ''));
        $sort = (string) data_get($b, 'sort', 'newest');
        $tab  = trim((string) data_get($b, 'tab', ''));

        return array_filter([
            'rol'  => $rol ?: null,
            'q'    => $q ?: null,
            'sort' => $sort ?: null,
            'tab'  => $tab ?: null,
        ]);
    }

    private function fetchProjectsForUser(User $user)
    {
        if (!Schema::hasTable('projects')) {
            return collect();
        }

        // detecteer mogelijke koppelkolom
        $candidateCols = ['client_id', 'customer_id', 'user_id', 'klant_id'];
        $col = null;

        foreach ($candidateCols as $c) {
            if (Schema::hasColumn('projects', $c)) {
                $col = $c;
                break;
            }
        }

        if (!$col) {
            return collect();
        }

        return Project::query()
            ->where($col, $user->id)
            ->orderByDesc('id')
            ->limit(100)
            ->get();
    }

    private function fetchPlanningForUser(User $user)
    {
        // jij gebruikt ProjectPlanningItem elders; tabel/kolommen kunnen verschillen
        if (!Schema::hasTable('project_planning_items')) {
            return collect();
        }

        $qb = ProjectPlanningItem::query();

        if (Schema::hasColumn('project_planning_items', 'assignee_id')) {
            $qb->where('assignee_id', $user->id);
        } elseif (Schema::hasColumn('project_planning_items', 'user_id')) {
            $qb->where('user_id', $user->id);
        } else {
            return collect();
        }

        $orderCol = null;
        foreach (['start_at', 'start', 'starts_at', 'created_at', 'id'] as $c) {
            if (Schema::hasColumn('project_planning_items', $c)) {
                $orderCol = $c;
                break;
            }
        }

        return $qb->orderByDesc($orderCol ?: 'id')
            ->limit(100)
            ->get();
    }

    // -----------------------------
    // Routes
    // -----------------------------

    /**
     * ✅ index: zonder ?rol= => ALLE gebruikers
     * kolommen in jouw Blade: Naam, e-mail, telefoon, rol, acties
     */
    public function index(Request $request)
    {
        $authUser = auth()->user();

        $rolRaw = trim((string) $request->query('rol', ''));
        $rol = $rolRaw !== '' ? $this->safeRoleOrDefault($rolRaw) : null;
        $rolLabel = $rol ? $this->roleLabel($rol) : 'Alle gebruikers';

        $q    = trim((string) $request->query('q', $this->extractQ($request) ?? ''));
        $sort = (string) $request->query('sort', 'newest');

        $qb = User::query()
            ->when($rol, fn($qb) => $qb->where('rol', $rol))
            ->when($q !== '', function ($qb) use ($q) {
                $qb->where(function ($q2) use ($q) {
                    $q2->where('name', 'like', '%' . $q . '%')
                        ->orWhere('email', 'like', '%' . $q . '%')
                        ->orWhere('phone', 'like', '%' . $q . '%');
                });
            });

        $this->applySort($qb, $sort);

        $rows = $qb->paginate(25)->withQueryString();

        return view('hub.gebruikers.index', [
            'user' => $authUser,
            'rows' => $rows,
            'rol'  => $rol,
            'rolLabel' => $rolLabel,
            'q'    => $q,
            'sort' => $sort,
        ]);
    }

    /**
     * (optioneel) oude HTMX lijst: /lijst/{rol}
     */
    public function lijst(Request $request, string $rol)
    {
        $rol = $this->safeRoleOrDefault($rol);
        $rolLabel = $this->roleLabel($rol);

        $q = $this->extractQ($request);

        $users = User::query()
            ->where('rol', $rol)
            ->when($q, function ($qb) use ($q) {
                $qb->where(function ($q2) use ($q) {
                    $q2->where('name', 'like', '%' . $q . '%')
                        ->orWhere('email', 'like', '%' . $q . '%')
                        ->orWhere('phone', 'like', '%' . $q . '%');
                });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'rol']);

        return view('hub.gebruikers.partials.users_list', compact('users', 'rol', 'rolLabel', 'q'));
    }

    public function store(Request $request)
    {
        $this->assertAdmin();

        $roleNormalized = $this->safeRoleOrDefault($request->input('rol'));

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],

            'address'        => ['nullable', 'string', 'max:255'],
            'city'           => ['nullable', 'string', 'max:255'],
            'state_province' => ['nullable', 'string', 'max:255'],
            'postal_code'    => ['nullable', 'string', 'max:32'],
            'phone'          => ['nullable', 'string', 'max:32'],
        ]);

        $this->validateWorkHours($request);
        $workHours = $this->normalizeWorkHours($request->input('work_hours'));

        DB::transaction(function () use ($validated, $roleNormalized, $workHours) {
            User::create([
                'name'       => $validated['name'],
                'email'      => $validated['email'],
                'rol'        => $roleNormalized,
                'company_id' => null,

                'address'        => $validated['address'] ?? null,
                'city'           => $validated['city'] ?? null,
                'state_province' => $validated['state_province'] ?? null,
                'postal_code'    => $validated['postal_code'] ?? null,
                'phone'          => $validated['phone'] ?? null,

                'work_hours' => $workHours,

                'password'   => bcrypt(Str::random(24)),
            ]);
        });

        return redirect()->route('support.gebruikers.index', [
            'rol' => $roleNormalized,
        ]);
    }

    /**
     * ✅ show: full page user hub (bewerken + werkuren + planning + projecten)
     */
    public function show(Request $request, User $user)
    {
        if ($this->isHtmx($request)) {
            return view('hub.gebruikers.partials.user_detail', compact('user'));
        }

        $authUser = auth()->user();

        // ✅ rol mag leeg zijn (alle)
        $rolRaw = trim((string) $request->query('rol', ''));
        $rol    = $rolRaw !== '' ? $this->safeRoleOrDefault($rolRaw) : null;

        $q    = trim((string) $request->query('q', ''));
        $sort = (string) $request->query('sort', 'newest');

        $projects      = $this->fetchProjectsForUser($user);
        $planningItems = $this->fetchPlanningForUser($user);

        return view('hub.gebruikers.show', [
            'user'          => $authUser,  // layout
            'targetUser'    => $user,      // detail user
            'rol'           => $rol,
            'q'             => $q,
            'sort'          => $sort,
            'projects'      => $projects,
            'planningItems' => $planningItems,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->assertAdmin();

        $roleNormalized = $this->safeRoleOrDefault($request->input('rol'));

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],

            'address'        => ['nullable', 'string', 'max:255'],
            'city'           => ['nullable', 'string', 'max:255'],
            'state_province' => ['nullable', 'string', 'max:255'],
            'postal_code'    => ['nullable', 'string', 'max:32'],
            'phone'          => ['nullable', 'string', 'max:32'],
        ]);

        $this->validateWorkHours($request);
        $workHours = $this->normalizeWorkHours($request->input('work_hours'));

        $user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
            'rol'   => $roleNormalized,

            'address'        => $validated['address'] ?? null,
            'city'           => $validated['city'] ?? null,
            'state_province' => $validated['state_province'] ?? null,
            'postal_code'    => $validated['postal_code'] ?? null,
            'phone'          => $validated['phone'] ?? null,

            'work_hours' => $workHours,
        ]);

        if ($this->isHtmx($request)) {
            $detail = view('hub.gebruikers.partials.user_detail', ['user' => $user])->render();
            return response($detail, 200)->header('Content-Type', 'text/html; charset=UTF-8');
        }

        $qs = $this->backParamsFromRequest($request);

        return redirect()->route('support.gebruikers.show', ['user' => $user->id] + $qs);
    }

    public function destroy(Request $request, User $user)
    {
        $this->assertAdmin();

        // ✅ nooit jezelf verwijderen
        abort_if($user->id === auth()->id(), 403, 'Je kunt je eigen account niet verwijderen.');

        // (optioneel) nooit laatste admin verwijderen
        if ($user->rol === 'admin') {
            $admins = User::where('rol', 'admin')->count();
            abort_if($admins <= 1, 403, 'Je kunt de laatste admin niet verwijderen.');
        }

        $user->delete();

        if ($this->isHtmx($request)) {
            return response('', 200);
        }

        $qs = $this->backParamsFromRequest($request);

        return redirect()->route('support.gebruikers.index', $qs);
    }
}
