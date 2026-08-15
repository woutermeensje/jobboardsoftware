<?php

namespace App\Http\Controllers;

use App\Support\AdminActionNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function submit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        app(AdminActionNotifier::class)->notify('Contactformulier verzonden', [
            'naam' => $validated['name'],
            'email' => $validated['email'],
            'bedrijf' => $validated['company_name'] ?? null,
            'bericht' => $validated['message'],
        ]);

        return back()->with('status', 'Thanks! We\'ll get back to you shortly.');
    }
}
