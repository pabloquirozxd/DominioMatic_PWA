<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use App\Imports\ContactsImport;
use Maatwebsite\Excel\Facades\Excel;

class ContactController extends Controller
{
    /**
     * Obtiene de forma segura el ID de la empresa activa del usuario.
     */
    private function getActiveCompanyId(Request $request): ?int
    {
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId) {
            return (int) $activeCompanyId;
        }

        // Fallback: Tomar la primera empresa asociada al usuario
        $company = $request->user()?->companies()->first();

        if ($company) {
            session(['active_company_id' => $company->id]);
            return $company->id;
        }

        return null;
    }

    public function index(Request $request): Response
    {
        $companyId = $this->getActiveCompanyId($request);

        $contacts = Contact::query()
            ->where('company_id', $companyId)
            ->with(['clients' => function ($query) {
                $query->withPivot('position', 'is_primary');
            }])
            ->orderBy('first_name')
            ->get();

        return Inertia::render('Contacts/Index', [
            'contacts' => $contacts,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id'  => ['nullable', 'integer'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['nullable', 'string', 'max:255'],
            'email'      => ['nullable', 'email', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:50'],
            'type'       => ['nullable', 'in:primary,secondary'],
            'position'   => ['nullable', 'string', 'max:255'],
        ]);

        $companyId = $this->getActiveCompanyId($request);

        $client = null;
        if (!empty($validated['client_id'])) {
            $client = Client::where('company_id', $companyId)
                ->findOrFail($validated['client_id']);
        }

        DB::transaction(function () use ($validated, $companyId, $client) {
            $contactData = [
                'first_name' => $validated['first_name'],
                'last_name'  => $validated['last_name'] ?? null,
                'phone'      => $validated['phone'] ?? null,
                'position'   => $validated['position'] ?? null,
            ];

            if (!empty($validated['email'])) {
                $contact = Contact::updateOrCreate(
                    ['company_id' => $companyId, 'email' => $validated['email']],
                    $contactData
                );
            } else {
                $contact = Contact::create(array_merge(['company_id' => $companyId], $contactData));
            }

            if ($client) {
                $isPrimary = ($validated['type'] ?? 'secondary') === 'primary';

                if ($isPrimary) {
                    DB::table('client_contact')
                        ->where('client_id', $client->id)
                        ->update(['is_primary' => false]);
                }

                DB::table('client_contact')->updateOrInsert(
                    [
                        'client_id'  => $client->id,
                        'contact_id' => $contact->id,
                    ],
                    [
                        'position'   => $validated['position'] ?? null,
                        'is_primary' => $isPrimary,
                        'updated_at' => now(),
                    ]
                );
            }
        });

        return redirect()->back()->with('success', 'Contacto guardado correctamente.');
    }

    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $companyId = $this->getActiveCompanyId($request);
        abort_unless($contact->company_id === $companyId, 403);

        $validated = $request->validate([
            'client_id'  => ['nullable', 'integer'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['nullable', 'string', 'max:255'],
            'email'      => ['nullable', 'email', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:50'],
            'type'       => ['nullable', 'in:primary,secondary'],
            'position'   => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $contact) {
            // 1. Datos base del contacto (perfil global)
            $contactData = [
                'first_name' => $validated['first_name'],
                'last_name'  => $validated['last_name'] ?? null,
                'email'      => $validated['email'] ?? null,
                'phone'      => $validated['phone'] ?? null,
            ];

            // Solo actualizar el cargo general si NO proviene de un cliente específico
            if (empty($validated['client_id'])) {
                $contactData['position'] = $validated['position'] ?? null;
            }

            $contact->update($contactData);

            // 2. Si proviene de un cliente, actualizar la relación en la tabla pivote
            if (!empty($validated['client_id'])) {
                $clientId = $validated['client_id'];
                $isPrimary = ($validated['type'] ?? 'secondary') === 'primary';

                if ($isPrimary) {
                    DB::table('client_contact')
                        ->where('client_id', $clientId)
                        ->where('contact_id', '!=', $contact->id)
                        ->update(['is_primary' => false]);
                }

                DB::table('client_contact')->updateOrInsert(
                    [
                        'client_id'  => $clientId,
                        'contact_id' => $contact->id,
                    ],
                    [
                        'position'   => $validated['position'] ?? null,
                        'is_primary' => $isPrimary,
                        'updated_at' => now(),
                    ]
                );
            }
        });

        return redirect()->back()->with('success', 'Contacto actualizado correctamente.');
    }

    public function destroy(Request $request, Contact $contact): RedirectResponse
    {
        $companyId = $this->getActiveCompanyId($request);
        abort_unless($contact->company_id === $companyId, 403);

        if ($clientId = $request->input('client_id')) {
            $client = Client::where('company_id', $companyId)->findOrFail($clientId);
            $client->contacts()->detach($contact->id);
            $message = 'Contacto desvinculado del cliente.';
        } else {
            $contact->clients()->detach();
            $contact->delete();
            $message = 'Contacto eliminado correctamente.';
        }

        return redirect()->back()->with('success', $message);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:2048',
        ]);

        Excel::import(new ContactsImport, $request->file('file'));

        return redirect()->route('contacts.index')
            ->with('message', 'Contactos importados exitosamente.');
    }
}