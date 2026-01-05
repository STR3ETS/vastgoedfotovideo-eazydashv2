<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

            // beide leeg = geen werktijd
            if ($start === '' && $end === '') {
                $out[$dayKey] = null;
                continue;
            }

            // één ingevuld = we bewaren wat er is (validator vangt errors)
            $out[$dayKey] = [
                'start' => $start !== '' ? $start : null,
                'end'   => $end !== '' ? $end : null,
            ];
        }

        // als alles null is, return null
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

                // als één van beide is ingevuld, moet de ander ook
                if (($start && !$end) || (!$start && $end)) {
                    $v->errors()->add("work_hours.$dayKey.start", 'Vul beide tijden in (start en eind).');
                    $v->errors()->add("work_hours.$dayKey.end", 'Vul beide tijden in (start en eind).');
                    continue;
                }

                // als beide ingevuld zijn: end moet later zijn dan start
                if ($start && $end && $end <= $start) {
                    $v->errors()->add("work_hours.$dayKey.end", 'Eindtijd moet later zijn dan starttijd.');
                }
            }
        });

        return $validator->validate();
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $activeRole = $this->safeRoleOrDefault($request->query('rol', 'klant'));
        $activeRoleLabel = $this->roleLabel($activeRole);

        $q = $this->extractQ($request) ?? '';

        return view('hub.gebruikers.index', compact('user', 'activeRole', 'activeRoleLabel', 'q'));
    }

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
                        ->orWhere('email', 'like', '%' . $q . '%');
                });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'rol']);

        return view('hub.gebruikers.partials.users_list', compact('users', 'rol', 'rolLabel', 'q'));
    }

    public function store(Request $request)
    {
        $this->assertAdmin();

        $roleNormalized = $this->safeRoleOrDefault($request->input('rol'));
        $rolLabel = $this->roleLabel($roleNormalized);

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],

            'address'        => ['nullable', 'string', 'max:255'],
            'city'           => ['nullable', 'string', 'max:255'],
            'state_province' => ['nullable', 'string', 'max:255'],
            'postal_code'    => ['nullable', 'string', 'max:32'],
            'phone'          => ['nullable', 'string', 'max:32'],
        ]);

        // ✅ werktijden valideren (optioneel)
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

                // ✅ werktijden
                'work_hours' => $workHours,

                'password'   => bcrypt(Str::random(24)),
            ]);
        });

        $q = $this->extractQ($request);

        $users = User::query()
            ->where('rol', $roleNormalized)
            ->when($q, function ($qb) use ($q) {
                $qb->where(function ($q2) use ($q) {
                    $q2->where('name', 'like', '%' . $q . '%')
                        ->orWhere('email', 'like', '%' . $q . '%');
                });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'rol']);

        return response()
            ->view('hub.gebruikers.partials.users_list', [
                'users'    => $users,
                'rol'      => $roleNormalized,
                'rolLabel' => $rolLabel,
                'q'        => $q ?? '',
            ], 200)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function show(Request $request, User $user)
    {
        if ($this->isHtmx($request)) {
            return view('hub.gebruikers.partials.user_detail', compact('user'));
        }

        return redirect()->route('support.gebruikers.index', ['rol' => $user->rol]);
    }

    public function update(Request $request, User $user)
    {
        $this->assertAdmin();

        $roleNormalized = $this->safeRoleOrDefault($request->input('rol'));

        $validated = $request->validate([
            'name'  => ['required','string','max:255'],
            'email' => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],

            'address'        => ['nullable', 'string', 'max:255'],
            'city'           => ['nullable', 'string', 'max:255'],
            'state_province' => ['nullable', 'string', 'max:255'],
            'postal_code'    => ['nullable', 'string', 'max:32'],
            'phone'          => ['nullable', 'string', 'max:32'],
        ]);

        // ✅ werktijden valideren
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

            // ✅ werktijden
            'work_hours' => $workHours,
        ]);

        if ($this->isHtmx($request)) {
            $detail = view('hub.gebruikers.partials.user_detail', ['user' => $user])->render();
            return response($detail, 200)->header('Content-Type', 'text/html; charset=UTF-8');
        }

        return back();
    }

    public function destroy(Request $request, User $user)
    {
        $this->assertAdmin();

        $role = $user->rol;
        $user->delete();

        if ($this->isHtmx($request)) {
            $q = $this->extractQ($request);

            $users = User::query()
                ->where('rol', $role)
                ->when($q, function ($qb) use ($q) {
                    $qb->where(function ($q2) use ($q) {
                        $q2->where('name', 'like', '%' . $q . '%')
                            ->orWhere('email', 'like', '%' . $q . '%');
                    });
                })
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'rol']);

            $list = view('hub.gebruikers.partials.users_list', [
                'users'    => $users,
                'rol'      => $role,
                'rolLabel' => $this->roleLabel($role),
                'q'        => $q ?? '',
            ])->render();

            $close = '<div id="user-detail-card" hx-swap-oob="true" class="hidden col-span-1 bg-white rounded-xl h-full min-h-0 flex flex-col"></div>';

            return response($list . "\n" . $close, 200)->header('Content-Type', 'text/html; charset=UTF-8');
        }

        return back();
    }
}
