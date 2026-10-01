<!-- resources/js/Pages/Clients/Components/ClientTable.vue -->
<script setup>
import { ref } from 'vue'
import {
    PencilSquareIcon,
    TrashIcon,
    UserGroupIcon,
    BuildingOffice2Icon,
    UserIcon,
    GlobeAltIcon,
    ArrowTopRightOnSquareIcon,
    Squares2X2Icon,
    ListBulletIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    clients: {
        type: Array,
        default: () => [],
    },
    searchQuery: {
        type: String,
        default: '',
    },
})

const emit = defineEmits(['open-detail', 'open-edit', 'open-delete'])

// Cambiador de modo de vista ('grid' | 'table')
const viewMode = ref('grid')
</script>

<template>
    <div class="space-y-4">
        <!-- Selector de modo de vista -->
        <div class="flex items-center justify-end gap-1">
            <button
                type="button"
                @click="viewMode = 'grid'"
                :class="[
                    viewMode === 'grid'
                        ? 'bg-black/10 text-gray-900 dark:bg-white/20 dark:text-white'
                        : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300',
                ]"
                class="rounded-xl p-2 transition active:scale-90"
                title="Vista en tarjetas"
            >
                <Squares2X2Icon class="h-5 w-5" />
            </button>

            <button
                type="button"
                @click="viewMode = 'table'"
                :class="[
                    viewMode === 'table'
                        ? 'bg-black/10 text-gray-900 dark:bg-white/20 dark:text-white'
                        : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300',
                ]"
                class="rounded-xl p-2 transition active:scale-90"
                title="Vista en tabla"
            >
                <ListBulletIcon class="h-5 w-5" />
            </button>
        </div>

        <!-- MODO 1: GRID DE TARJETAS -->
        <div v-if="viewMode === 'grid' && clients.length > 0" class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            <div
                v-for="client in clients"
                :key="client.id"
                class="glass-card flex flex-col justify-between p-6 transition hover:-translate-y-1 hover:shadow-xl"
            >
                <div>
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ client.company_name }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ client.type === 'company' ? 'Empresa' : 'Cliente individual' }}
                            </p>
                        </div>

                        <span class="rounded-xl bg-[#007AFF]/10 px-3 py-1 text-xs font-semibold text-[#007AFF]">
                            {{ client.language }}
                        </span>
                    </div>

                    <div class="mt-4 flex items-center gap-2 rounded-2xl bg-black/5 p-3 dark:bg-white/5">
                        <UserGroupIcon class="h-5 w-5 text-[#007AFF]" />
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                            {{ client.contacts?.length || 0 }} contacto(s) registrado(s)
                        </span>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-between border-t border-gray-200/60 pt-4 dark:border-white/10">
                    <button
                        type="button"
                        @click="$emit('open-detail', client)"
                        class="inline-flex items-center gap-1 text-sm font-semibold text-[#007AFF] transition hover:underline"
                    >
                        <span>Ver detalles y contactos</span>
                        <span>→</span>
                    </button>

                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            @click="$emit('open-edit', client)"
                            class="rounded-xl p-2 text-gray-400 transition hover:bg-black/5 hover:text-gray-700 active:scale-90 dark:hover:bg-white/10 dark:hover:text-white"
                            title="Editar cliente"
                        >
                            <PencilSquareIcon class="h-4 w-4" />
                        </button>
                        <button
                            type="button"
                            @click="$emit('open-delete', client)"
                            class="rounded-xl p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-600 active:scale-90 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                            title="Eliminar cliente"
                        >
                            <TrashIcon class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODO 2: TABLA TIPO MACOS -->
        <div v-else-if="viewMode === 'table' && clients.length > 0" class="glass-card overflow-hidden rounded-3xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-black/5 bg-black/[0.02] text-xs font-semibold uppercase tracking-wider text-gray-500 dark:border-white/10 dark:bg-white/[0.02] dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-4">Cliente / Empresa</th>
                            <th class="px-6 py-4">Sitio Web</th>
                            <th class="px-6 py-4">Idioma</th>
                            <th class="px-6 py-4 text-center">Contactos</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/10">
                        <tr
                            v-for="client in clients"
                            :key="client.id"
                            class="transition hover:bg-black/[0.02] dark:hover:bg-white/[0.02]"
                        >
                            <!-- Nombre e Ícono -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-black/5 text-gray-600 dark:bg-white/10 dark:text-gray-300">
                                        <component :is="client.type === 'company' ? BuildingOffice2Icon : UserIcon" class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-white">
                                            {{ client.company_name }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ client.type === 'company' ? 'Empresa' : 'Individual' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Website -->
                            <td class="px-6 py-4">
                                <a
                                    v-if="client.website"
                                    :href="client.website.startsWith('http') ? client.website : `https://${client.website}`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 font-medium text-[#007AFF] hover:underline"
                                >
                                    <span>{{ client.website }}</span>
                                    <ArrowTopRightOnSquareIcon class="h-3.5 w-3.5" />
                                </a>
                                <span v-else class="italic text-gray-400 dark:text-gray-500">Sin registro</span>
                            </td>

                            <!-- Idioma -->
                            <td class="px-6 py-4">
                                <span class="rounded-lg bg-[#007AFF]/10 px-2.5 py-1 text-xs font-semibold text-[#007AFF]">
                                    {{ client.language }}
                                </span>
                            </td>

                            <!-- Contactos -->
                            <td class="px-6 py-4 text-center">
                                <button
                                    type="button"
                                    @click="$emit('open-detail', client)"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-black/5 px-3 py-1 text-xs font-semibold text-gray-700 transition hover:bg-black/10 dark:bg-white/10 dark:text-gray-300 dark:hover:bg-white/20"
                                >
                                    <UserGroupIcon class="h-3.5 w-3.5 text-[#007AFF]" />
                                    <span>{{ client.contacts?.length || 0 }}</span>
                                </button>
                            </td>

                            <!-- Acciones -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button
                                        type="button"
                                        @click="$emit('open-detail', client)"
                                        class="rounded-xl px-2.5 py-1.5 text-xs font-semibold text-[#007AFF] hover:bg-[#007AFF]/10"
                                    >
                                        Ver
                                    </button>
                                    <button
                                        type="button"
                                        @click="$emit('open-edit', client)"
                                        class="rounded-xl p-2 text-gray-400 hover:bg-black/5 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white"
                                        title="Editar"
                                    >
                                        <PencilSquareIcon class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        @click="$emit('open-delete', client)"
                                        class="rounded-xl p-2 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                                        title="Eliminar"
                                    >
                                        <TrashIcon class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ESTADO VACÍO (EMPTY STATE) -->
        <div
            v-else
            class="rounded-3xl border border-dashed border-gray-300 py-12 text-center dark:border-white/10"
        >
            <p class="text-sm text-gray-500 dark:text-gray-400">
                No se encontraron clientes que coincidan con "{{ searchQuery }}".
            </p>
        </div>
    </div>
</template>