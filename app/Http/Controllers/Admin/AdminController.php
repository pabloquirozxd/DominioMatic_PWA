<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $activeCompanyId = session('active_company_id');

        // 1. Obtener la empresa activa
        $activeCompany = $user->companies()
            ->where('companies.id', $activeCompanyId)
            ->first() ?? $user->companies()->first();

        $activeCompanyId = $activeCompany?->id;
        $pivotRole = $activeCompany?->pivot->role ?? 'member';

        // 2. Evaluar si es Owner (por pivote o por súper correo)
        $isOwner = $pivotRole === 'owner' || $user->email === 'pablo@quiroz.me';
        $authRole = $isOwner ? 'owner' : $pivotRole;

        // Denegar acceso si es un miembro regular
        if (!in_array($authRole, ['owner', 'admin'])) {
            abort(403, 'No tienes permisos para acceder al área de administración.');
        }

        // 3. Obtener Usuarios / Miembros según el Alcance
        $usersQuery = User::with('companies')
            ->when(!$isOwner, function ($q) use ($activeCompanyId) {
                $q->whereHas('companies', fn ($c) => $c->where('companies.id', $activeCompanyId));
            })
            ->latest();

        $users = $usersQuery->get()->map(function ($u) use ($activeCompanyId) {
            $company = $u->companies->firstWhere('id', $activeCompanyId) ?? $u->companies->first();

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $company?->pivot->role ?? 'member',
                'avatar' => $u->avatar,
                'company_name' => $company?->name ?? 'Sin Empresa',
                'created_at' => $u->created_at?->format('d/m/Y'),
            ];
        });

        // 4. Obtener Solicitudes de Acceso según el Alcance
        $requestsQuery = AccessRequest::with('company')
            ->when(!$isOwner, fn ($q) => $q->where('company_id', $activeCompanyId))
            ->latest('requested_at');

        $accessRequests = $requestsQuery->get()->map(fn ($r) => [
            'uuid' => $r->uuid ?? $r->id,
            'name' => $r->user?->name ?? $r->name ?? 'Usuario',
            'email' => $r->user?->email ?? $r->email,
            'company_name' => $r->company?->name ?? 'N/A',
            'message' => $r->message,
            'status' => $r->status,
            'requested_at' => $r->requested_at?->diffForHumans() ?? $r->created_at?->diffForHumans(),
        ]);

        // 5. Empresas (Solo visible para el Owner)
        $companies = $isOwner 
            ? Company::withCount('users')->latest()->get()->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'users_count' => $c->users_count,
                'is_active' => $c->status === 'active',
                'created_at' => $c->created_at?->format('d/m/Y'),
            ])
            : [];

        return Inertia::render('Admin/Index', [
            'authRole' => $authRole,
            'userCompany' => $activeCompany?->name ?? 'Global',
            'users' => $users,
            'accessRequests' => $accessRequests,
            'companies' => $companies,
            'pendingCount' => $accessRequests->where('status', 'pending')->count(),
        ]);
    }

    /**
     * Procesa las invitaciones enviadas desde el modal de administración.
     */
    public function invite(Request $request)
    {
        $user = $request->user();
        $activeCompanyId = session('active_company_id');

        $activeCompany = $user->companies()
            ->where('companies.id', $activeCompanyId)
            ->first() ?? $user->companies()->first();

        $pivotRole = $activeCompany?->pivot->role ?? 'member';
        $isOwner = $pivotRole === 'owner' || $user->email === 'pablo@quiroz.me';

        // Validación básica
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'company_id' => ['nullable'],
            'new_company_name' => ['nullable', 'required_if:company_id,new', 'string', 'max:255'],
            'role' => ['required', 'in:owner,admin,member'],
        ]);

        $targetCompanyId = null;

        if ($isOwner) {
            if ($request->company_id === 'new') {
                $newCompany = Company::create([
                    'name' => $request->new_company_name,
                    'slug' => Str::slug($request->new_company_name),
                    'status' => 'active',
                ]);
                $targetCompanyId = $newCompany->id;
            } else {
                $targetCompanyId = $request->company_id ?? $activeCompany?->id;
            }
        } else {
            $targetCompanyId = $activeCompany?->id;
        }

        if (!$targetCompanyId) {
            return back()->with('error', 'No se pudo determinar la empresa asignada.');
        }

        // Crear o buscar al usuario
        $invitedUser = User::firstOrCreate(
            ['email' => $validated['email']],
            [
                'name' => Str::before($validated['email'], '@'),
                'password' => bcrypt(Str::random(16)),
            ]
        );

        // Vincular usuario a la empresa con el rol asignado en la pivote
        $invitedUser->companies()->syncWithoutDetaching([
            $targetCompanyId => ['role' => $validated['role']],
        ]);

        return back()->with('success', 'Invitación/Alta procesada correctamente.');
    }
}