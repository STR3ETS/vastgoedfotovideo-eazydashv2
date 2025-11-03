<?php

namespace App\Http\Controllers;

use App\Mail\TeamInviteMail;
use App\Models\TeamInvite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TeamInviteController extends Controller
{
    /** POST (auth): /app/instellingen/team/invite  | route name: support.instellingen.team.invite */
    public function send(Request $request)
    {
        $auth = $request->user();

        // Alleen platform admin of company-admin met bedrijf
        abort_unless(
            $auth->rol === 'admin' || ($auth->rol === 'klant' && $auth->is_company_admin && $auth->company),
            403
        );

        $data = $request->validate([
            'email' => ['required','email','max:255'],
        ]);

        $company = $auth->rol === 'admin' ? ($auth->company ?? $auth->company) : $auth->company;
        abort_unless($company, 422, 'Geen bedrijfscontext.');

        $email = strtolower(trim($data['email']));

        // Als user al bestaat en al aan dit bedrijf hangt → direct friendly flash
        $existing = User::where('email', $email)->first();
        if ($existing && (int)$existing->company_id === (int)$company->id) {
            return $this->htmxFlash(__('instellingen.invite.already_member'));
        }

        // Invite aanmaken/vervangen
        $invite    = TeamInvite::issue($company->id, $auth->id, $email, 7);
        $acceptUrl = route('support.instellingen.team.invite.accept', $invite->token);

        Mail::to($email)->send(new TeamInviteMail($invite, $acceptUrl));

        return $this->htmxFlash(__('instellingen.invite.sent'));
    }

    /** GET (public): /app/instellingen/team/invite/{token}  | support.instellingen.team.invite.accept */
    public function showAccept(string $token)
    {
        $invite = TeamInvite::where('token', $token)->first();

        if (!$invite || $invite->isExpired() || $invite->isAccepted()) {
            return response()->view('auth.invites.expired_or_used', [], 410);
        }

        return view('auth.invites.accept', compact('invite'));
    }

    /** POST (public): /app/instellingen/team/invite/{token} | support.instellingen.team.invite.handle */
    public function handleAccept(Request $request, string $token)
    {
        $invite = TeamInvite::where('token', $token)->first();

        if (!$invite || $invite->isExpired() || $invite->isAccepted()) {
            return back()->withErrors(['token' => __('instellingen.invite.invalid_or_used')]);
        }

        $data = $request->validate([
            'name' => ['required','string','max:255'],
        ]);

        // Bestaat dit e-mailadres al?
        $existing = User::where('email', $invite->email)->first();
        if ($existing) {
            if (is_null($existing->company_id)) {
                $existing->update(['company_id' => $invite->company_id]);
                $user = $existing;
            } else {
                // Ander bedrijf → blokkeer
                return back()->withErrors(['email' => __('instellingen.invite.email_in_use_other_company')]);
            }
        } else {
            $user = User::create([
                'name'       => $data['name'],
                'email'      => $invite->email,
                'rol'        => 'klant',
                'company_id' => $invite->company_id,
                'password'   => bcrypt(Str::random(32)), // jullie loggen via OTP
            ]);
        }

        $invite->update(['accepted_at' => now()]);

        Auth::login($user);

        return redirect()->route('support.dashboard')
            ->with('success', __('instellingen.invite.accepted'));
    }

    protected function htmxFlash(string $message)
    {
        return response()->view('hub.instellingen.partials.invite_flash', compact('message'), 200)
            ->header('Vary', 'HX-Request');
    }
}
