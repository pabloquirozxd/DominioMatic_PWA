<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $activeCompanyId = $request->session()->get('active_company_id');

        $activeCompany = null;
        $roleInCompany = null;

        if ($user) {
            // Intentamos buscar la empresa elegida en sesión
            $company = null;

            if ($activeCompanyId) {
                $company = $user->companies()
                    ->where('companies.id', $activeCompanyId)
                    ->first();
            }

            // Fallback: Si no hay ID en sesión o no se encontró, tomar la primera empresa asociada
            if (!$company) {
                $company = $user->companies()->first();

                if ($company) {
                    $request->session()->put('active_company_id', $company->id);
                }
            }

            if ($company) {
                $activeCompany = [
                    'id' => $company->id,
                    'name' => $company->name,
                    'slug' => $company->slug,
                    'is_dominiomatic' => (bool) ($company->is_dominiomatic ?? false),
                    'primary_color' => $company->primary_color,
                    'secondary_color' => $company->secondary_color,
                ];

                // Extraer el rol desde la pivote
                $roleInCompany = $company->pivot->role ?? 'member';
            }
        }

        return [
            ...parent::share($request),

            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'is_dominiomatic' => (bool) ($user->is_dominiomatic ?? false),
                    'role' => $roleInCompany ?? 'member', // <-- Inyectamos directamente el rol dentro de user
                ] : null,

                'company' => $activeCompany,
                'role' => $roleInCompany ?? 'member', // Mantenemos también la propiedad a nivel raíz por compatibilidad
            ],

            // Mensajes flash
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'info'    => fn () => $request->session()->get('info'),
            ],
        ];
    }
}