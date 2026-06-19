<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    /**
     * Muestra la lista del Directorio Inteligente.
     */
    public function index(): Response
    {
        // Trae los contactos de la empresa actual (el candado los filtra automáticamente)
        $contacts = Contact::orderBy('first_name')->get();

        // El puente inmaterial: Enviamos los datos directamente como propiedades (Props) a Vue 3
        return Inertia::render('Contacts/Index', [
            'contacts' => $contacts
        ]);
    }

    /**
     * Almacena un nuevo contacto en el sistema.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validamos estrictamente las entradas del cliente
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'type' => 'required|in:primary,secondary',
            'position' => 'nullable|string|max:255',
        ]);

        // Guardamos. Gracias al Trait Tenantable, el 'company_id' se asigna solo.
        Contact::create($validated);

        // Redirección ultra fluida con Inertia (mantiene al usuario en la SPA sin recargar)
        return redirect()->route('contacts.index');
    }
}