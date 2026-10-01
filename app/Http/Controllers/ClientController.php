<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRequest;
use App\Models\Client;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    /**
     * Helper para obtener el ID de la empresa activa del usuario.
     */
    private function getCompanyId(): int
    {
        if (session()->has('active_company_id')) {
            return (int) session('active_company_id');
        }

        $user = Auth::user();
        $companyId = $user->companies()->first()?->id;

        if (!$companyId) {
            abort(403, 'El usuario no está vinculado a ninguna empresa.');
        }

        return $companyId;
    }

    public function index(): Response
    {
        // 🎯 El trait Tenantable ya aplica el filtro por company_id automáticamente
        $clients = Client::with(['contacts' => function ($query) {
                $query->orderByPivot('is_primary', 'desc')
                      ->orderByPivot('created_at', 'desc');
            }])
            ->latest()
            ->get();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
        ]);
    }

    /**
     * Buscar contactos existentes para el autocompletado en el formulario.
     */
    public function searchContacts(Request $request): JsonResponse
    {
        $query = trim($request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $companyId = $this->getCompanyId();

        $contacts = Contact::where('company_id', $companyId)
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere(DB::raw("CONCAT(first_name, ' ', COALESCE(last_name, ''))"), 'like', "%{$query}%");
            })
            ->limit(5)
            ->get(['id', 'first_name', 'last_name', 'email', 'phone']);

        return response()->json($contacts);
    }

    public function store(ClientRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $userCompanyId = $this->getCompanyId();

        DB::transaction(function () use ($validated, $userCompanyId) {
            // 1. Crear el Cliente
            $client = Client::create(
                array_merge($this->extractClientData($validated), [
                    'company_id' => $userCompanyId,
                ])
            );

            // 2. Resolver o crear el contacto
            $contact = $this->resolveContact($validated, $userCompanyId);

            // 3. Vincularlo como Contacto Principal
            $client->contacts()->syncWithoutDetaching([
                $contact->id => [
                    'position'   => !empty($validated['position']) ? $validated['position'] : null,
                    'is_primary' => true,
                ],
            ]);
        });

        return redirect()
            ->route('clients.index')
            ->with('success', 'Cliente creado correctamente.');
    }

    public function update(ClientRequest $request, Client $client): RedirectResponse
    {
        $validated = $request->validated();
        $userCompanyId = $this->getCompanyId();

        DB::transaction(function () use ($validated, $client, $userCompanyId) {
            // 1. Actualizar Datos del Cliente
            $client->update($this->extractClientData($validated));

            // 2. Resolver el contacto
            $contact = $this->resolveContact($validated, $userCompanyId);

            // 3. Degradar contactos existentes a no principales
            $contactIds = $client->contacts()->pluck('contacts.id')->toArray();
            if (!empty($contactIds)) {
                $client->contacts()->updateExistingPivot(
                    $contactIds,
                    ['is_primary' => false]
                );
            }

            // 4. Promover o vincular como Contacto Principal
            $client->contacts()->syncWithoutDetaching([
                $contact->id => [
                    'position'   => !empty($validated['position']) ? $validated['position'] : null,
                    'is_primary' => true,
                ],
            ]);
        });

        return redirect()
            ->route('clients.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    /**
     * Establece un contacto específico como principal para el cliente.
     */
    public function setPrimaryContact(Client $client, Contact $contact): RedirectResponse
    {
        $companyId = $this->getCompanyId();
        if ($client->company_id !== $companyId || $contact->company_id !== $companyId) {
            abort(403);
        }

        DB::transaction(function () use ($client, $contact) {
            $contactIds = $client->contacts()->pluck('contacts.id')->toArray();

            if (!empty($contactIds)) {
                $client->contacts()->updateExistingPivot(
                    $contactIds,
                    ['is_primary' => false]
                );
            }

            $client->contacts()->syncWithoutDetaching([
                $contact->id => [
                    'is_primary' => true,
                ],
            ]);
        });

        return redirect()
            ->back()
            ->with('success', 'Contacto principal actualizado.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', 'Cliente eliminado.');
    }

    /**
     * Resuelve la identidad del contacto.
     */
    private function resolveContact(array $validated, int $companyId): Contact
    {
        if (!empty($validated['contact_id'])) {
            $contact = Contact::where('company_id', $companyId)
                ->findOrFail($validated['contact_id']);

            $contact->update([
                'first_name' => $validated['first_name'],
                'last_name'  => $validated['last_name'] ?? null,
                'email'      => $validated['email'] ?? null,
                'phone'      => $validated['phone'] ?? null,
            ]);

            return $contact;
        }

        $existingContact = Contact::where('company_id', $companyId)
            ->where('first_name', $validated['first_name'])
            ->where(function ($q) use ($validated) {
                if (!empty($validated['last_name'])) {
                    $q->where('last_name', $validated['last_name']);
                } else {
                    $q->whereNull('last_name')->orWhere('last_name', '');
                }
            })
            ->first();

        if ($existingContact) {
            $existingContact->update([
                'email' => array_key_exists('email', $validated) ? $validated['email'] : $existingContact->email,
                'phone' => array_key_exists('phone', $validated) ? $validated['phone'] : $existingContact->phone,
            ]);

            return $existingContact;
        }

        return Contact::create([
            'company_id' => $companyId,
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'] ?? null,
            'email'      => $validated['email'] ?? null,
            'phone'      => $validated['phone'] ?? null,
        ]);
    }

    /**
     * Mapea y limpia los atributos para la persistencia del cliente.
     */
    private function extractClientData(array $validated): array
    {
        return [
            'type'           => $validated['type'],
            'company_name'   => $validated['type'] === 'company' 
                ? $validated['company_name'] 
                : trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? '')),
            'phone'          => $validated['company_phone'] ?? $validated['phone'] ?? null,
            'tax_id'         => $validated['tax_id'] ?? null,
            'payment_terms'  => $validated['payment_terms'] ?? 'Due on Receipt',
            'notes'          => $validated['notes'] ?? null,
            'website'        => $validated['website'] ?? null,
            'language'       => $validated['language'] ?? 'es',
            'portal_enabled' => $validated['portal_enabled'] ?? false,
        ];
    }
}