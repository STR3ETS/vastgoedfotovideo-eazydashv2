<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

class GebruikersController extends Controller
{
    protected function canManageCompany(Company $company): bool
    {
        $auth = auth()->user();

        if (!$auth) return false;

        // Platform admin (mag altijd)
        if ($auth->rol === 'admin') return true;

        // Company admin binnen hetzelfde bedrijf
        return $auth->rol === 'klant'
            && (int)$auth->company_id === (int)$company->id
            && (bool)$auth->is_company_admin === true;
    }

    /** Full-page entry: de index met tabs/kolommen */
    public function index()
    {
        $user = auth()->user();
        return view('hub.gebruikers.index', compact('user'));
    }

    /** Lijst met klanten (HTMX → partial, anders index) */
    public function klanten(Request $request)
    {
        $q = $this->extractQ($request);

        $klanten = User::query()
            ->where('rol', 'klant')
            ->when($q, fn($qb) => $qb->where('name', 'like', '%'.$q.'%'))
            ->orderBy('name')
            ->get(['id','name','email']);

        if ($this->isHtmx($request)) {
            return view('hub.gebruikers.partials.klanten_list', compact('klanten'));
        }

        $user = auth()->user();
        $bootstrap = [
            'activeTab'      => 'klanten',
            'listUrl'        => route('support.gebruikers.klanten', $q ? ['q'=>$q] : []),
            'prefetchDetail' => null,
        ];
        return view('hub.gebruikers.index', compact('user','bootstrap'));
    }

    /** Lijst met medewerkers (HTMX → partial, anders index) */
    public function medewerkers(Request $request)
    {
        $q = $this->extractQ($request);

        $medewerkers = User::query()
            ->whereIn('rol', ['medewerker','admin'])
            ->when($q, fn($qb) => $qb->where('name', 'like', '%'.$q.'%'))
            ->orderBy('name')
            ->get(['id','name','email','rol']);

        if ($this->isHtmx($request)) {
            return view('hub.gebruikers.partials.medewerkers_list', compact('medewerkers'));
        }

        $user = auth()->user();
        $bootstrap = [
            'activeTab'      => 'medewerkers',
            'listUrl'        => route('support.gebruikers.medewerkers', $q ? ['q'=>$q] : []),
            'prefetchDetail' => null,
        ];
        return view('hub.gebruikers.index', compact('user','bootstrap'));
    }

    /** Aanmaken (Klant of Medewerker); return meteen de juiste lijst-partial */
    public function store(Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->rol === 'admin', 403);

        // fallback: als rol leeg en context=klanten → zet op 'klant'
        if (!$request->input('rol') && $request->input('context') === 'klanten') {
            $request->merge(['rol' => 'klant']);
        }

        $validated = $request->validate([
            'name'        => ['required','string','max:255'],
            'email'       => ['required','email','max:255','unique:users,email'],
            'rol'         => ['required','in:klant,medewerker,admin'],
            'context'     => ['required','in:klanten,medewerkers'],
            // ✅ company_id is optioneel en moet bestaan als hij is meegegeven
            'company_id'  => ['nullable','integer','exists:companies,id'],
        ]);

        $newUser = DB::transaction(function () use ($validated) {
            $creator   = auth()->user();
            $companyId = $validated['company_id'] ?? null;

            // ❗️Nooit automatisch een company aanmaken
            // Optioneel: voor medewerker/admin de company van de aanmaker gebruiken als niets meegegeven is
            if (!$companyId && in_array($validated['rol'], ['medewerker','admin'])) {
                $companyId = $creator->company_id; // laat dit staan of verwijder als je ook dit niet wilt
            }

            return User::create([
                'name'        => $validated['name'],
                'email'       => $validated['email'],
                'rol'         => $validated['rol'],
                'company_id'  => $companyId, // kan null zijn
                'password'    => bcrypt(Str::random(24)),
            ]);
        });

        if ($validated['context'] === 'klanten') {
            $klanten = User::where('rol','klant')->orderBy('name')->get(['id','name','email']);
            return view('hub.gebruikers.partials.klanten_list', compact('klanten'));
        }

        $medewerkers = User::whereIn('rol', ['medewerker','admin'])->orderBy('name')->get(['id','name','email','rol']);
        return view('hub.gebruikers.partials.medewerkers_list', compact('medewerkers'));
    }

    /** Detail: klant (HTMX → partial, anders index) */
    public function showKlant(Request $request, User $klant)
    {
        abort_unless($klant->rol === 'klant', 404);

        if ($this->isHtmx($request)) {
            return view('hub.gebruikers.partials.klant_detail', compact('klant'));
        }

        $user = auth()->user();
        $q = $this->extractQ($request) ?? '';
        $bootstrap = [
            'activeTab'      => 'klanten',
            'listUrl'        => route('support.gebruikers.klanten', $q !== '' ? ['q' => $q] : []),
            'prefetchDetail' => route('support.gebruikers.klanten.show', $klant),
        ];

        return view('hub.gebruikers.index', compact('user','bootstrap'));
    }

    /** Update klant + OOB ververs lijst-item */
    public function updateKlant(Request $request, User $klant)
    {
        abort_unless($klant->rol === 'klant', 404);
        abort_unless(auth()->user()->rol === 'admin', 403);

        $validated = $request->validate([
            'name'  => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email,'.$klant->id],
            // rol blijft 'klant'
        ]);

        $klant->update($validated);

        if ($this->isHtmx($request)) {
            $detail = view('hub.gebruikers.partials.klant_detail', compact('klant'))->render();
            $itemOob = view('hub.gebruikers.partials._klant_list_item', [
                'k'   => $klant,
                'oob' => true, // => hx-swap-oob="true"
            ])->render();

            return response($detail . "\n" . $itemOob);
        }

        return back()->with('status', 'Klant bijgewerkt');
    }

    /** Verwijder klant + refresh lijst + sluit detail OOB */
    public function destroyKlant(Request $request, User $klant)
    {
        abort_unless(auth()->check() && auth()->user()->rol === 'admin', 403);
        abort_unless($klant->rol === 'klant', 404);

        $klant->delete();

        if ($this->isHtmx($request)) {
            $q = $this->extractQ($request);

            $klanten = User::where('rol','klant')
                ->when($q, fn($qb) => $qb->where('name', 'like', '%'.$q.'%'))
                ->orderBy('name')
                ->get(['id','name','email']);

            $list = view('hub.gebruikers.partials.klanten_list', compact('klanten'))->render();

            return response($list . "\n" . $this->detailCloseOob(), 200);
        }

        return back();
    }

    /** Detail: medewerker (HTMX → partial, anders index) */
    public function showMedewerker(Request $request, User $medewerker)
    {
        abort_unless(in_array($medewerker->rol, ['medewerker','admin']), 404);

        if ($this->isHtmx($request)) {
            return view('hub.gebruikers.partials.medewerker_detail', compact('medewerker'));
        }

        $user = auth()->user();
        $q = $this->extractQ($request) ?? '';
        $bootstrap = [
            'activeTab'      => 'medewerkers',
            'listUrl'        => route('support.gebruikers.medewerkers', $q !== '' ? ['q' => $q] : []),
            'prefetchDetail' => route('support.gebruikers.medewerkers.show', $medewerker),
        ];

        return view('hub.gebruikers.index', compact('user','bootstrap'));
    }

    /** Update medewerker + OOB ververs lijst-item */
    public function updateMedewerker(Request $request, User $medewerker)
    {
        abort_unless(in_array($medewerker->rol, ['medewerker','admin']), 404);
        abort_unless(auth()->user()->rol === 'admin', 403);

        $validated = $request->validate([
            'name'  => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email,'.$medewerker->id],
            'rol'   => ['required','in:medewerker,admin'],
        ]);

        $medewerker->update($validated);

        if ($this->isHtmx($request)) {
            $detail = view('hub.gebruikers.partials.medewerker_detail', compact('medewerker'))->render();
            $itemOob = view('hub.gebruikers.partials._medewerker_list_item', [
                'm'   => $medewerker,
                'oob' => true, // => hx-swap-oob="true"
            ])->render();

            return response($detail . "\n" . $itemOob);
        }

        return back()->with('status', 'Medewerker bijgewerkt');
    }

    /** Verwijder medewerker + refresh lijst + sluit detail OOB */
    public function destroyMedewerker(Request $request, User $medewerker)
    {
        abort_unless(auth()->check() && auth()->user()->rol === 'admin', 403);
        abort_unless(in_array($medewerker->rol, ['medewerker','admin']), 404);

        $medewerker->delete();

        if ($this->isHtmx($request)) {
            $q = $this->extractQ($request);

            $medewerkers = User::whereIn('rol',['medewerker','admin'])
                ->when($q, fn($qb) => $qb->where('name', 'like', '%'.$q.'%'))
                ->orderBy('name')
                ->get(['id','name','email','rol']);

            $list = view('hub.gebruikers.partials.medewerkers_list', compact('medewerkers'))->render();

            return response($list . "\n" . $this->detailCloseOob(), 200);
        }

        return back();
    }

    public function bedrijven(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $companies = Company::query()
            ->when($q !== '', function ($qb) use ($q) {
                $qb->where(function ($q2) use ($q) {
                    $q2->where('name', 'like', "%{$q}%")
                    ->orWhere('domain', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->get();

        if ($request->headers->has('HX-Request')) {
            return view('hub.gebruikers.partials.bedrijven_list', compact('companies', 'q'));
        }

        $user = auth()->user();
        $bootstrap = [
            'activeTab'      => 'bedrijven',
            'listUrl'        => route('support.gebruikers.bedrijven', $q !== '' ? ['q' => $q] : []),
            'prefetchDetail' => null,
        ];
        return view('hub.gebruikers.index', compact('user', 'bootstrap'));
    }

    public function bedrijfShow(Request $request, Company $company)
    {
        if ($request->headers->has('HX-Request')) {
            return view('hub.gebruikers.partials.bedrijf_detail', compact('company'));
        }

        $user = auth()->user();
        $q = trim((string) $request->query('q', ''));
        $bootstrap = [
            'activeTab'      => 'bedrijven',
            'listUrl'        => route('support.gebruikers.bedrijven', $q !== '' ? ['q' => $q] : []),
            'prefetchDetail' => route('support.gebruikers.bedrijven.show', $company),
        ];
        return view('hub.gebruikers.index', compact('user', 'bootstrap'));
    }

    public function storeBedrijf(Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->rol === 'admin', 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // eventueel: ['unique:companies,name'] toevoegen als je unieke namen wilt
        ]);

        // Aanmaken bedrijf
        $company = DB::transaction(function () use ($validated) {
            return Company::create([
                'name' => $validated['name'],
                // later uitbreidbaar: 'domain' => null, 'email' => null, ...
            ]);
        });

        // Lijst verversen, precies zoals bij andere store-acties
        $q = $this->extractQ($request);

        $companies = Company::query()
            ->when($q !== null && $q !== '', function ($qb) use ($q) {
                $qb->where(function ($q2) use ($q) {
                    $q2->where('name', 'like', "%{$q}%")
                    ->orWhere('domain', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->get();

        // HTMX-partial teruggeven (zelfde patroon als bij users)
        if ($this->isHtmx($request)) {
            return view('hub.gebruikers.partials.bedrijven_list', compact('companies', 'q'));
        }

        // Full page fallback: zelfde index met prefetch van het zojuist aangemaakte bedrijf
        $user = auth()->user();
        $bootstrap = [
            'listUrl'        => route('support.gebruikers.bedrijven', $q !== '' ? ['q' => $q] : []),
            'prefetchDetail' => route('support.gebruikers.bedrijven.show', $company),
        ];

        return view('hub.gebruikers.index', compact('user', 'bootstrap'));
    }

    public function bedrijfPersonen(Request $request, Company $company)
    {
        $q = trim((string) $request->query('q', ''));
        $users = User::query()
            ->where('rol', 'klant')
            ->whereNull('company_id')
            ->when($q !== '', function ($qb) use ($q) {
                $qb->where(function($q2) use ($q){
                    $q2->where('name', 'like', "%{$q}%")
                    ->orWhere('email','like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get(['id','name','email']);

        return view('hub.gebruikers.partials._bedrijf_personen_picker', [
            'company' => $company,
            'users'   => $users,
            'q'       => $q,
        ]);
    }

    public function bedrijfPersonenKoppel(Request $request, Company $company)
    {
        abort_unless(auth()->check() && auth()->user()->rol === 'admin', 403);

        $data = $request->validate([
            'user_id' => ['required','integer','exists:users,id'],
        ]);

        // Koppel de user
        $gekoppeld = User::where('rol','klant')
            ->whereNull('company_id')
            ->findOrFail($data['user_id']);

        $gekoppeld->company_id = $company->id;
        $gekoppeld->save();

        // 1) HTML voor het nieuwe person-row (wordt in #company-persons "beforeend" geplakt)
        $rowHtml = view('hub.gebruikers.partials._bedrijf_persoon_row', [
            'u'       => $gekoppeld,
            'company' => $company,   // <-- belangrijk
        ])->render();

        // 2) Vernieuw de picker-lijst OUT-OF-BAND zodat de zojuist gekoppelde user verdwijnt
        $q = $this->extractQ($request) ?? '';

        $users = User::query()
            ->where('rol', 'klant')
            ->whereNull('company_id')
            ->when($q !== '', function ($qb) use ($q) {
                $qb->where(function($q2) use ($q){
                    $q2->where('name', 'like', "%{$q}%")
                    ->orWhere('email','like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get(['id','name','email']);

        // Dit is dezelfde partial als je picker body.
        $pickerHtml = view('hub.gebruikers.partials._bedrijf_personen_picker', [
            'company' => $company,
            'users'   => $users,
            'q'       => $q,
        ])->render();

        // Combineer:
        // - standaard response = $rowHtml (gaat naar #company-persons door hx-target)
        // - OOB swap = ververs de picker body zodat gekoppelde user verdwijnt
        $uid = 'cmp-'.$company->id;

        $oob = <<<HTML
        <div id="person-picker-panel-body-{$uid}" hx-swap-oob="true" hx-swap="innerHTML">
        {$pickerHtml}
        </div>
        HTML;

        return response($rowHtml . "\n" . $oob, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function bedrijfPersonenOntkoppel(Request $request, Company $company, User $user)
    {
        abort_unless($this->canManageCompany($company), 403);
        abort_unless($user->rol === 'klant' && (int)$user->company_id === (int)$company->id, 404);

        // Zelf ontkoppelen blokkeren
        if ((int)$user->id === (int)auth()->id()) {
            return response('Je kunt jezelf niet ontkoppelen.', 403);
        }

        // Laatste admin niet ontkoppelen
        if ($user->is_company_admin) {
            $adminCount = User::where('company_id', $company->id)
                ->where('rol', 'klant')
                ->where('is_company_admin', true)
                ->count();
            if ($adminCount <= 1) {
                return response('Je kunt de laatste admin niet ontkoppelen.', 422);
            }
        }

        $user->company_id = null;
        $user->is_company_admin = false;
        $user->save();

        // OOB refresh van picker (ongewijzigd)
        $q = $this->extractQ($request) ?? '';
        $users = User::query()
            ->where('rol', 'klant')
            ->whereNull('company_id')
            ->when($q !== '', function ($qb) use ($q) {
                $qb->where(function($q2) use ($q){
                    $q2->where('name', 'like', "%{$q}%")
                    ->orWhere('email','like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get(['id','name','email']);

        $pickerHtml = view('hub.gebruikers.partials._bedrijf_personen_picker', [
            'company' => $company,
            'users'   => $users,
            'q'       => $q,
        ])->render();

        $uid = 'cmp-'.$company->id;

        $oob = <<<HTML
    <div id="person-picker-panel-body-{$uid}" hx-swap-oob="true" hx-swap="innerHTML">
    {$pickerHtml}
    </div>
    HTML;

        return response($oob, 200)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function bedrijfToggleAdmin(Company $company, User $user, Request $request)
    {
        abort_unless($user->company_id === $company->id, 404);
        abort_unless($this->canManageCompany($company), 403);

        if ((int)$user->id === (int)auth()->id()) {
            return response('Je kunt je eigen adminstatus niet wijzigen.', 403);
        }

        // Laatste admin beschermen bij UIT-zetten
        if ($user->is_company_admin) {
            $adminCount = User::where('company_id', $company->id)->where('is_company_admin', true)->count();
            if ($adminCount <= 1) return response('Er moet minimaal één admin blijven.', 422);
        }

        $user->is_company_admin = ! $user->is_company_admin;
        $user->save();

        // Recompute counts NA wijziging
        $companyAdminCount  = User::where('company_id', $company->id)->where('is_company_admin', true)->count();
        $companyMemberCount = User::where('company_id', $company->id)->count();

        $ctx    = $request->string('ctx')->toString();
        $asLink = $ctx !== 'team';

        // Crown target
        $crownHtml = view('hub.gebruikers.partials._bedrijf_persoon_crown', [
            'u'                  => $user,
            'company'            => $company,
            'ctx'                => $ctx,
            'disabled'           => ($user->is_company_admin && $companyAdminCount <= 1) || (auth()->id() === $user->id),
            'companyAdminCount'  => $companyAdminCount,
            'companyMemberCount' => $companyMemberCount,
        ])->render();

        // Full row OOB
        $rowHtml = view('hub.gebruikers.partials._bedrijf_persoon_row', [
            'u'                  => $user,
            'company'            => $company,
            'asLink'             => $asLink,
            'ctx'                => $ctx,
            'companyAdminCount'  => $companyAdminCount,
            'companyMemberCount' => $companyMemberCount,
        ])->render();

        $oob = '<div id="person-row-'.$user->id.'" hx-swap-oob="true">'.$rowHtml.'</div>';

        return response($crownHtml."\n".$oob, 200)->header('Content-Type','text/html; charset=UTF-8');
    }

    /* ----------------- Helpers ----------------- */

    protected function isHtmx(Request $request): bool
    {
        return $request->header('HX-Request') === 'true' || $request->ajax();
    }

    /** Leegt & verbergt het detailpaneel via een OOB swap */
    protected function detailCloseOob(): string
    {
        return <<<HTML
        <div id="user-detail-card" hx-swap-oob="true" class="hidden col-span-1 bg-white rounded-xl h-fit"></div>
        HTML;
    }

    protected function extractQ(Request $request): ?string
    {
        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            return $q;
        }

        // probeer 'HX-Current-URL' (HTMX stuurt dit mee met de huidige pagina-URL)
        $current = (string) $request->header('HX-Current-URL', '');
        if ($current) {
            $parts = parse_url($current);
            if (!empty($parts['query'])) {
                parse_str($parts['query'], $query);
                if (!empty($query['q'])) {
                    return trim((string) $query['q']);
                }
            }
        }
        return null;
    }
}
