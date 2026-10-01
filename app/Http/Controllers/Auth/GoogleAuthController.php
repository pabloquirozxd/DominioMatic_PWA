<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to Google.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google's callback.
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            Log::error('Error en callback de Google OAuth: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'No pudimos iniciar sesión con Google. Intenta nuevamente.',
                ]);
        }

        /*
         * Buscamos primero por Google ID y luego por correo.
         */
        $user = User::query()
            ->where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        /*
         * ---------------------------------------------------------------
         * Usuario existente
         * ---------------------------------------------------------------
         */
        if ($user) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);

            Auth::login($user, true);

            $request->session()->regenerate();

            /*
             * Buscamos la empresa principal.
             * Priorizamos donde tenga rol 'owner' o 'admin' para evitar seleccionar
             * empresas secundarias o vacías por defecto.
             */
            $company = $user->companies()
                ->wherePivot('role', 'owner')
                ->first() 
                ?? $user->companies()->wherePivot('role', 'admin')->first()
                ?? $user->companies()->first();

            if ($company) {
                $request->session()->put(
                    'active_company_id',
                    $company->id
                );

                return redirect()->route('dashboard');
            }

            /*
             * La cuenta existe pero todavía no pertenece
             * a ninguna organización.
             */
            $request->session()->forget('active_company_id');

            return redirect()->route('join.lookup');
        }

        /*
         * ---------------------------------------------------------------
         * Usuario nuevo
         * ---------------------------------------------------------------
         */
        $user = User::create([
            'name' => $googleUser->getName()
                ?: $googleUser->getNickname()
                ?: 'Usuario Google',

            'email' => $googleUser->getEmail(),

            'google_id' => $googleUser->getId(),

            'avatar' => $googleUser->getAvatar(),

            'password' => Hash::make(
                Str::random(32)
            ),
        ]);

        Auth::login($user, true);

        $request->session()->regenerate();

        $request->session()->forget('active_company_id');

        return redirect()->route('join.lookup');
    }
}