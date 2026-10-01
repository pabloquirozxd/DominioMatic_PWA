<!-- resources/js/Pages/Clients/Index.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

import PrimaryButton from '@/Components/UI/Buttons/PrimaryButton.vue'
import SecondaryButton from '@/Components/UI/Buttons/SecondaryButton.vue'
import GlassDropdown from '@/Components/UI/GlassDropdown.vue'

import CreateClientModal from '@/Pages/Clients/Components/CreateClientModal.vue'
import EditClientModal from '@/Pages/Clients/Components/EditClientModal.vue'
import DeleteClientModal from '@/Pages/Clients/Components/DeleteClientModal.vue'
import ImportClientsModal from '@/Pages/Clients/Components/ImportClientsModal.vue'
import ClientDetailModal from '@/Pages/Clients/Components/ClientDetailModal.vue'

import {
    PlusIcon,
    PencilSquareIcon,
    TrashIcon,
    MagnifyingGlassIcon,
    ArrowUpTrayIcon,
    DocumentArrowDownIcon,
    TableCellsIcon,
    UserGroupIcon,
    GlobeAltIcon,
    ChevronDownIcon,
} from '@heroicons/vue/24/outline'

defineOptions({
    layout: AuthenticatedLayout,
});

const props = defineProps({
    clients: {
        type: Array,
        default: () => [],
    },
})

const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const showImportModal = ref(false)
const showDetailModal = ref(false)

const selectedClient = ref(null)
const searchQuery = ref('')

const filteredClients = computed(() => {
    if (!searchQuery.value.trim()) return props.clients

    const query = searchQuery.value.toLowerCase().trim()

    return props.clients.filter((client) => {
        const nameMatch = client.company_name?.toLowerCase().includes(query)
        const typeMatch = (client.type === 'company' ? 'empresa' : 'persona').includes(query)
        const langMatch = client.language?.toLowerCase().includes(query)
        const webMatch = client.website?.toLowerCase().includes(query)

        const contactMatch = client.contacts?.some(
            (c) =>
                `${c.first_name || ''} ${c.last_name || ''}`
                    .toLowerCase()
                    .includes(query) ||
                c.email?.toLowerCase().includes(query)
        )

        return nameMatch || typeMatch || langMatch || webMatch || contactMatch
    })
})

const openEditModal = (client) => {
    selectedClient.value = client
    showEditModal.value = true
}

const closeEditModal = () => {
    showEditModal.value = false
    selectedClient.value = null
}

const openDeleteModal = (client) => {
    selectedClient.value = client
    showDeleteModal.value = true
}

const closeDeleteModal = () => {
    showDeleteModal.value = false
    selectedClient.value = null
}

const openDetailModal = (client) => {
    selectedClient.value = client
    showDetailModal.value = true
}

const handleRefresh = () => {
    router.reload({
        only: ['clients'],
        onSuccess: () => {
            if (selectedClient.value) {
                selectedClient.value =
                    props.clients.find(
                        (c) => c.id === selectedClient.value.id
                    ) || null
            }
        },
    })
}
</script>

<template>
    <Head title="Clientes | DominioMatic" />

    <div
        class="min-h-screen overflow-x-hidden bg-[#f5f5f7] text-slate-950 transition-colors dark:bg-[#050507] dark:text-white"
    >
        <!-- AMBIENT BACKGROUND -->
        <div
            class="pointer-events-none fixed inset-0 overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="absolute -bottom-56 -left-56 h-[44rem] w-[44rem] rounded-full opacity-[0.14] blur-[130px] dark:opacity-[0.24]"
                style="background: radial-gradient(circle, #002B48 0%, transparent 70%)"
            ></div>

            <div
                class="absolute -right-48 -top-48 h-[42rem] w-[42rem] rounded-full opacity-[0.16] blur-[130px] dark:opacity-[0.26]"
                style="background: radial-gradient(circle, #0072A8 0%, transparent 70%)"
            ></div>

            <div
                class="absolute -left-40 top-[18%] h-[38rem] w-[38rem] rounded-full opacity-[0.12] blur-[120px] dark:opacity-[0.20]"
                style="background: radial-gradient(circle, #21B24B 0%, transparent 70%)"
            ></div>

            <div
                class="absolute -right-40 bottom-[8%] h-[40rem] w-[40rem] rounded-full opacity-[0.10] blur-[130px] dark:opacity-[0.18]"
                style="background: radial-gradient(circle, #005E2B 0%, transparent 70%)"
            ></div>

            <div
                class="absolute left-1/2 top-1/3 h-[36rem] w-[36rem] -translate-x-1/2 rounded-full opacity-[0.55] blur-[140px] dark:opacity-[0.06]"
                style="background: radial-gradient(circle, #FFFFFF 0%, transparent 65%)"
            ></div>
        </div>

        <div
            class="relative mx-auto max-w-[1480px] px-4 pb-20 pt-5 sm:px-6 lg:px-8"
        >
            <!-- HEADER -->
            <header
                class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between"
            >
                <div>
                    <div class="flex items-center gap-3">
                        <h1
                            class="text-3xl font-semibold tracking-[-0.045em] text-slate-950 sm:text-4xl dark:text-white"
                        >
                            Clientes & Directorio
                        </h1>
                    </div>

                    <p
                        class="mt-2 max-w-xl text-sm text-slate-500 dark:text-slate-400"
                    >
                        Gestiona empresas, clientes individuales y
                        todos sus contactos asociados.
                    </p>
                </div>

                <div
                    class="flex flex-wrap items-center gap-2 sm:gap-3"
                >
                    <GlassDropdown align="right" width="w-48">
                        <template #trigger="{ isOpen }">
                            <SecondaryButton>
                                <DocumentArrowDownIcon
                                    class="h-4 w-4 stroke-[2.2]"
                                />
                                <span>Exportar</span>
                                <ChevronDownIcon
                                    class="h-3.5 w-3.5 opacity-60 transition-transform duration-300"
                                    :class="{
                                        'rotate-180': isOpen,
                                    }"
                                />
                            </SecondaryButton>
                        </template>

                        <template #content>
                            <a
                                :href="route('reports.clients.pdf')"
                                target="_blank"
                                class="group flex items-center gap-2.5 rounded-2xl px-3.5 py-2.5 text-xs font-semibold text-slate-700 transition-all duration-200 hover:bg-[#0072A8] hover:text-white dark:text-slate-200"
                            >
                                <DocumentArrowDownIcon
                                    class="h-4 w-4 text-rose-500 transition-colors group-hover:text-white"
                                />
                                <span>Reporte en PDF</span>
                            </a>

                            <a
                                :href="route('reports.clients.excel')"
                                class="group flex items-center gap-2.5 rounded-2xl px-3.5 py-2.5 text-xs font-semibold text-slate-700 transition-all duration-200 hover:bg-[#0072A8] hover:text-white dark:text-slate-200"
                            >
                                <TableCellsIcon
                                    class="h-4 w-4 text-emerald-500 transition-colors group-hover:text-white"
                                />
                                <span>Exportar a Excel</span>
                            </a>
                        </template>
                    </GlassDropdown>

                    <SecondaryButton @click="showImportModal = true">
                        <ArrowUpTrayIcon class="h-4 w-4 stroke-[2.2]" />
                        <span>Importar</span>
                    </SecondaryButton>

                    <PrimaryButton @click="showCreateModal = true">
                        <PlusIcon class="h-4 w-4 stroke-[2.5]" />
                        <span>Nuevo cliente</span>
                    </PrimaryButton>
                </div>
            </header>

            <!-- SEARCH -->
            <div
                class="mb-8 rounded-[26px] border border-white/65 bg-white/35 p-2 shadow-[0_18px_55px_rgba(15,23,42,0.05),inset_0_1px_2px_rgba(255,255,255,0.85),inset_0_-2px_5px_rgba(0,0,0,0.06)] backdrop-blur-xl dark:border-white/[0.08] dark:bg-[#101014]/60 dark:shadow-[0_18px_55px_rgba(0,0,0,0.25),inset_0_1px_2px_rgba(255,255,255,0.16),inset_0_-2px_6px_rgba(0,0,0,0.5)]"
            >
                <div class="relative flex items-center">
                    <MagnifyingGlassIcon
                        class="absolute left-4 h-5 w-5 text-slate-400 dark:text-slate-500"
                    />

                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Buscar por nombre, contacto, sitio web o idioma..."
                        class="w-full rounded-[20px] border-0 bg-white/45 py-3 pl-11 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:bg-white/80 focus:ring-2 focus:ring-[#0072A8] dark:bg-white/[0.04] dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:bg-black/30 dark:focus:ring-[#0072A8]"
                    />
                </div>
            </div>

            <!-- GRID DE CLIENTES -->
            <div
                v-if="filteredClients.length > 0"
                class="grid gap-5 md:grid-cols-2 xl:grid-cols-3"
            >
                <div
                    v-for="client in filteredClients"
                    :key="client.id"
                    class="group relative flex cursor-pointer flex-col justify-between rounded-[24px] border border-white/65 bg-white/35 p-5 shadow-[0_14px_40px_rgba(15,23,42,0.04),inset_0_1px_2px_rgba(255,255,255,0.85),inset_0_-2px_5px_rgba(0,0,0,0.06)] backdrop-blur-xl transition duration-300 hover:-translate-y-1 hover:bg-white/55 hover:shadow-[0_22px_60px_rgba(15,23,42,0.10),inset_0_1px_2px_rgba(255,255,255,0.9),inset_0_-2px_5px_rgba(0,0,0,0.06)] dark:border-white/[0.08] dark:bg-[#101014]/65 dark:shadow-[0_14px_40px_rgba(0,0,0,0.25),inset_0_1px_2px_rgba(255,255,255,0.16),inset_0_-2px_6px_rgba(0,0,0,0.5)] dark:hover:bg-[#101014]/80"
                    @click="openDetailModal(client)"
                >
                    <div class="space-y-3">
                        <!-- Título + Badges -->
                        <div
                            class="flex items-start justify-between gap-3"
                        >
                            <div class="min-w-0 flex-1">
                                <h3
                                    class="truncate text-base font-semibold tracking-[-0.02em] text-slate-950 dark:text-white"
                                >
                                    {{ client.company_name }}
                                </h3>

                                <p
                                    class="mt-1 text-[10px] font-semibold uppercase tracking-[0.14em]"
                                    :class="
                                        client.type === 'company'
                                            ? 'text-[#21B24B] dark:text-[#4ADE80]'
                                            : 'text-slate-500 dark:text-slate-400'
                                    "
                                >
                                    {{
                                        client.type === 'company'
                                            ? 'Empresa'
                                            : 'Cliente Individual'
                                    }}
                                </p>
                            </div>

                            <span
                                class="inline-flex shrink-0 items-center rounded-full border border-white/55 bg-white/40 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500 dark:border-white/[0.08] dark:bg-white/[0.05] dark:text-slate-400"
                            >
                                {{ client.language }}
                            </span>
                        </div>

                        <!-- Meta: web + contactos -->
                        <div
                            class="flex flex-wrap items-center gap-2 pt-1"
                        >
                            <a
                                v-if="client.website"
                                :href="
                                    client.website.startsWith(
                                        'http://'
                                    ) ||
                                    client.website.startsWith(
                                        'https://'
                                    )
                                        ? client.website
                                        : `https://${client.website}`
                                "
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 rounded-full border border-white/55 bg-white/40 px-2.5 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-white/70 hover:text-slate-900 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white"
                                @click.stop
                            >
                                <GlobeAltIcon
                                    class="h-3.5 w-3.5 text-slate-400 dark:text-slate-500"
                                />

                                <span class="max-w-[150px] truncate">
                                    {{
                                        client.website.replace(
                                            /^https?:\/\//,
                                            ''
                                        )
                                    }}
                                </span>
                            </a>

                            <span
                                class="inline-flex items-center gap-1.5 rounded-full border border-[#0072A8]/20 bg-[#0072A8]/10 px-2.5 py-1.5 text-xs font-semibold text-[#0072A8] dark:border-[#0072A8]/30 dark:bg-[#0072A8]/15 dark:text-[#4FC3F7]"
                            >
                                <UserGroupIcon
                                    class="h-3.5 w-3.5"
                                />

                                <span>
                                    {{
                                        client.contacts?.length || 0
                                    }}
                                    {{
                                        client.contacts?.length === 1
                                            ? 'contacto'
                                            : 'contactos'
                                    }}
                                </span>
                            </span>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="mt-5 flex items-center justify-between border-t border-black/[0.055] pt-3 dark:border-white/[0.07]"
                    >
                        <span
                            class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                        >
                            Ver detalles
                            <span
                                class="transition-transform duration-300 group-hover:translate-x-0.5"
                            >
                                →
                            </span>
                        </span>

                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                class="rounded-xl p-2 text-slate-400 transition-all duration-200 hover:bg-[#0072A8]/10 hover:text-[#0072A8] active:scale-90 dark:hover:bg-[#0072A8]/20 dark:hover:text-[#4FC3F7]"
                                title="Editar cliente"
                                @click.stop="
                                    openEditModal(client)
                                "
                            >
                                <PencilSquareIcon
                                    class="h-4 w-4 stroke-[2.2]"
                                />
                            </button>

                            <button
                                type="button"
                                class="rounded-xl p-2 text-slate-400 transition-all duration-200 hover:bg-rose-500/10 hover:text-rose-500 active:scale-90 dark:hover:bg-rose-500/20 dark:hover:text-rose-400"
                                title="Eliminar cliente"
                                @click.stop="
                                    openDeleteModal(client)
                                "
                            >
                                <TrashIcon
                                    class="h-4 w-4 stroke-[2.2]"
                                />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ESTADO VACÍO -->
            <div
                v-else
                class="mt-2 flex flex-col items-center justify-center rounded-[24px] border border-dashed border-slate-200/80 bg-white/30 p-12 text-center backdrop-blur-xl dark:border-white/[0.07] dark:bg-white/[0.02]"
            >
                <p
                    class="text-sm text-slate-500 dark:text-slate-400"
                >
                    No se encontraron clientes que coincidan con
                    "<span
                        class="font-semibold text-slate-800 dark:text-slate-200"
                        >{{ searchQuery }}</span
                    >".
                </p>
            </div>
        </div>
    </div>

    <!-- MODALES -->
    <CreateClientModal
        :show="showCreateModal"
        @close="showCreateModal = false"
    />

    <EditClientModal
        v-if="selectedClient"
        :key="selectedClient.id"
        :show="showEditModal"
        :client="selectedClient"
        @close="closeEditModal"
        @success="handleRefresh"
    />

    <DeleteClientModal
        :show="showDeleteModal"
        :client="selectedClient"
        @close="closeDeleteModal"
        @success="handleRefresh"
    />

    <ImportClientsModal
        :show="showImportModal"
        @close="showImportModal = false"
        @success="handleRefresh"
    />

    <ClientDetailModal
        :show="showDetailModal"
        :client="selectedClient"
        @close="showDetailModal = false"
        @refresh="handleRefresh"
    />
</template>