<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Product;
use App\Models\Subscription;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $contactsCount = Contact::count();
        $productsCount = Product::count();
        $subscriptionsCount = Subscription::count();

        $lowStockQuery = Product::query()
            ->where('is_infinite', false)
            ->where('stock', '<=', 5);

        $lowStockCount = (clone $lowStockQuery)->count();

        $inventoryValue = (float) Product::query()
            ->where('is_infinite', false)
            ->selectRaw('COALESCE(SUM(price_list * stock), 0) as total')
            ->value('total');

        $recentContacts = Contact::query()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($contact) {
                return [
                    'id' => $contact->id,
                    'name' => trim($contact->first_name . ' ' . $contact->last_name),
                    'subtitle' => $contact->position ?: ($contact->type === 'primary' ? 'Contacto principal' : 'Contacto secundario'),
                    'meta' => $contact->email ?: ($contact->phone ?: 'Sin datos de contacto'),
                    'time' => $contact->created_at?->diffForHumans(),
                ];
            })
            ->values();

        $recentProducts = Product::query()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'subtitle' => $product->is_infinite ? 'Inventario ilimitado' : 'Stock: ' . $product->stock,
                    'meta' => '$' . number_format((float) $product->price_list, 2),
                    'time' => $product->created_at?->diffForHumans(),
                ];
            })
            ->values();

        $recentSubscriptions = Subscription::with(['contact', 'product'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($subscription) {
                $contactName = trim(($subscription->contact?->first_name ?? '') . ' ' . ($subscription->contact?->last_name ?? ''));

                return [
                    'id' => $subscription->id,
                    'title' => $contactName !== '' ? $contactName : 'Sin contacto',
                    'subtitle' => $subscription->product?->name ?? 'Sin producto',
                    'meta' => $subscription->expires_at ? $subscription->expires_at->format('d/m/Y') : 'Sin fecha',
                    'status' => $subscription->status,
                    'time' => $subscription->created_at?->diffForHumans(),
                ];
            })
            ->values();

            $subscriptionAlerts = Subscription::with(['contact', 'product'])
            ->whereDate('expires_at', '<=', now()->addDays(30))
            ->orderBy('expires_at')
            ->take(5)
            ->get()
            ->map(function ($subscription) {
                $contactName = trim(
                    ($subscription->contact?->first_name ?? '') . ' ' . ($subscription->contact?->last_name ?? '')
                );

                $expiresAt = $subscription->expires_at?->copy()->startOfDay();
                $today = now()->startOfDay();
                $days = $today->diffInDays($expiresAt, false);

                if ($days > 1) {
                    $label = "Vence en {$days} días";
                    $type = 'upcoming';
                } elseif ($days === 1) {
                    $label = 'Vence mañana';
                    $type = 'upcoming';
                } elseif ($days === 0) {
                    $label = 'Vence hoy';
                    $type = 'upcoming';
                } elseif ($days === -1) {
                    $label = 'Venció ayer';
                    $type = 'overdue';
                } else {
                    $label = 'Venció hace ' . abs($days) . ' días';
                    $type = 'overdue';
                }

                return [
                    'id' => $subscription->id,
                    'title' => $contactName !== '' ? $contactName : 'Sin contacto',
                    'subtitle' => $subscription->product?->name ?? 'Sin producto',
                    'label' => $label,
                    'type' => $type,
                    'date' => $subscription->expires_at?->format('d/m/Y'),
                ];
            })
            ->values();

        $lowStockProducts = (clone $lowStockQuery)
            ->orderBy('stock')
            ->orderBy('name')
            ->take(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => $product->stock,
                    'price' => '$' . number_format((float) $product->price_list, 2),
                ];
            })
            ->values();

        return Inertia::render('Dashboard', [
            'contactsCount' => $contactsCount,
            'productsCount' => $productsCount,
            'subscriptionsCount' => $subscriptionsCount,
            'lowStockCount' => $lowStockCount,
            'inventoryValue' => $inventoryValue,
            'recentContacts' => $recentContacts,
            'recentProducts' => $recentProducts,
            'recentSubscriptions' => $recentSubscriptions,
            'subscriptionAlerts' => $subscriptionAlerts,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}