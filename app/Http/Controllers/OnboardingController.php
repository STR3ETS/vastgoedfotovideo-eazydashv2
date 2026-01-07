<?php

namespace App\Http\Controllers;

use App\Models\OnboardingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class OnboardingController extends Controller
{
    private string $sessionKey = 'onboarding';

    // --- helpers ---
    private function wizard(Request $request): array
    {
        return $request->session()->get($this->sessionKey, []);
    }

    private function putWizard(Request $request, array $data): void
    {
        $current = $this->wizard($request);
        $request->session()->put($this->sessionKey, array_merge($current, $data));
    }

    private function clearWizard(Request $request): void
    {
        $request->session()->forget($this->sessionKey);
    }

    // --- intro pages (die je al had) ---
    public function index(Request $request)
    {
        $q    = trim((string) $request->query('q', ''));
        $sort = (string) $request->query('sort', 'newest');

        $requests = OnboardingRequest::query()
            ->with('user');

        // simpele search op user + contactpersoon
        if ($q !== '') {
            $requests->where(function ($query) use ($q) {
                $query->whereHas('user', function ($u) use ($q) {
                    $u->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
                })
                ->orWhere('contact_first_name', 'like', "%{$q}%")
                ->orWhere('contact_last_name', 'like', "%{$q}%")
                ->orWhere('contact_email', 'like', "%{$q}%");
            });
        }

        // sorteren
        if ($sort === 'oldest') {
            $requests->orderBy('created_at', 'asc');
        } elseif ($sort === 'title_asc') {
            // “Titel A–Z” -> we gebruiken hiervoor de naam van de user
            $requests->orderByRaw("LOWER(COALESCE(NULLIF(contact_last_name,''), '')) asc")
                    ->orderByRaw("LOWER(COALESCE(NULLIF(contact_first_name,''), '')) asc");
        } elseif ($sort === 'title_desc') {
            $requests->orderByRaw("LOWER(COALESCE(NULLIF(contact_last_name,''), '')) desc")
                    ->orderByRaw("LOWER(COALESCE(NULLIF(contact_first_name,''), '')) desc");
        } elseif ($sort === 'status') {
            $requests->orderBy('status', 'asc')->orderBy('created_at', 'desc');
        } else {
            $requests->orderBy('created_at', 'desc'); // newest
        }

        // DB records = verstuurd => “Voltooid”
        $rows = $requests->paginate(20)->withQueryString();

        // wizard in session = concept
        $wizard = $this->wizard($request);

        // concept alleen tonen als er al iets is ingevuld
        $hasDraft = is_array($wizard) && count($wizard) > 0;

        return view('hub.onboarding.index', [
            'user'     => $request->user(),
            'rows'     => $rows,
            'wizard'   => $wizard,
            'hasDraft' => $hasDraft,
            'q'        => $q,
            'sort'     => $sort,
        ]);
    }

    public function show(Request $request, OnboardingRequest $onboardingRequest)
    {
        $onboardingRequest->load('user'); // vereist user() relatie op model

        return view('hub.onboarding.show', [
            'user' => $request->user(),
            'row'  => $onboardingRequest,
        ]);
    }

    public function create(Request $request)
    {
        // start wizard fresh (optioneel)
        $this->clearWizard($request);

        return view('hub.onboarding.create', [
            'user' => $request->user(),
        ]);
    }

    // --- STEP 1 ---
    public function step1(Request $request)
    {
        return view('hub.onboarding.step1', [
            'user'   => $request->user(),
            'wizard' => $this->wizard($request),
        ]);
    }

    public function storeStep1(Request $request)
    {
        $data = $request->validate([
            'address'              => ['required','string','max:255'],
            'postcode'             => ['required','string','max:20'],
            'city'                 => ['required','string','max:120'],
            'surface_home'         => ['nullable','integer','min:0'],
            'surface_outbuildings' => ['nullable','integer','min:0'],
            'surface_plot'         => ['nullable','integer','min:0'],
        ]);

        $this->putWizard($request, $data);

        return redirect()->route('support.onboarding.step2');
    }

    // --- STEP 2 ---
    public function step2(Request $request)
    {
        return view('hub.onboarding.step2', [
            'user'   => $request->user(),
            'wizard' => $this->wizard($request),
        ]);
    }

    public function storeStep2(Request $request)
    {
        $data = $request->validate([
            'contact_first_name' => ['required','string','max:100'],
            'contact_last_name'  => ['required','string','max:120'],
            'contact_email'      => ['required','email','max:255'],
            'contact_phone'      => ['required','string','max:40'],
            'contact_updates'    => ['nullable','boolean'],

            'agency_first_name'  => ['required','string','max:100'],
            'agency_last_name'   => ['required','string','max:120'],
            'agency_email'       => ['required','email','max:255'],
            'agency_phone'       => ['required','string','max:40'],
        ]);

        // checkbox netjes casten
        $data['contact_updates'] = (bool) ($request->input('contact_updates') ?? false);

        $this->putWizard($request, $data);

        return redirect()->route('support.onboarding.step3');
    }

    // --- STEP 3 ---
    public function step3(Request $request)
    {
        return view('hub.onboarding.step3', [
            'user'   => $request->user(),
            'wizard' => $this->wizard($request),
        ]);
    }

    public function storeStep3(Request $request)
    {
        $allowed = ['pro','plus','essentials','media','funda_klaar','buiten'];

        $data = $request->validate([
            'package' => ['required','in:'.implode(',', $allowed)],
        ]);

        $this->putWizard($request, $data);

        return redirect()->route('support.onboarding.step4');
    }

    // --- STEP 4 ---
    public function step4(Request $request)
    {
        return view('hub.onboarding.step4', [
            'user'   => $request->user(),
            'wizard' => $this->wizard($request),
        ]);
    }

    public function storeStep4(Request $request)
    {
        $allowedExtras = [
            'privacy_check','detailfotos','hoogtefotografie_8m','plattegrond_in_video','tekst_video',
            'floorplanner_3d','meubels_toevoegen','tuin_toevoegen','artist_impression','woningtekst',
            'video_1min','foto_slideshow','levering_24u','huisstijl_plattegrond','m2_per_ruimte','style_shoot'
        ];

        $data = $request->validate([
            'extras'   => ['nullable','array'],
            'extras.*' => ['in:'.implode(',', $allowedExtras)],
        ]);

        $data['extras'] = array_values($data['extras'] ?? []);

        $this->putWizard($request, $data);

        return redirect()->route('support.onboarding.step5');
    }

    // --- STEP 5 ---
    public function step5(Request $request)
    {
        return view('hub.onboarding.step5', [
            'user'   => $request->user(),
            'wizard' => $this->wizard($request),
        ]);
    }

    public function storeStep5(Request $request)
    {
        $allowedSlots = ['09:00 - 11:00','11:00 - 13:00','13:00 - 15:00','15:00 - 17:00','17:00 - 19:00'];

        $data = $request->validate([
            'shoot_date' => ['required','date'],
            'shoot_slot' => ['required','in:'.implode(',', $allowedSlots)],
        ]);

        $this->putWizard($request, $data);

        return redirect()->route('support.onboarding.step6');
    }

    // --- STEP 6 ---
    public function step6(Request $request)
    {
        return view('hub.onboarding.step6', [
            'user'   => $request->user(),
            'wizard' => $this->wizard($request),
        ]);
    }

    // --- FINAL SUBMIT ---
    public function submit(Request $request)
    {
        // confirmations van stap 6
        $confirm = $request->validate([
            'confirm_truth' => ['accepted'],
            'confirm_terms' => ['accepted'],
        ]);

        $wizard = $this->wizard($request);

        // Safety: alles wat we minimaal nodig hebben checken (quick guard)
        $required = [
            'address','postcode','city',
            'contact_first_name','contact_last_name','contact_email','contact_phone',
            'agency_first_name','agency_last_name','agency_email','agency_phone',
            'package',
            'shoot_date','shoot_slot',
        ];

        foreach ($required as $key) {
            if (!Arr::has($wizard, $key) || blank($wizard[$key] ?? null)) {
                return redirect()->route('support.onboarding.step1')
                    ->with('error', 'Er missen gegevens in je onboarding. Vul stap voor stap alles in.');
            }
        }

        $record = OnboardingRequest::create([
            'user_id'              => $request->user()->id,

            'address'              => $wizard['address'],
            'postcode'             => $wizard['postcode'],
            'city'                 => $wizard['city'],
            'surface_home'         => (int) ($wizard['surface_home'] ?? 0),
            'surface_outbuildings' => (int) ($wizard['surface_outbuildings'] ?? 0),
            'surface_plot'         => (int) ($wizard['surface_plot'] ?? 0),

            'contact_first_name'   => $wizard['contact_first_name'],
            'contact_last_name'    => $wizard['contact_last_name'],
            'contact_email'        => $wizard['contact_email'],
            'contact_phone'        => $wizard['contact_phone'],
            'contact_updates'      => (bool) ($wizard['contact_updates'] ?? false),

            'agency_first_name'    => $wizard['agency_first_name'],
            'agency_last_name'     => $wizard['agency_last_name'],
            'agency_email'         => $wizard['agency_email'],
            'agency_phone'         => $wizard['agency_phone'],

            'package'              => $wizard['package'],
            'extras'               => $wizard['extras'] ?? [],

            'shoot_date'           => $wizard['shoot_date'],
            'shoot_slot'           => $wizard['shoot_slot'],

            'confirm_truth'        => true,
            'confirm_terms'        => true,

            'status'               => 'new',
        ]);

        $project = app(\App\Actions\CreateProjectFromOnboardingRequest::class)
            ->execute($record, $request->user()->id);

        $this->clearWizard($request);

        return redirect()->route('support.onboarding.index')
            ->with('success', 'Aanvraag verzonden! (#'.$record->id.')');
    }

    public function reset(Request $request)
    {
        $this->clearWizard($request);
        return redirect()->route('support.onboarding.create')->with('success', 'Onboarding opnieuw gestart.');
    }
}