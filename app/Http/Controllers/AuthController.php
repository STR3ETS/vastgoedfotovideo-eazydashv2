<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function sendLoginToken(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();

        // 6-cijferige code genereren
        $token = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Code + vervaltijd opslaan op user
        $user->remember_token = $token;               // OTP hier!
        $user->otp_expires_at = now()->addMinutes(15);
        $user->save();

        // Mail versturen
        Mail::send('emails.login-token', ['token' => $token], function ($m) use ($user) {
            $m->to($user->email)->subject('Jouw EazySupport inlogcode');
        });

        return back()->with([
            'token_input' => true,
            'email'       => $user->email,
        ]);
    }

    public function verifyLoginToken(Request $request)
    {
        $data = $request->validate([
            'email'      => ['required', 'email', 'exists:users,email'],
            'temp_token' => ['required', 'string', 'size:6'],
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();

        $isValid = $user->remember_token === $data['temp_token']
            && $user->otp_expires_at
            && $user->otp_expires_at->isFuture();

        if (! $isValid) {
            return back()
                ->withErrors(['temp_token' => 'Token is ongeldig of verlopen.'])
                ->withInput();
        }

        Auth::login($user);

        // Opruimen
        $user->forceFill([
            'remember_token' => null,
            'otp_expires_at' => null,
        ])->save();

        return redirect()->route('support.dashboard');
    }

    public function resendLoginToken(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        // Vorige OTP ongeldig maken (optioneel)
        $user = User::where('email', $data['email'])->firstOrFail();
        $user->forceFill(['remember_token' => null, 'otp_expires_at' => null])->save();

        // Zelfde flow als send
        return $this->sendLoginToken($request);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('support.login');
    }

    public function dismissFirstLogin(Request $request)
    {
        $user = $request->user();
        if ($user && $user->first_login) {
            $user->first_login = false;
            $user->save();
        }
        
        return response('');
    }
}
