<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        /*
         * Seleccionamos temporalmente la primera empresa
         * a la que pertenece el usuario.
         *
         * Más adelante podremos añadir un selector
         * para que el usuario cambie entre empresas.
         */
        $company = $user->companies()->first();

        if ($company) {
            $request->session()->put(
                'active_company_id',
                $company->id
            );
        } else {
            $request->session()->forget(
                'active_company_id'
            );
        }

        /*
         * Si el usuario todavía no pertenece a ninguna
         * organización, podrá continuar hacia el flujo Join.
         */
        if (! $company) {
            return redirect()->route('join.lookup');
        }

        return redirect()->intended(
            route('dashboard', absolute: false)
        );
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
