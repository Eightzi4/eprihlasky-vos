<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\LoginLinkMail;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function handleEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $email = $request->input('email');

        $user = User::where('email', $email)->first();

        if ($user && $user->password) {
            return view('auth.password-login', ['email' => $email]);
        }

        if (! $user) {
            $user = User::create([
                'email' => $email,
                'name'  => strstr($email, '@', true),
            ]);
        }

        $this->sendLoginTicket($user);
        return view('auth.check-email', ['email' => $email]);
    }

    public function sendLoginTicket(User $user)
    {
        $token = Str::random(32);

        $ticket = LoginTicket::create([
            'user_id'    => $user->id,
            'token'      => $token,
            'expires_at' => now()->addMinutes(30),
        ]);

        $link = route('auth.verify', ['loginTicket' => $token]);

        try {
            Mail::to($user->email)->send(new LoginLinkMail($link, $ticket->expires_at));
        } catch (\Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage());
        }

        Log::info("Login ticket created for user {$user->email}, link: {$link}");
    }

    public function sendLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->firstOrFail();

        $this->sendLoginTicket($user);
        return view('auth.check-email', ['email' => $user->email]);
    }

    public function loginWithPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return view('auth.password-login', ['email' => $request->email])
            ->withErrors(['password' => 'Zadané heslo není správné.']);
    }

    public function verifyTicket(Request $request)
    {
        $token = $request->query('loginTicket');

        if (! $token) {
            return redirect()->route('login')->with('error', 'Chybějící přihlašovací token.');
        }

        $ticket = LoginTicket::where('token', $token)
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->first();

        if (! $ticket) {
            return redirect()->route('login')->with('error', 'Neplatný nebo expirovaný odkaz.');
        }

        Auth::login($ticket->user);
        $ticket->update(['used_at' => now()]);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
