<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function index(): Response
    {
        $subscriptions = Subscription::with(['contact', 'product'])
            ->latest()
            ->get()
            ->map(function ($subscription) {
                $contactName = trim(($subscription->contact?->first_name ?? '') . ' ' . ($subscription->contact?->last_name ?? ''));

                return [
                    'id' => $subscription->id,
                    'contact_name' => $contactName !== '' ? $contactName : 'Sin contacto',
                    'product_name' => $subscription->product?->name ?? 'Sin producto',
                    'price_list' => $subscription->price_list,
                    'discount' => $subscription->discount,
                    'total_neto' => $subscription->total_neto,
                    'starts_at' => $subscription->starts_at?->format('d/m/Y'),
                    'expires_at' => $subscription->expires_at?->format('d/m/Y'),
                    'status' => $subscription->status,
                ];
            })
            ->values();

        $contacts = Contact::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name'])
            ->map(function ($contact) {
                return [
                    'id' => $contact->id,
                    'name' => trim($contact->first_name . ' ' . $contact->last_name),
                ];
            })
            ->values();

        $products = Product::query()
            ->orderBy('name')
            ->get(['id', 'name', 'price_list'])
            ->values();

        return Inertia::render('Subscriptions/Index', [
            'subscriptions' => $subscriptions,
            'contacts' => $contacts,
            'products' => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contact_id' => ['required', 'exists:contacts,id'],
            'product_id' => ['required', 'exists:products,id'],
            'price_list' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['required', 'date'],
            'expires_at' => ['required', 'date'],
            'status' => ['required', 'in:active,expired,suspended'],
        ]);

        Subscription::create([
            'contact_id' => $validated['contact_id'],
            'product_id' => $validated['product_id'],
            'price_list' => $validated['price_list'],
            'discount' => $validated['discount'] ?? 0,
            'starts_at' => $validated['starts_at'],
            'expires_at' => $validated['expires_at'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('subscriptions.index');
    }

    public function update(Request $request, Subscription $subscription): RedirectResponse
    {
        $validated = $request->validate([
            'contact_id' => ['required', 'exists:contacts,id'],
            'product_id' => ['required', 'exists:products,id'],
            'price_list' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['required', 'date'],
            'expires_at' => ['required', 'date'],
            'status' => ['required', 'in:active,expired,suspended'],
        ]);

        $subscription->update([
            'contact_id' => $validated['contact_id'],
            'product_id' => $validated['product_id'],
            'price_list' => $validated['price_list'],
            'discount' => $validated['discount'] ?? 0,
            'starts_at' => $validated['starts_at'],
            'expires_at' => $validated['expires_at'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('subscriptions.index');
    }

    public function destroy(Subscription $subscription): RedirectResponse
    {
        $subscription->delete();

        return redirect()->route('subscriptions.index');
    }
}