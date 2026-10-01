<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyIsActive
{
    /**
     * Verifica que la empresa activa seleccionada en la sesión esté activa.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $activeCompanyId = session('active_company_id');

        // Si no hay empresa activa en sesión, dejamos pasar (ej. flujos de join/lookup)
        if (! $activeCompanyId) {
            return $next($request);
        }

        // Buscamos la empresa activa del usuario
        $company = $user->companies()
            ->where('companies.id', $activeCompanyId)
            ->first();

        if (! $company) {
            return $next($request);
        }

        if ($company->status !== 'active') {
            auth()->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = match ($company->status) {
                'pending' => 'Tu empresa está pendiente de aprobación por el equipo de soporte.',
                'suspended' => 'La cuenta de tu empresa ha sido suspendida temporalmente.',
                'cancelled' => 'La suscripción de tu empresa ha sido cancelada.',
                default => 'Tu empresa no tiene acceso activo en este momento.',
            };

            return redirect()->route('login')->with('error', $message);
        }

        return $next($request);
    }
}