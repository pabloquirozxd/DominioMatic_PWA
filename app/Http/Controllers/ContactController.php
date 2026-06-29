<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    /**
     * Muestra la lista del Directorio Inteligente.
     */
    public function index(Request $request): Response
    {
        $contacts = Contact::query()
            ->where('company_id', $request->user()->company_id)
            ->orderBy('first_name')
            ->get();

        return Inertia::render('Contacts/Index', [
            'contacts' => $contacts,
        ]);
    }

    /**
     * Almacena un nuevo contacto en el sistema.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'type' => ['required', 'in:primary,secondary'],
            'position' => ['nullable', 'string', 'max:255'],
        ]);

        Contact::create($validated);

        return redirect()
            ->route('contacts.index')
            ->with('success', 'Contacto creado correctamente.');
    }

    /**
     * Actualiza un contacto existente.
     */
    public function update(Request $request, Contact $contact): RedirectResponse
    {
        abort_unless($contact->company_id === $request->user()->company_id, 403);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'type' => ['required', 'in:primary,secondary'],
            'position' => ['nullable', 'string', 'max:255'],
        ]);

        $contact->update($validated);

        return redirect()
            ->route('contacts.index')
            ->with('success', 'Contacto actualizado correctamente.');
    }

    /**
     * Elimina un contacto existente.
     */
    public function destroy(Request $request, Contact $contact): RedirectResponse
    {
        abort_unless($contact->company_id === $request->user()->company_id, 403);

        $contact->delete();

        return redirect()
            ->route('contacts.index')
            ->with('success', 'Contacto eliminado correctamente.');
    }
}