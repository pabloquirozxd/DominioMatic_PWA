<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    /**
     * Obtener ID de la empresa del usuario activo.
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
        $subscriptions = Subscription::with(['client', 'product'])
            ->latest()
            ->get()
            ->map(function ($subscription) {
                return [
                    'id' => $subscription->id,
                    'client_id' => $subscription->client_id,
                    'product_id' => $subscription->product_id,
                    'client_name' => $subscription->client?->company_name ?? 'Sin Empresa',
                    'product_name' => $subscription->product?->name ?? 'Sin Producto',
                    'quantity' => $subscription->quantity ?? 1,
                    'billing_cycle' => $subscription->billing_cycle ?? 'monthly',
                    'price_list' => $subscription->price_list,
                    'currency' => $subscription->currency ?? $subscription->product?->currency ?? 'USD',
                    'discount' => $subscription->discount,
                    'total_neto' => $subscription->total_neto,
                    'starts_at' => $subscription->starts_at?->format('Y-m-d'),
                    'expires_at' => $subscription->expires_at?->format('Y-m-d'),
                    'starts_at_formatted' => $subscription->starts_at?->format('d/m/Y'),
                    'expires_at_formatted' => $subscription->expires_at?->format('d/m/Y'),
                    'status' => $subscription->effective_status ?? $subscription->status,
                ];
            })
            ->values();

        // Obtener clientes (Empresas) en lugar de contactos individuales
        $clients = Client::query()
            ->orderBy('company_name')
            ->get(['id', 'company_name', 'type'])
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->company_name,
                ];
            })
            ->values();

        $products = Product::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'price_list',
                'currency',
            ])
            ->values();

        return Inertia::render('Subscriptions/Index', [
            'subscriptions' => $subscriptions,
            'clients' => $clients,
            'products' => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'billing_cycle' => ['required', 'in:weekly,monthly,yearly,custom'],
            'price_list' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'in:USD,BOB'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['required', 'date'],
            'expires_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', 'in:active,expired,suspended'],
        ]);

        $companyId = $this->getCompanyId();
        $quantity = (int) $validated['quantity'];
        $priceList = (float) $validated['price_list'];
        $discount = (float) ($validated['discount'] ?? 0);

        // Cálculo de Total Neto: (Precio Unitario x Cantidad) - Descuento
        $totalNeto = max(0, ($priceList * $quantity) - $discount);

        Subscription::create([
            'company_id' => $companyId,
            'client_id' => $validated['client_id'],
            'product_id' => $validated['product_id'],
            'quantity' => $quantity,
            'billing_cycle' => $validated['billing_cycle'],
            'price_list' => $priceList,
            'currency' => $validated['currency'],
            'discount' => $discount,
            'total_neto' => $totalNeto,
            'starts_at' => $validated['starts_at'],
            'expires_at' => $validated['expires_at'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('subscriptions.index')->with('success', 'Suscripción creada correctamente.');
    }

    public function update(Request $request, Subscription $subscription): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'billing_cycle' => ['required', 'in:weekly,monthly,yearly,custom'],
            'price_list' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'in:USD,BOB'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['required', 'date'],
            'expires_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', 'in:active,expired,suspended'],
        ]);

        $quantity = (int) $validated['quantity'];
        $priceList = (float) $validated['price_list'];
        $discount = (float) ($validated['discount'] ?? 0);

        // Cálculo de Total Neto: (Precio Unitario x Cantidad) - Descuento
        $totalNeto = max(0, ($priceList * $quantity) - $discount);

        $subscription->update([
            'client_id' => $validated['client_id'],
            'product_id' => $validated['product_id'],
            'quantity' => $quantity,
            'billing_cycle' => $validated['billing_cycle'],
            'price_list' => $priceList,
            'currency' => $validated['currency'],
            'discount' => $discount,
            'total_neto' => $totalNeto,
            'starts_at' => $validated['starts_at'],
            'expires_at' => $validated['expires_at'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('subscriptions.index')->with('success', 'Suscripción actualizada correctamente.');
    }

    public function destroy(Subscription $subscription): RedirectResponse
    {
        $subscription->delete();

        return redirect()->route('subscriptions.index')->with('success', 'Suscripción eliminada.');
    }
}