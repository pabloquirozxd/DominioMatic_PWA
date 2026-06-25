<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'company_identifier' => 'required|string|max:255',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $companyIdentifier = $this->normalizeCompanyIdentifier($validated['company_identifier']);

        $company = Company::query()
            ->whereRaw('LOWER(slug) = ?', [$companyIdentifier])
            ->orWhereRaw('LOWER(name) = ?', [$companyIdentifier])
            ->first();

        if (! $company) {
            throw ValidationException::withMessages([
                'company_identifier' => 'No encontramos una empresa registrada con ese nombre. Verifica que esté escrito correctamente.',
            ]);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'company_id' => $company->id,
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    private function normalizeCompanyIdentifier(string $value): string
    {
        $value = strtolower(trim($value));

        $value = preg_replace('/^https?:\/\//', '', $value);
        $value = preg_replace('/^www\./', '', $value);

        return rtrim($value, '/');
    }
}