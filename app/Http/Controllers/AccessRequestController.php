<?php

namespace App\Http\Controllers;

use App\Mail\NewAccessRequestNotification;
use App\Models\AccessRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class AccessRequestController extends Controller
{
    /**
     * Muestra la pantalla inicial para buscar la empresa a la que se desea unirse.
     */
    public function lookup(): Response
    {
        return Inertia::render('AccessRequest/Lookup');
    }

    /**
     * Procesa la búsqueda inteligente de la empresa por nombre, dominio o slug.
     */
    public function find(Request $request)
    {
        $request->validate([
            'company_query' => ['required', 'string', 'max:255'],
        ], [
            'company_query.required' => 'Ingresa el nombre o dominio de la empresa.',
        ]);

        $query = trim($request->input('company_query'));

        // Limpiar dominios comunes (ej. "DominioMatic.com" -> "DominioMatic")
        $cleanQuery = preg_replace('/\.(com|org|net|io|co|app|es|dev)$/i', '', $query);
        $slugified = Str::slug($cleanQuery);

        // Búsqueda inteligente: intenta por slug exacto, slug limpio o coincidencia de nombre
        $company = Company::where('slug', Str::slug($query))
            ->orWhere('slug', $slugified)
            ->orWhere('name', 'LIKE', "%{$cleanQuery}%")
            ->orWhere('name', 'LIKE', "%{$query}%")
            ->first();

        if (!$company) {
            return back()->withErrors([
                'company_query' => 'No encontramos ninguna organización registrada con ese nombre o dominio.',
            ]);
        }

        return redirect()->route('join.create', ['slug' => $company->slug]);
    }

    /**
     * Muestra el formulario público para solicitar acceso a una empresa.
     */
    public function create(string $slug)
    {
        $company = Company::where('slug', $slug)->first();

        if (!$company) {
            return redirect()->route('join.lookup')->withErrors([
                'company_query' => 'La empresa solicitada no existe o el enlace ha cambiado.',
            ]);
        }

        return Inertia::render('AccessRequest/Create', [
            'company' => [
                'name' => $company->name,
                'slug' => $company->slug,
                'logo_path' => $company->logo_path,
                'primary_color' => $company->primary_color,
            ],
        ]);
    }

    /**
     * Guarda la solicitud de acceso en la base de datos y notifica a los admins/owners.
     */
    public function store(Request $request, string $slug)
    {
        $company = Company::where('slug', $slug)->first();

        if (!$company) {
            return redirect()->route('join.lookup')->withErrors([
                'company_query' => 'La empresa a la que intentas unirte ya no existe.',
            ]);
        }

        // Validaciones agregando la clave de contraseña requerida
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $exists = AccessRequest::where('company_id', $company->id)
            ->where('email', $validated['email'])
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'email' => 'Ya existe una solicitud pendiente de revisión para este correo.',
            ]);
        }

        $accessRequest = AccessRequest::create([
            'uuid' => (string) Str::uuid(),
            'company_id' => $company->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']), // Guardado encriptado seguro
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $admins = $company->users()
            ->wherePivotIn('role', ['admin', 'owner'])
            ->get();

        if ($admins->isNotEmpty()) {
            Mail::to($admins)->send(new NewAccessRequestNotification($accessRequest));
        }

        return redirect()->route('join.submitted', [
            'slug' => $company->slug,
            'requestId' => $accessRequest->uuid,
        ]);
    }

    /**
     * Pantalla de confirmación tras enviar la solicitud.
     */
    public function submitted(string $slug, string $requestId)
    {
        $company = Company::where('slug', $slug)->first();
        $accessRequest = AccessRequest::where('uuid', $requestId)->first();

        if (!$company || !$accessRequest) {
            return redirect()->route('join.lookup');
        }

        return Inertia::render('AccessRequest/Submitted', [
            'company' => [
                'name' => $company->name,
                'logo_path' => $company->logo_path,
            ],
            'request' => [
                'email' => $accessRequest->email,
                'requested_at' => $accessRequest->requested_at?->diffForHumans() ?? now()->diffForHumans(),
            ],
        ]);
    }
}