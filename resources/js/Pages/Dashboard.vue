<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    contactsCount: { type: Number, default: 0 },
    productsCount: { type: Number, default: 0 },
    subscriptionsCount: { type: Number, default: 0 },
    lowStockCount: { type: Number, default: 0 },
    inventoryValue: { type: Number, default: 0 },
    recentContacts: { type: Array, default: () => [] },
    recentProducts: { type: Array, default: () => [] },
    recentSubscriptions: { type: Array, default: () => [] },
    subscriptionAlerts: { type: Array, default: () => [] },
    lowStockProducts: { type: Array, default: () => [] },
});

const metrics = computed(() => [
    { label: 'Contactos', value: props.contactsCount, hint: 'Directorio activo', accent: 'from-[#0066cc] to-cyan-400' },
    { label: 'Productos', value: props.productsCount, hint: 'Servicios registrados', accent: 'from-[#c600bf] to-fuchsia-400' },
    { label: 'Suscripciones', value: props.subscriptionsCount, hint: 'Ciclo en curso', accent: 'from-[#ff2d55] to-rose-400' },
    { label: 'Stock bajo', value: props.lowStockCount, hint: 'Requieren atención', accent: 'from-slate-700 to-slate-500' },
]);

function formatMoney(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 2,
    }).format(Number(value || 0));
}
</script>

<template>
    <Head title="Dashboard | DominioMatic" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            
            <div class="glass-card mb-6 flex items-center justify-between gap-4 p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-[#0066cc] via-[#c600bf] to-[#ff2d55] text-sm font-bold text-white shadow-lg shadow-fuchsia-500/20">
                        D
                    </div>
                    <div>
                        <p class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">DominioMatic</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">WebApp SaaS</p>
                    </div>
                </div>

                <div class="hidden flex-1 px-6 md:block">
                    <div class="relative max-w-md">
                        <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-gray-400">⌘</span>
                        <input
                            type="text"
                            placeholder="Buscar en la app..."
                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 py-3 pl-10 pr-4 text-sm outline-none transition focus:border-[#0066cc] focus:bg-white dark:border-white/10 dark:bg-white/5 dark:text-gray-100 dark:placeholder:text-gray-500"
                        />
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        href="/contacts"
                        class="rounded-2xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:bg-white/10"
                    >
                        Contactos
                    </Link>
                    <Link
                        href="/products"
                        class="rounded-2xl bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800 dark:bg-white dark:text-black"
                    >
                        Productos
                    </Link>
                </div>
            </div>

            <div class="mb-6 md:hidden">
                <input
                    type="text"
                    placeholder="Buscar..."
                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-white/5 dark:text-gray-100 dark:placeholder:text-gray-500"
                />
            </div>

            <div class="grid gap-6 lg:grid-cols-[260px_1fr]">
                <aside class="glass-card p-4">
                    <div class="mb-4 rounded-3xl bg-gradient-to-br from-[#0066cc] via-[#c600bf] to-[#ff2d55] p-5 text-white shadow-lg shadow-fuchsia-500/20">
                        <p class="text-xs uppercase tracking-[0.25em] text-white/80">Estado del sistema</p>
                        <p class="mt-3 text-sm text-white/90">Valor inventario: {{ formatMoney(inventoryValue) }}</p>
                        <p class="mt-2 text-lg font-semibold">Operativo</p>
                        <p class="mt-1 text-sm text-white/90">Datos en tiempo real</p>
                    </div>

                    <nav class="space-y-2">
                        <Link
                            href="/dashboard"
                            class="flex items-center justify-between rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-lg dark:bg-white dark:text-black"
                        >
                            <span>Dashboard</span>
                            <span>↗</span>
                        </Link>

                        <Link
                            href="/contacts"
                            class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:bg-white/10"
                        >
                            <span>Contactos</span>
                            <span>{{ contactsCount }}</span>
                        </Link>

                        <Link
                            href="/products"
                            class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:bg-white/10"
                        >
                            <span>Productos</span>
                            <span>{{ productsCount }}</span>
                        </Link>
                        <Link
                            href="/subscriptions"
                            class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:bg-white/10"
                        >
                            <span>Suscripciones</span>
                            <span>{{ subscriptionsCount }}</span>
                        </Link>
                    </nav>
                </aside>

                <main class="space-y-6">
                    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <article
                            v-for="metric in metrics"
                            :key="metric.label"
                            class="glass-card group p-5 hover:-translate-y-1 hover:shadow-2xl"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ metric.label }}</p>
                                    <p class="mt-2 text-4xl font-semibold tracking-tight text-gray-900 dark:text-white">
                                        {{ metric.money ? formatMoney(metric.value) : metric.value }}
                                    </p>
                                    <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">{{ metric.hint }}</p>
                                </div>
                                <div :class="['rounded-2xl px-3 py-2 text-sm text-white shadow-lg bg-gradient-to-br', metric.accent]">
                                    ●
                                </div>
                            </div>
                        </article>
                    </section>

                    <section class="grid gap-6 xl:grid-cols-2">
                        <div class="glass-card p-6">
                            <div class="mb-5 flex items-center justify-between gap-4">
                                <div>
                                    <h2 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-white">Contactos recientes</h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Últimos registros desde MySQL.</p>
                                </div>
                                <Link href="/contacts" class="text-sm font-medium text-[#0066cc] hover:underline">Ver todo</Link>
                            </div>

                            <div class="space-y-3">
                                <div v-for="contact in recentContacts" :key="contact.id" class="flex items-start justify-between gap-4 rounded-2xl bg-gray-50 p-4 dark:bg-white/5">
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ contact.name }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ contact.subtitle }}</p>
                                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ contact.meta }}</p>
                                    </div>
                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ contact.time }}</span>
                                </div>
                                <div v-if="!recentContacts.length" class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-gray-400">
                                    Todavía no hay contactos.
                                </div>
                            </div>
                        </div>

                        <div class="glass-card p-6">
                            <div class="mb-5 flex items-center justify-between gap-4">
                                <div>
                                    <h2 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-white">Productos recientes</h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Últimos servicios registrados.</p>
                                </div>
                                <Link href="/products" class="text-sm font-medium text-[#0066cc] hover:underline">Ver todo</Link>
                            </div>

                            <div class="space-y-3">
                                <div v-for="product in recentProducts" :key="product.id" class="rounded-2xl bg-gray-50 p-4 dark:bg-white/5">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-900 dark:text-white">{{ product.name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ product.subtitle }}</p>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ product.meta }}</span>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">{{ product.time }}</p>
                                </div>
                                <div v-if="!recentProducts.length" class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-gray-400">
                                    Todavía no hay productos.
                                </div>
                            </div>
                        </div>

                        <div class="glass-card p-6 xl:col-span-2">
                            <div class="mb-5">
                                <h2 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-white">Suscripciones recientes</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Ciclos activos y renovaciones.</p>
                            </div>

                            <div class="space-y-3">
                                <div v-for="item in recentSubscriptions" :key="item.id" class="rounded-2xl bg-gray-50 p-4 dark:bg-white/5">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-900 dark:text-white">{{ item.title }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ item.subtitle }}</p>
                                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ item.meta }}</p>
                                        </div>
                                        <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-600 shadow-sm dark:bg-white/10 dark:text-gray-300">
                                            {{ item.status }}
                                        </span>
                                    </div>
                                </div>
                                <div v-if="!recentSubscriptions.length" class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-gray-400">
                                    Todavía no hay suscripciones.
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="glass-card p-6">
                        <div class="mb-5">
                            <h2 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-white">Alertas de vencimiento</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Suscripciones próximas a vencer o ya vencidas.</p>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="alert in subscriptionAlerts"
                                :key="alert.id"
                                class="rounded-2xl border p-4"
                                :class="alert.type === 'overdue'
                                    ? 'border-rose-200 bg-rose-50 dark:border-rose-500/20 dark:bg-rose-500/5'
                                    : 'border-amber-200 bg-amber-50 dark:border-amber-500/20 dark:bg-amber-500/5'"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ alert.title }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ alert.subtitle }}</p>
                                    </div>
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-medium"
                                        :class="alert.type === 'overdue'
                                            ? 'bg-white text-rose-700 dark:bg-white/10 dark:text-rose-300'
                                            : 'bg-white text-amber-700 dark:bg-white/10 dark:text-amber-300'"
                                    >
                                        {{ alert.label }}
                                    </span>
                                </div>
                                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Vence el {{ alert.date }}</p>
                            </div>

                            <div v-if="!subscriptionAlerts.length" class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-gray-400">
                                No hay vencimientos próximos.
                            </div>
                        </div>
                    </section>

                    <section class="glass-card p-6">
                        <div class="mb-5">
                            <h2 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-white">Alertas de stock bajo</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Productos que necesitan revisión.</p>
                        </div>

                        <div class="overflow-hidden rounded-2xl border border-gray-100 dark:border-white/10">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-100 dark:divide-white/10">
                                    <thead class="bg-gray-50 dark:bg-white/5">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Producto</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Stock</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Precio</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-white/10 dark:bg-transparent">
                                        <tr v-for="item in lowStockProducts" :key="item.id" class="hover:bg-gray-50/80 dark:hover:bg-white/5">
                                            <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ item.name }}</td>
                                            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ item.stock }}</td>
                                            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ item.price }}</td>
                                        </tr>
                                        <tr v-if="!lowStockProducts.length">
                                            <td colspan="3" class="px-4 py-14 text-center text-sm text-gray-500 dark:text-gray-400">
                                                No hay productos con stock bajo.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </AuthenticatedLayout>
</template>