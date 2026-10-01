<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Empresa activa
        |--------------------------------------------------------------------------
        |
        | Un usuario puede pertenecer a varias empresas.
        | La empresa utilizada actualmente se guarda en la sesión.
        |
        */

        $activeCompanyId = $request->session()->get(
            'active_company_id'
        );

        abort_unless(
            $user && $activeCompanyId,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Verificar que la empresa activa realmente pertenece al usuario
        |--------------------------------------------------------------------------
        */

        $company = $user->companies()
            ->whereKey($activeCompanyId)
            ->first();

        abort_unless($company, 403);

        $companyId = $company->id;

        /*
        |--------------------------------------------------------------------------
        | Consultas base
        |--------------------------------------------------------------------------
        |
        | Contact, Product y Subscription pertenecen a una empresa.
        | El company_id se especifica explícitamente para mantener
        | el aislamiento multi-tenant.
        |
        */

        $contactsQuery = Contact::query()
            ->where('contacts.company_id', $companyId);

        $productsQuery = Product::query()
            ->where('products.company_id', $companyId);

        $subscriptionsQuery = Subscription::query()
            ->where('subscriptions.company_id', $companyId);

        /*
        |--------------------------------------------------------------------------
        | Fechas de referencia
        |--------------------------------------------------------------------------
        */

        $today = now()->startOfDay();

        $sevenDaysFromNow = $today->copy()->addDays(7);

        $thirtyDaysFromNow = $today->copy()->addDays(30);

        /*
        |--------------------------------------------------------------------------
        | Resumen general
        |--------------------------------------------------------------------------
        */

        $contactsCount = (clone $contactsQuery)->count();

        $productsCount = (clone $productsQuery)->count();

        $subscriptionsCount = (clone $subscriptionsQuery)->count();

        /*
        |--------------------------------------------------------------------------
        | Estado real de suscripciones
        |--------------------------------------------------------------------------
        |
        | La fecha expires_at determina si una suscripción está vencida.
        |
        | suspended tiene prioridad porque es un estado manual.
        |
        */

        $expiredSubscriptions = (clone $subscriptionsQuery)
            ->where('status', '!=', 'suspended')
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<', $today)
            ->count();

        $activeSubscriptions = (clone $subscriptionsQuery)
            ->where('status', '!=', 'suspended')
            ->where(function ($query) use ($today) {
                $query
                    ->whereNull('expires_at')
                    ->orWhereDate('expires_at', '>=', $today);
            })
            ->count();

        $suspendedSubscriptions = (clone $subscriptionsQuery)
            ->where('status', 'suspended')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Fechas de vencimiento
        |--------------------------------------------------------------------------
        */

        $expiringToday = (clone $subscriptionsQuery)
            ->where('status', '!=', 'suspended')
            ->whereDate('expires_at', $today)
            ->count();

        $expiringNext7Days = (clone $subscriptionsQuery)
            ->where('status', '!=', 'suspended')
            ->whereBetween('expires_at', [
                $today,
                $sevenDaysFromNow,
            ])
            ->count();

        $expiringNext30Days = (clone $subscriptionsQuery)
            ->where('status', '!=', 'suspended')
            ->whereBetween('expires_at', [
                $today,
                $thirtyDaysFromNow,
            ])
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Distribución de estados
        |--------------------------------------------------------------------------
        |
        | Reutilizamos los mismos conteos para evitar inconsistencias
        | entre las tarjetas y la distribución.
        |
        */

        $subscriptionStatusDistribution = collect([
            [
                'status' => 'active',
                'total' => $activeSubscriptions,
            ],
            [
                'status' => 'expired',
                'total' => $expiredSubscriptions,
            ],
            [
                'status' => 'suspended',
                'total' => $suspendedSubscriptions,
            ],
        ])
            ->filter(function ($item) {
                return $item['total'] > 0;
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Alertas de vencimiento
        |--------------------------------------------------------------------------
        |
        | Incluimos:
        | - suscripciones ya vencidas
        | - suscripciones que vencen hoy
        | - suscripciones que vencerán dentro de 30 días
        |
        | Las suspendidas no aparecen.
        |
        */

        $subscriptionAlerts = (clone $subscriptionsQuery)
            ->with([
                'contact',
                'product',
            ])
            ->where('status', '!=', 'suspended')
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<=', $thirtyDaysFromNow)
            ->orderBy('expires_at')
            ->take(8)
            ->get()
            ->map(function ($subscription) use ($today) {
                $contactName = trim(
                    ($subscription->contact?->first_name ?? '') .
                    ' ' .
                    ($subscription->contact?->last_name ?? '')
                );

                $expiresAt = $subscription->expires_at
                    ? $subscription->expires_at->copy()->startOfDay()
                    : null;

                $days = $expiresAt
                    ? $today->diffInDays($expiresAt, false)
                    : null;

                if ($days === null) {
                    $label = 'Sin fecha';
                    $type = 'unknown';
                } elseif ($days > 1) {
                    $label = "Vence en {$days} días";
                    $type = 'upcoming';
                } elseif ($days === 1) {
                    $label = 'Vence mañana';
                    $type = 'upcoming';
                } elseif ($days === 0) {
                    $label = 'Vence hoy';
                    $type = 'today';
                } elseif ($days === -1) {
                    $label = 'Venció ayer';
                    $type = 'overdue';
                } else {
                    $label = 'Venció hace ' . abs($days) . ' días';
                    $type = 'overdue';
                }

                return [
                    'id' => $subscription->id,

                    'title' => $contactName !== ''
                        ? $contactName
                        : 'Sin contacto',

                    'subtitle' => $subscription->product?->name
                        ?? 'Sin producto',

                    'status' => $subscription->effective_status,

                    'label' => $label,

                    'type' => $type,

                    'date' => $subscription->expires_at
                        ? $subscription->expires_at->format('d/m/Y')
                        : null,

                    'days' => $days,

                    'currency' => $subscription->currency,

                    'total' => (float) $subscription->total_neto,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Inventario finito
        |--------------------------------------------------------------------------
        */

        $finiteProductsQuery = (clone $productsQuery)
            ->where('is_infinite', false);

        /*
        |--------------------------------------------------------------------------
        | Stock bajo y agotado
        |--------------------------------------------------------------------------
        */

        $lowStockQuery = (clone $finiteProductsQuery)
            ->whereBetween('stock', [1, 5]);

        $outOfStockQuery = (clone $finiteProductsQuery)
            ->where('stock', '<=', 0);

        $finiteProductsCount = (clone $finiteProductsQuery)
            ->count();

        $lowStockCount = (clone $lowStockQuery)
            ->count();

        $outOfStockCount = (clone $outOfStockQuery)
            ->count();

        $inventoryUnits = (int) (clone $finiteProductsQuery)
            ->sum('stock');

        /*
        |--------------------------------------------------------------------------
        | Valor del inventario por moneda
        |--------------------------------------------------------------------------
        */

        $inventoryValueByCurrency = (clone $finiteProductsQuery)
            ->select('currency')
            ->selectRaw(
                'COALESCE(SUM(price_list * stock), 0) as total'
            )
            ->groupBy('currency')
            ->orderBy('currency')
            ->get()
            ->map(function ($item) {
                return [
                    'currency' => $item->currency,
                    'total' => (float) $item->total,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Productos con stock bajo
        |--------------------------------------------------------------------------
        */

        $lowStockProducts = (clone $lowStockQuery)
            ->orderBy('stock')
            ->orderBy('name')
            ->take(8)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => (int) $product->stock,
                    'currency' => $product->currency,
                    'price' => (float) $product->price_list,
                    'inventoryValue' => (float) $product->price_list
                        * (int) $product->stock,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Productos más utilizados
        |--------------------------------------------------------------------------
        */

        $topProducts = Product::withoutGlobalScope('company_scope')
            ->where('products.company_id', $companyId)
            ->leftJoin('subscriptions', function ($join) use ($companyId) {
                $join->on(
                    'products.id',
                    '=',
                    'subscriptions.product_id'
                )->where(
                    'subscriptions.company_id',
                    '=',
                    $companyId
                );
            })
            ->select(
                'products.id',
                'products.name'
            )
            ->selectRaw(
                'COUNT(subscriptions.id) as subscriptions_count'
            )
            ->groupBy(
                'products.id',
                'products.name'
            )
            ->orderByDesc('subscriptions_count')
            ->orderBy('products.name')
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Valores de los productos más utilizados
        |--------------------------------------------------------------------------
        */

        $topProductIds = $topProducts
            ->pluck('id')
            ->values();

        $topProductValues = collect();

        if ($topProductIds->isNotEmpty()) {
            $topProductValues = Subscription::query()
                ->where('company_id', $companyId)
                ->whereIn('product_id', $topProductIds)
                ->select(
                    'product_id',
                    'currency'
                )
                ->selectRaw(
                    'COALESCE(SUM(total_neto), 0) as total'
                )
                ->groupBy(
                    'product_id',
                    'currency'
                )
                ->get()
                ->groupBy('product_id');
        }

        $topProducts = $topProducts
            ->map(function ($product) use ($topProductValues) {
                $values = $topProductValues->get(
                    $product->id,
                    collect()
                );

                return [
                    'id' => $product->id,

                    'name' => $product->name,

                    'subscriptions' => (int) $product->subscriptions_count,

                    'valuesByCurrency' => $values
                        ->map(function ($item) {
                            return [
                                'currency' => $item->currency,
                                'total' => (float) $item->total,
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Tendencia de suscripciones
        |--------------------------------------------------------------------------
        |
        | Últimos 6 meses.
        |
        */

        $trendStart = now()
            ->copy()
            ->startOfMonth()
            ->subMonths(5);

        $subscriptionTrendRows = (clone $subscriptionsQuery)
            ->where(
                'subscriptions.created_at',
                '>=',
                $trendStart
            )
            ->selectRaw("
                DATE_FORMAT(
                    subscriptions.created_at,
                    '%Y-%m'
                ) as month,

                COUNT(subscriptions.id) as subscriptions_count,

                subscriptions.currency,

                COALESCE(
                    SUM(subscriptions.total_neto),
                    0
                ) as total
            ")
            ->groupByRaw("
                DATE_FORMAT(
                    subscriptions.created_at,
                    '%Y-%m'
                ),
                subscriptions.currency
            ")
            ->orderByRaw("
                DATE_FORMAT(
                    subscriptions.created_at,
                    '%Y-%m'
                ) ASC
            ")
            ->get();

        $monthLabels = [
            1 => 'Ene',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Abr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ago',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dic',
        ];

        $subscriptionTrend = collect();

        for ($i = 0; $i < 6; $i++) {
            $month = $trendStart
                ->copy()
                ->addMonths($i);

            $monthKey = $month->format('Y-m');

            $monthRows = $subscriptionTrendRows
                ->where('month', $monthKey);

            $subscriptionCount = (int) $monthRows
                ->sum('subscriptions_count');

            $valuesByCurrency = $monthRows
                ->map(function ($item) {
                    return [
                        'currency' => $item->currency,
                        'total' => (float) $item->total,
                    ];
                })
                ->values();

            $subscriptionTrend->push([
                'month' => $monthKey,

                'label' => $monthLabels[
                    (int) $month->format('n')
                ],

                'subscriptions' => $subscriptionCount,

                'valuesByCurrency' => $valuesByCurrency,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Valor registrado de suscripciones por moneda
        |--------------------------------------------------------------------------
        */

        $subscriptionValueByCurrency = (clone $subscriptionsQuery)
            ->select('currency')
            ->selectRaw(
                'COALESCE(SUM(total_neto), 0) as total'
            )
            ->groupBy('currency')
            ->orderBy('currency')
            ->get()
            ->map(function ($item) {
                return [
                    'currency' => $item->currency,
                    'total' => (float) $item->total,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Respuesta Inertia
        |--------------------------------------------------------------------------
        */

        return Inertia::render('Dashboard', [
            'overview' => [
                'contacts' => $contactsCount,
                'products' => $productsCount,
                'subscriptions' => $subscriptionsCount,
            ],

            'subscriptionStats' => [
                'total' => $subscriptionsCount,
                'active' => $activeSubscriptions,
                'expired' => $expiredSubscriptions,
                'suspended' => $suspendedSubscriptions,
                'expiringToday' => $expiringToday,
                'expiring7Days' => $expiringNext7Days,
                'expiring30Days' => $expiringNext30Days,
                'distribution' => $subscriptionStatusDistribution,
            ],

            'subscriptionTrend' => $subscriptionTrend->values(),

            'subscriptionValueByCurrency' => $subscriptionValueByCurrency,

            'topProducts' => $topProducts,

            'inventoryStats' => [
                'units' => $inventoryUnits,
                'finiteProducts' => $finiteProductsCount,
                'lowStock' => $lowStockCount,
                'outOfStock' => $outOfStockCount,
                'valueByCurrency' => $inventoryValueByCurrency,
            ],

            'subscriptionAlerts' => $subscriptionAlerts,

            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}