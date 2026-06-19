<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    subscriptions: Array,
    contacts: Array,
    products: Array,
});

const showModal = ref(false);
const editing = ref(false);
const selectedId = ref(null);

const search = ref('');
const filterStatus = ref('all');

const form = useForm({
    contact_id: '',
    product_id: '',
    price_list: '',
    discount: 0,
    starts_at: '',
    expires_at: '',
    status: 'active',
});

const filteredSubscriptions = computed(() => {
    return props.subscriptions.filter((item) => {
        const contactName = item.contact?.name || '';
        const productName = item.product?.name || '';

        const matchesSearch =
            contactName.toLowerCase().includes(search.value.toLowerCase()) ||
            productName.toLowerCase().includes(search.value.toLowerCase());

        const matchesStatus =
            filterStatus.value === 'all'
                ? true
                : item.status === filterStatus.value;

        return matchesSearch && matchesStatus;
    });
});

function openCreate() {
    editing.value = false;
    selectedId.value = null;
    form.reset();
    form.discount = 0;
    form.status = 'active';
    showModal.value = true;
}

function openEdit(subscription) {
    editing.value = true;
    selectedId.value = subscription.id;
    form.contact_id = subscription.contact_id;
    form.product_id = subscription.product_id;
    form.price_list = subscription.price_list;
    form.discount = subscription.discount;
    form.starts_at = subscription.starts_at;
    form.expires_at = subscription.expires_at;
    form.status = subscription.status;
    showModal.value = true;
}

function saveSubscription() {
    if (editing.value) {
        form.put(route('subscriptions.update', selectedId.value));
    } else {
        form.post(route('subscriptions.store'));
    }
    showModal.value = false;
}

function deleteSubscription(id) {
    if (confirm('¿Eliminar esta suscripción?')) {
        router.delete(route('subscriptions.destroy', id));
    }
}
</script>

<template>
<Head title="Suscripciones | DominioMatic" />

<AuthenticatedLayout>
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8 text-gray-900 dark:text-gray-100">
        
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Suscripciones
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Gestión de servicios contratados y ciclos de facturación.
                </p>
            </div>

            <button
                @click="openCreate"
                class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-slate-800 dark:bg-white dark:text-black"
            >
                Nueva suscripción
            </button>
        </div>

        <div class="glass-card p-6">
            
            <div class="flex flex-col md:flex-row gap-3 mb-6">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Buscar por cliente o servicio..."
                    class="flex-1 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-gray-100 dark:placeholder:text-gray-500"
                />

                <select
                    v-model="filterStatus"
                    class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-gray-100"
                >
                    <option value="all">Todos los estados</option>
                    <option value="active">Activas</option>
                    <option value="expired">Vencidas</option>
                    <option value="suspended">Suspendidas</option>
                </select>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-100 dark:border-white/10">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-white/10">
                        <thead class="bg-gray-50 dark:bg-[#1c1c1f]">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Cliente</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Producto</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Neto</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Estado</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white dark:divide-white/10 dark:bg-transparent">
                            <tr
                                v-for="subscription in filteredSubscriptions"
                                :key="subscription.id"
                                class="hover:bg-gray-50/80 dark:hover:bg-[#1c1c1f] transition-colors"
                            >
                                <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ subscription.contact_name }}
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    {{ subscription.product_name }}
                                </td>
                                <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                    ${{ subscription.total_neto }}
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize shadow-sm dark:bg-white/10 dark:text-gray-300 bg-gray-100 text-gray-800"
                                    >
                                        {{ subscription.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right text-sm font-medium space-x-2">
                                    <button
                                        @click="openEdit(subscription)"
                                        class="text-[#0066cc] hover:underline"
                                    >
                                        Editar
                                    </button>
                                    <button
                                        @click="deleteSubscription(subscription.id)"
                                        class="text-rose-600 hover:underline"
                                    >
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!filteredSubscriptions.length">
                                <td colspan="5" class="px-4 py-14 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No se encontraron registros de suscripción.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="glass-card p-6 w-full max-w-xl shadow-2xl transition-all">
            
            <div class="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ editing ? 'Editar Suscripción' : 'Nueva Suscripción' }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Configura el ciclo, precio y estado de la afiliación.
                    </p>
                </div>
                <button type="button" class="text-gray-400 transition hover:text-gray-700 dark:hover:text-gray-200" @click="showModal = false">✕</button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Contacto / Cliente</label>
                    <select v-model="form.contact_id" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white">
                        <option value="">Seleccionar contacto</option>
                        <option v-for="contact in contacts" :key="contact.id" :value="contact.id">{{ contact.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Producto / Servicio</label>
                    <select v-model="form.product_id" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white">
                        <option value="">Seleccionar producto</option>
                        <option v-for="product in products" :key="product.id" :value="product.id">{{ product.name }}</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Precio de Lista</label>
                        <input v-model="form.price_list" type="number" placeholder="0.00" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Descuento aplicado</label>
                        <input v-model="form.discount" type="number" placeholder="0.00" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Fecha Inicio</label>
                        <input v-model="form.starts_at" type="date" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Fecha Expiración</label>
                        <input v-model="form.expires_at" type="date" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Estado de la suscripción</label>
                    <select v-model="form.status" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white">
                        <option value="active">Activa</option>
                        <option value="expired">Vencida</option>
                        <option value="suspended">Suspendidas</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button @click="showModal = false" class="rounded-2xl border border-gray-200 px-5 py-3 text-sm font-semibold text-gray-700 dark:border-white/10 dark:text-gray-200">
                    Cancelar
                </button>
                <button @click="saveSubscription" class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white dark:bg-white dark:text-black">
                    Guardar
                </button>
            </div>

        </div>
    </div>
</AuthenticatedLayout>
</template>