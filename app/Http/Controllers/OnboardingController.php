<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\TeamInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    protected function getState(Request $request): array
    {
        return $request->session()->get('onboarding', []);
    }

    protected function putState(Request $request, array $data): array
    {
        $state = array_merge($this->getState($request), $data);
        $request->session()->put('onboarding', $state);
        return $state;
    }

    public function account(Request $request)
    {
        return view('onboarding.index'); // wrapper; HTMX laadt step in
    }

    /** ---------- STEP 1 ---------- */
    public function step1(Request $request)
    {
        $state = $this->getState($request);

        if ($request->header('HX-Request')) {
            return view('onboarding.steps.step1', compact('state'));
        }

        return view('onboarding.index', [
            'serverStepView' => 'onboarding.steps.step1',
            'serverStep'     => 1,
            'state'          => $state,
        ]);
    }

    public function storeStep1(Request $request)
    {
        $data = $request->validate([
            'name'  => ['required','string','max:255'],
            'email' => ['required','email','max:255'],
            'phone' => ['nullable','string','max:50'],
        ]);

        $state = $this->putState($request, ['account' => $data]);

        // direct volgende stap met state
        return view('onboarding.steps.step2', compact('state'));
    }

    /** ---------- STEP 2 ---------- */
    public function step2(Request $request)
    {
        $state = $this->getState($request);

        if ($request->header('HX-Request')) {
            return view('onboarding.steps.step2', compact('state'));
        }

        return view('onboarding.index', [
            'serverStepView' => 'onboarding.steps.step2',
            'serverStep'     => 2,
            'state'          => $state,
        ]);
    }

    public function storeStep2(Request $request)
    {
        $data = $request->validate([
            'role'    => ['required','in:owner,manager,agent'],
            'goals'   => ['nullable','array'],
            'goals.*' => ['in:tickets,collab,comms,reports,forms,automation'],
        ]);

        $state = $this->putState($request, ['profile' => $data]);

        return view('onboarding.steps.step3', compact('state'));
    }

    /** ---------- STEP 3 ---------- */
    public function step3(Request $request)
    {
        $state = $this->getState($request);

        if ($request->header('HX-Request')) {
            return view('onboarding.steps.step3', compact('state'));
        }

        return view('onboarding.index', [
            'serverStepView' => 'onboarding.steps.step3',
            'serverStep'     => 3,
            'state'          => $state,
        ]);
    }

    public function storeStep3(Request $request)
    {
        // Normaliseer invites: trim + lege waarden eruit
        $invites = collect($request->input('invites', []))
            ->map(fn ($v) => is_string($v) ? trim($v) : $v)
            ->filter(fn ($v) => !empty($v))
            ->values()
            ->all();

        // Merge terug zodat validator de opgeschoonde versie ziet
        $request->merge(['invites' => $invites]);

        $data = $request->validate([
            'company_name'  => ['required','string','max:255'],
            'country_code'  => ['required','string','size:2'],
            'invites'       => ['nullable','array'],
            'invites.*'     => ['nullable','email'], // laat lege invites toe
        ]);

        // Bewaar in session-state
        $this->putState($request, [
            'company' => [
                'name'         => $data['company_name'],
                'country_code' => strtoupper($data['country_code']),
            ],
            'invites' => $data['invites'] ?? [],
        ]);

        // ⚡ Meteen afronden (stuurt HX-Redirect naar login met e-mail)
        return $this->finish($request);
    }

    public function finish(Request $request)
    {
        $state = $this->getState($request);
        abort_if(empty($state['account']) || empty($state['company']), 422, 'Onboarding niet compleet.');

        // ==== 1) Bedrijf aanmaken/halen ====
        $company = Company::firstOrCreate(
            ['name' => $state['company']['name']],
            ['country_code' => strtoupper($state['company']['country_code'] ?? 'NL')]
        );

        // Phone uit onboarding (stap 1) op companies opslaan
        $companyPhone = $state['account']['phone'] ?? null;
        if ($companyPhone && $company->phone !== $companyPhone) {
            $company->phone = $companyPhone; // companies.phone
            $company->save();
        }

        // Start 30-dagen proefperiode als het bedrijf net is aangemaakt
        // of nog nooit een trial had. Bestaande actieve trial laten we intact.
        if ($company->wasRecentlyCreated || is_null($company->trial_starts_at)) {
            $company->startTrial(30);
        }

        // ==== 2) User aanmaken/bijwerken ====
        $inputRole      = data_get($state, 'profile.role'); // owner|manager|agent|null
        $isCompanyAdmin = $inputRole === 'owner' ? 1 : 0;

        $user = User::firstOrNew(['email' => $state['account']['email']]);
        $user->name             = $state['account']['name'];
        $user->company_id       = $company->id;
        $user->rol              = 'klant';          // altijd 'klant'
        $user->is_company_admin = $isCompanyAdmin;  // 1/0

        // Geen $user->phone hier

        if (!$user->exists || empty($user->password)) {
            $user->password = \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(40)); // tijdelijk; OTP neemt over
        }
        $user->save();

        // ==== 3) Team invites ====
        foreach (array_filter((array) ($state['invites'] ?? [])) as $email) {
            TeamInvitation::firstOrCreate(
                ['email' => $email, 'company_id' => $company->id],
                ['invited_by' => null, 'token' => \Illuminate\Support\Str::uuid(), 'status' => 'pending']
            );
        }

        // ==== 4) Login + session cleanup ====
        \Illuminate\Support\Facades\Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('onboarding');

        // ==== 5) HTMX redirect naar /app ====
        return response('')
            ->header('HX-Redirect', route('support.dashboard'))
            ->header('HX-Trigger', json_encode(['onboarding:clearLocal' => true]));
    }
}