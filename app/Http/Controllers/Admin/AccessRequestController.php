<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AccessRequestApproved;
use App\Models\AccessRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class AccessRequestController extends Controller
{
    /**
     * Muestra el listado de solicitudes filtrado por rol (Admin vs Owner).
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // 🎯 Multi-Tenant: Obtenemos los IDs de las empresas donde el usuario es Admin u Owner
        $userCompanyIds = $user->companies()->pluck('companies.id')->toArray();
        
        // Verificamos si tiene el rol 'owner' en al menos una empresa o globalmente
        $isOwner = $user->companies()->wherePivot('role', 'owner')->exists();

        $status = $request->input('status', 'pending');
        $search = $request->input('search');
        $companyIdFilter = $request->input('company_id');

        // 1. Definir el alcance base según el ROL del usuario
        $query = AccessRequest::with('company')
            ->when(!$isOwner, function ($q) use ($userCompanyIds) {
                // El ADMIN solo ve solicitudes de sus empresas asociadas
                $q->whereIn('company_id', $userCompanyIds);
            })
            ->when($isOwner && $companyIdFilter, function ($q) use ($companyIdFilter) {
                // El OWNER puede filtrar voluntariamente por una empresa en particular
                $q->where('company_id', $companyIdFilter);
            });

        // 2. Aplicar filtros de estado y búsqueda
        $requests = (clone $query)
            ->when($status !== 'all', function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($search, function ($q, $search) {
                $q->where(function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest('requested_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn ($item) => [
                'uuid' => $item->uuid,
                'name' => $item->name,
                'email' => $item->email,
                'company_name' => $item->company?->name ?? 'N/A',
                'message' => $item->message,
                'status' => $item->status,
                'requested_at' => $item->requested_at?->diffForHumans() ?? $item->created_at->diffForHumans(),
                'reviewed_at' => $item->reviewed_at?->format('d/m/Y H:i'),
            ]);

        // 3. Calcular contadores según el alcance del usuario
        $countsQuery = AccessRequest::query()
            ->when(!$isOwner, fn($q) => $q->whereIn('company_id', $userCompanyIds))
            ->when($isOwner && $companyIdFilter, fn($q) => $q->where('company_id', $companyIdFilter));

        return Inertia::render('Admin/AccessRequests/Index', [
            'requests' => $requests,
            'isOwner' => $isOwner,
            'companies' => $isOwner ? Company::select('id', 'name')->get() : [],
            'filters' => [
                'status' => $status,
                'search' => $search,
                'company_id' => $companyIdFilter,
            ],
            'counts' => [
                'pending' => (clone $countsQuery)->where('status', 'pending')->count(),
                'approved' => (clone $countsQuery)->where('status', 'approved')->count(),
                'rejected' => (clone $countsQuery)->where('status', 'rejected')->count(),
            ],
        ]);
    }

    /**
     * Aprueba la solicitud de acceso y crea/asigna la cuenta de usuario.
     */
    public function approve(Request $request, string $uuid)
    {
        $admin = $request->user();
        $isOwner = $admin->companies()->wherePivot('role', 'owner')->exists();
        $userCompanyIds = $admin->companies()->pluck('companies.id')->toArray();

        // Seguridad Multi-Tenant: El Admin solo aprueba de sus empresas, el Owner de cualquiera
        $accessRequest = AccessRequest::with('company')
            ->where('uuid', $uuid)
            ->when(!$isOwner, function ($q) use ($userCompanyIds) {
                $q->whereIn('company_id', $userCompanyIds);
            })
            ->firstOrFail();

        DB::transaction(function () use ($accessRequest, $admin) {
            // 1. Crear el usuario si no existe (usando la contraseña almacenada en la solicitud)
            $user = User::firstOrCreate(
                ['email' => $accessRequest->email],
                [
                    'name' => $accessRequest->name,
                    'password' => $accessRequest->password,
                ]
            );

            // 2. Vincular el usuario a la empresa con el rol de miembro en la tabla pivote
            $user->companies()->syncWithoutDetaching([
                $accessRequest->company_id => ['role' => 'member'],
            ]);

            // 3. Actualizar la solicitud
            $accessRequest->update([
                'status' => 'approved',
                'reviewed_at' => now(),
                'approved_at' => now(),
                'reviewed_by' => $admin->id,
                'user_id' => $user->id,
            ]);

            // 4. Notificar al usuario por correo
            Mail::to($accessRequest->email)->send(new AccessRequestApproved($accessRequest));
        });

        return back()->with('success', "La solicitud de {$accessRequest->name} ha sido aprobada y su cuenta configurada.");
    }

    /**
     * Rechaza la solicitud de acceso.
     */
    public function reject(Request $request, string $uuid)
    {
        $admin = $request->user();
        $isOwner = $admin->companies()->wherePivot('role', 'owner')->exists();
        $userCompanyIds = $admin->companies()->pluck('companies.id')->toArray();

        $accessRequest = AccessRequest::where('uuid', $uuid)
            ->when(!$isOwner, function ($q) use ($userCompanyIds) {
                $q->whereIn('company_id', $userCompanyIds);
            })
            ->firstOrFail();

        $accessRequest->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'rejected_at' => now(),
            'reviewed_by' => $admin->id,
        ]);

        return back()->with('info', "La solicitud de {$accessRequest->name} fue rechazada.");
    }
}