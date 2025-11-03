<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Company;

class InstellingenController extends Controller
{
    public function index(Request $request)
    {
        // Standaard land je op "personal"
        return view('hub.instellingen.index', [
            'user'       => $request->user(),
            'initialTab' => 'personal',
            'initialUrl' => route('support.instellingen.personal'),
        ]);
    }

    public function personal(Request $request)
    {
        $user = $request->user();
        $q    = $request->string('q')->toString();

        if ($request->header('HX-Request')) {
            return view('hub.instellingen.partials.personal', compact('user', 'q'));
        }

        // Harde reload: render volledige pagina + laad deze tab in de rechterkolom
        return view('hub.instellingen.index', [
            'user'       => $user,
            'initialTab' => 'personal',
            'initialUrl' => route('support.instellingen.personal'),
        ]);
    }

    public function company(Request $request)
    {
        $user = $request->user();
        $q    = $request->string('q')->toString();

        if ($request->header('HX-Request')) {
            return view('hub.instellingen.partials.company', compact('user', 'q'));
        }

        return view('hub.instellingen.index', [
            'user'       => $user,
            'initialTab' => 'company',
            'initialUrl' => route('support.instellingen.company'),
        ]);
    }

    public function team(Request $request)
    {
        $user    = $request->user();
        $q       = $request->string('q')->toString();
        $company = $user?->company; // nodig voor picker

        // Zelfde voorwaarde als je tab zichtbaar­heid
        abort_unless($user->rol === 'klant' && $user->is_company_admin, 403);

        if ($request->header('HX-Request')) {
            return view('hub.instellingen.partials.team', compact('user', 'q', 'company'));
        }

        return view('hub.instellingen.index', [
            'user'       => $user,
            'initialTab' => 'team',
            'initialUrl' => route('support.instellingen.team'),
        ]);
    }

    public function billing(Request $request)
    {
        $user = $request->user();
        $q    = $request->string('q')->toString();

        if ($request->header('HX-Request')) {
            return view('hub.instellingen.partials.billing', compact('user', 'q'));
        }

        return view('hub.instellingen.index', [
            'user'       => $user,
            'initialTab' => 'billing',
            'initialUrl' => route('support.instellingen.billing'),
        ]);
    }

    public function documents(Request $request)
    {
        $user = $request->user();
        $q    = $request->string('q')->toString();

        if ($request->header('HX-Request')) {
            return view('hub.instellingen.partials.documents', compact('user', 'q'));
        }

        return view('hub.instellingen.index', [
            'user'       => $user,
            'initialTab' => 'documents',
            'initialUrl' => route('support.instellingen.documents'),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'   => ['required','string','max:255'],
            'email'  => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'locale' => ['required', Rule::in(['nl','de','fr','es','en'])],
        ]);

        $user->fill($validated)->save();

        app()->setLocale($validated['locale']);
        session(['locale' => $validated['locale']]);

        if ($request->header('HX-Request')) {
            // bevat je i18n-dict + applyI18n() en evt. toast
            return response()->view('hub.instellingen.partials.flash_and_i18n');
        }

        return back()->with('success', __('instellingen.flash.saved'));
    }

    public function updateCompany(Request $request)
    {
        $auth = $request->user();

        $validated = $request->validate([
            'name'         => ['required','string','max:255'],
            'country_code' => ['nullable', Rule::in(['NL','BE','DE','FR','ES'])],
            'website'      => ['nullable','url','max:255'],
            'email'        => ['nullable','email','max:255'],
            'phone'        => ['nullable','string','max:50'],

            'street'       => ['nullable','string','max:255'],
            'house_number' => ['nullable','string','max:50'],
            'postal_code'  => ['nullable','string','max:50'],
            'city'         => ['nullable','string','max:255'],

            'kvk_number'   => ['nullable','string','max:100'],
            'vat_number'   => ['nullable','string','max:100'],
            'trade_name'   => ['nullable','string','max:255'],
            'legal_form'   => ['nullable','string','max:100'],

            // Alleen admin mag gericht een bedrijf updaten
            'company_id'   => ['sometimes','integer','exists:companies,id'],
        ]);

        // Bepaal target company
        if ($auth->rol === 'admin' && $request->filled('company_id')) {
            $company = Company::findOrFail((int) $request->input('company_id'));
        } else {
            // Eigen company (aanmaken indien nodig)
            $company = $auth->company ?: new Company();
        }

        // Vul alleen bedrijfsvelden (niet company_id)
        $data = $validated;
        unset($data['company_id']);

        $company->fill($data);
        $company->save();

        // Zorg dat de ingelogde user gekoppeld blijft aan z’n eigen company
        if (!$auth->company_id) {
            $auth->company()->associate($company);
            $auth->save();
        }

        // HTMX: geef een kleine stub terug voor je hx-target (#company-flash / -{uid})
        if ($request->header('HX-Request')) {
            return response()->view('hub.instellingen.partials.company_flash', [
                'company' => $company,
            ]);
        }

        return back()->with('success', __('instellingen.flash.saved'));
    }
}