<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    // login formulier
    public function create()
    {
        return view('sessions.create');
    }

    // inloggen
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Vul je e-mailadres in.',
            'email.email' => 'Dit is geen geldig e-mailadres.',
            'password.required' => 'Vul je wachtwoord in.',
        ]);

        if (! Auth::attempt($credentials)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'E-mailadres of wachtwoord is fout.']);
        }

        // nieuwe sessie id tegen session fixation
        $request->session()->regenerate();

        return redirect('/admin/posts')->with('success', 'Welkom terug!');
    }

    // uitloggen
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
