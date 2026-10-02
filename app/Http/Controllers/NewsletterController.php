<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    // e-mailadres opslaan voor de nieuwsbrief
    public function store(Request $request)
    {
        // eigen error bag, zodat de fouten niet bij het login formulier komen
        $attributes = $request->validateWithBag('newsletter', [
            'email' => 'required|email|max:255|unique:subscribers,email',
        ], [
            'email.required' => 'Vul je e-mailadres in.',
            'email.email' => 'Dit is geen geldig e-mailadres.',
            'email.unique' => 'Je bent al ingeschreven voor de nieuwsbrief.',
        ]);

        Subscriber::create($attributes);

        return redirect(url()->previous() . '#newsletter')->with('newsletter', 'Bedankt! Je bent ingeschreven voor de nieuwsbrief.');
    }
}
