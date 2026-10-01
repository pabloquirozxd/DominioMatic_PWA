<!-- resources/js/Pages/Clients/Components/ClientStats.vue -->
<script setup>
import { computed } from 'vue'
import {
    BuildingOffice2Icon,
    UserIcon,
    UserGroupIcon,
    GlobeAltIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    clients: {
        type: Array,
        default: () => [],
    },
})

// Métricas dinámicas calculadas en tiempo real
const totalClients = computed(() => props.clients.length)

const totalCompanies = computed(() => 
    props.clients.filter(c => c.type === 'company').length
)

const totalIndividuals = computed(() => 
    props.clients.filter(c => c.type === 'individual' || c.type === 'person').length
)

const totalContacts = computed(() => 
    props.clients.reduce((acc, c) => acc + (c.contacts?.length || 0), 0)
)
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- KPI 1: Total Clientes -->
        <div class="glass-card flex items-center gap-4 p-4 transition hover:-translate-y-0.5 hover:shadow-lg">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#007AFF]/10 text-[#007AFF]">
                <GlobeAltIcon class="h-6 w-6 stroke-[2]" />
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Total Clientes
                </p>
                <h4 class="mt-0.5 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    {{ totalClients }}
                </h4>
            </div>
        </div>

        <!-- KPI 2: Empresas -->
        <div class="glass-card flex items-center gap-4 p-4 transition hover:-translate-y-0.5 hover:shadow-lg">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-purple-500/10 text-purple-600 dark:text-purple-400">
                <BuildingOffice2Icon class="h-6 w-6 stroke-[2]" />
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Empresas
                </p>
                <h4 class="mt-0.5 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    {{ totalCompanies }}
                </h4>
            </div>
        </div>

        <!-- KPI 3: Particulares -->
        <div class="glass-card flex items-center gap-4 p-4 transition hover:-translate-y-0.5 hover:shadow-lg">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                <UserIcon class="h-6 w-6 stroke-[2]" />
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Particulares
                </p>
                <h4 class="mt-0.5 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    {{ totalIndividuals }}
                </h4>
            </div>
        </div>

        <!-- KPI 4: Total Contactos Directos -->
        <div class="glass-card flex items-center gap-4 p-4 transition hover:-translate-y-0.5 hover:shadow-lg">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                <UserGroupIcon class="h-6 w-6 stroke-[2]" />
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Contactos Directos
                </p>
                <h4 class="mt-0.5 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    {{ totalContacts }}
                </h4>
            </div>
        </div>
    </div>
</template>