<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $mode = $request->query('mode', 'login');

        if ($mode === 'register') {
            $request->validate([
                'company_identifier' => ['required', 'string', 'max:255'],
            ]);

            $companyIdentifier = $this->normalizeCompanyIdentifier(
                $request->query('company_identifier')
            );

            $company = Company::query()
                ->whereRaw('LOWER(slug) = ?', [$companyIdentifier])
                ->orWhereRaw('LOWER(name) = ?', [$companyIdentifier])
                ->first();

            if (! $company) {
                throw ValidationException::withMessages([
                    'company_identifier' => 'No encontramos una empresa registrada con ese nombre. Verifica que esté escrito correctamente.',
                ]);
            }

            session([
                'google_auth_mode' => 'register',
                'google_auth_company_id' => $company->id,
            ]);
        } else {
            session([
                'google_auth_mode' => 'login',
            ]);

            session()->forget('google_auth_company_id');
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'No pudimos iniciar sesión con Google. Intenta nuevamente.',
                ]);
        }

        $user = User::query()
            ->where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);

            Auth::login($user, true);

            return redirect()->route('dashboard');
        }

        if (session('google_auth_mode') !== 'register' || ! session('google_auth_company_id')) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'No existe una cuenta registrada con ese correo de Google. Primero crea una cuenta.',
                ]);
        }

        $user = User::create([
            'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Usuario Google',
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
            'company_id' => session('google_auth_company_id'),
            'password' => Hash::make(Str::random(32)),
        ]);

        session()->forget([
            'google_auth_mode',
            'google_auth_company_id',
        ]);

        Auth::login($user, true);

        return redirect()->route('dashboard');
    }

    private function normalizeCompanyIdentifier(string $value): string
    {
        $value = strtolower(trim($value));

        $value = preg_replace('/^https?:\/\//', '', $value);
        $value = preg_replace('/^www\./', '', $value);

        return rtrim($value, '/');
    }
}