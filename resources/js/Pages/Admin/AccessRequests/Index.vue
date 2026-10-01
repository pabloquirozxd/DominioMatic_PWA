<!-- resources/js/Pages/Admin/AccessRequests/Index.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextField from '@/Components/UI/Forms/TextField.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    requests: Object,
    filters: Object,
    counts: Object,
});

const search = ref(props.filters.search || '');
const currentStatus = ref(props.filters.status || 'pending');

let searchTimeout;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('admin.access-requests.index'),
            { search: value, status: currentStatus.value },
            { preserveState: true, replace: true }
        );
    }, 300);
});

const setStatusFilter = (status) => {
    currentStatus.value = status;
    router.get(
        route('admin.access-requests.index'),
        { search: search.value, status },
        { preserveState: true }
    );
};

const approve = (uuid) => {
    if (confirm('¿Estás seguro de aprobar esta solicitud de acceso?')) {
        router.post(route('admin.access-requests.approve', uuid));
    }
};

const reject = (uuid) => {
    if (confirm('¿Deseas rechazar esta solicitud?')) {
        router.post(route('admin.access-requests.reject', uuid));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Solicitudes de Acceso" />

        <div class="py-6 space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-950 dark:text-white">
                        Solicitudes de Acceso
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Gestiona y revisa las peticiones de usuarios que desean unirse a tu organización.
                    </p>
                </div>
            </div>

            <!-- Filtros & Búsqueda -->
            <div class="flex flex-col sm:flex-row gap-4 justify-between items-stretch sm:items-center">
                <div class="flex items-center gap-1 rounded-xl bg-gray-100 p-1 dark:bg-gray-800/60">
                    <button
                        @click="setStatusFilter('pending')"
                        :class="[
                            'px-3 py-1.5 text-xs font-semibold rounded-lg transition-all',
                            currentStatus === 'pending'
                                ? 'bg-white text-gray-900 shadow-xs dark:bg-gray-700 dark:text-white'
                                : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'
                        ]"
                    >
                        Pendientes ({{ counts.pending }})
                    </button>
                    <button
                        @click="setStatusFilter('approved')"
                        :class="[
                            'px-3 py-1.5 text-xs font-semibold rounded-lg transition-all',
                            currentStatus === 'approved'
                                ? 'bg-white text-gray-900 shadow-xs dark:bg-gray-700 dark:text-white'
                                : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'
                        ]"
                    >
                        Aprobadas ({{ counts.approved }})
                    </button>
                    <button
                        @click="setStatusFilter('rejected')"
                        :class="[
                            'px-3 py-1.5 text-xs font-semibold rounded-lg transition-all',
                            currentStatus === 'rejected'
                                ? 'bg-white text-gray-900 shadow-xs dark:bg-gray-700 dark:text-white'
                                : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'
                        ]"
                    >
                        Rechazadas ({{ counts.rejected }})
                    </button>
                </div>

                <div class="w-full sm:w-64">
                    <TextField
                        v-model="search"
                        placeholder="Buscar por nombre o correo..."
                        type="search"
                    />
                </div>
            </div>

            <!-- Tabla de Datos -->
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900/50">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50/50 dark:bg-gray-800/40">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Solicitante</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Mensaje</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Fecha</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Estado</th>
                            <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                        <tr v-for="item in requests.data" :key="item.uuid" class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900 dark:text-white">{{ item.name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ item.email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-gray-600 dark:text-gray-300 max-w-xs truncate">
                                    {{ item.message || 'Sin mensaje adicional' }}
                                </p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                {{ item.requested_at }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="[
                                        'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset',
                                        item.status === 'pending' && 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-400',
                                        item.status === 'approved' && 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-400',
                                        item.status === 'rejected' && 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-400',
                                    ]"
                                >
                                    {{ item.status === 'pending' ? 'Pendiente' : item.status === 'approved' ? 'Aprobada' : 'Rechazada' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-medium">
                                <div v-if="item.status === 'pending'" class="flex justify-end gap-2">
                                    <button
                                        @click="approve(item.uuid)"
                                        class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-medium rounded-lg transition-colors"
                                    >
                                        Aprobar
                                    </button>
                                    <button
                                        @click="reject(item.uuid)"
                                        class="px-3 py-1.5 bg-gray-100 hover:bg-red-50 text-red-600 dark:bg-gray-800 dark:hover:bg-red-950/30 dark:text-red-400 font-medium rounded-lg transition-colors"
                                    >
                                        Rechazar
                                    </button>
                                </div>
                                <span v-else class="text-xs text-gray-400">Revisado {{ item.reviewed_at }}</span>
                            </td>
                        </tr>
                        <tr v-if="requests.data.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No hay solicitudes registradas con este criterio.
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Bloque de Paginación -->
                <div v-if="requests.links && requests.links.length > 3" class="px-6 py-4 border-t border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/40">
                    <div class="flex flex-wrap items-center justify-center gap-1">
                        <template v-for="(link, key) in requests.links" :key="key">
                            <div 
                                v-if="link.url === null" 
                                class="px-3 py-1.5 text-xs text-gray-400 border border-transparent select-none" 
                                v-html="link.label" 
                            />
                            <Link 
                                v-else 
                                :href="link.url" 
                                :class="[
                                    'px-3 py-1.5 text-xs font-medium rounded-lg border transition-colors', 
                                    link.active 
                                        ? 'bg-gray-900 border-gray-900 text-white dark:bg-gray-100 dark:border-gray-100 dark:text-gray-900 shadow-xs' 
                                        : 'border-gray-200 text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800'
                                ]" 
                                v-html="link.label" 
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>