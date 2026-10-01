<!-- resources/js/Pages/Contacts/Partials/ContactTable.vue -->
<script setup>
import {
    PencilSquareIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline'

defineProps({
    contacts: {
        type: Array,
        required: true,
    },
})

const emit = defineEmits(['edit', 'delete'])

function getCompanies(contact) {
    if (!contact.clients || contact.clients.length === 0) return []
    return contact.clients
}

function getCompanyName(client) {
    return (
        client.name ||
        client.company_name ||
        client.business_name ||
        'Sin Nombre'
    )
}

function getPosition(contact) {
    if (contact.position) return contact.position

    const companyPosition = contact.clients?.find(
        (c) => c.pivot?.position
    )?.pivot?.position

    if (companyPosition) return companyPosition

    return contact.pivot?.position || null
}

function getInitials(contact) {
    const first = contact.first_name
        ? contact.first_name.charAt(0).toUpperCase()
        : ''
    const last = contact.last_name
        ? contact.last_name.charAt(0).toUpperCase()
        : ''

    return `${first}${last}` || '?'
}

function isPrimary(client) {
    return (
        client?.pivot?.is_primary ||
        client?.pivot?.type === 'primary'
    )
}
</script>

<template>
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left">
            <thead>
                <tr
                    class="border-b border-black/[0.055] dark:border-white/[0.07]"
                >
                    <th
                        scope="col"
                        class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                    >
                        Contacto / Cargo
                    </th>
                    <th
                        scope="col"
                        class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                    >
                        Datos de Contacto
                    </th>
                    <th
                        scope="col"
                        class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                    >
                        Empresas / Afiliación
                    </th>
                    <th
                        scope="col"
                        class="px-5 py-3.5 text-right text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                    >
                        Acciones
                    </th>
                </tr>
            </thead>

            <tbody
                class="divide-y divide-black/[0.045] text-sm dark:divide-white/[0.06]"
            >
                <tr
                    v-for="contact in contacts"
                    :key="contact.id"
                    class="group transition-colors hover:bg-black/[0.02] dark:hover:bg-white/[0.03]"
                >
                    <!-- Contacto / Cargo -->
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-500/10 text-[11px] font-semibold text-slate-600 dark:bg-white/[0.06] dark:text-slate-300"
                            >
                                {{ getInitials(contact) }}
                            </div>

                            <div class="flex min-w-0 flex-col">
                                <span
                                    class="truncate text-sm font-semibold text-slate-950 dark:text-white"
                                >
                                    {{ contact.first_name }}
                                    {{ contact.last_name }}
                                </span>

                                <p
                                    class="truncate text-xs text-slate-400 dark:text-slate-500"
                                >
                                    {{
                                        getPosition(contact) ||
                                        'Sin cargo asignado'
                                    }}
                                </p>
                            </div>
                        </div>
                    </td>

                    <!-- Datos de contacto -->
                    <td class="px-5 py-4">
                        <div class="flex min-w-0 flex-col gap-0.5">
                            <a
                                v-if="contact.email"
                                :href="`mailto:${contact.email}`"
                                class="truncate text-xs font-medium text-slate-700 transition hover:text-[#0072A8] dark:text-slate-300 dark:hover:text-[#4FC3F7]"
                            >
                                {{ contact.email }}
                            </a>

                            <span
                                v-else
                                class="text-xs text-slate-400 dark:text-slate-500"
                            >
                                —
                            </span>

                            <span
                                class="text-[11px] font-medium text-slate-400 dark:text-slate-500"
                            >
                                {{ contact.phone || 'Sin teléfono' }}
                            </span>
                        </div>
                    </td>

                    <!-- Empresas / Afiliación -->
                    <td class="px-5 py-4">
                        <div
                            v-if="getCompanies(contact).length > 0"
                            class="flex items-center gap-1.5"
                        >
                            <!-- Primera empresa visible -->
                            <div
                                class="inline-flex min-w-0 max-w-[200px] items-center gap-1.5 rounded-full border border-white/55 bg-white/40 px-2.5 py-1 text-[11px] font-semibold text-slate-700 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-slate-300"
                            >
                                <span class="truncate">
                                    {{
                                        getCompanyName(
                                            getCompanies(contact)[0]
                                        )
                                    }}
                                </span>

                                <span
                                    v-if="
                                        isPrimary(
                                            getCompanies(contact)[0]
                                        )
                                    "
                                    class="shrink-0 rounded-full border border-[#0072A8]/20 bg-[#0072A8]/10 px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-[0.1em] text-[#0072A8] dark:border-[#0072A8]/30 dark:bg-[#0072A8]/15 dark:text-[#4FC3F7]"
                                >
                                    Principal
                                </span>
                            </div>

                            <!-- Contador +N con tooltip -->
                            <div
                                v-if="getCompanies(contact).length > 1"
                                class="group/tooltip relative shrink-0"
                            >
                                <span
                                    class="inline-flex cursor-help items-center rounded-full border border-white/55 bg-white/40 px-2.5 py-1 text-[11px] font-semibold text-slate-600 transition hover:bg-white/70 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-slate-300 dark:hover:bg-white/[0.08]"
                                >
                                    +{{
                                        getCompanies(contact).length - 1
                                    }}
                                </span>

                                <!-- Tooltip flotante -->
                                <div
                                    class="absolute bottom-full left-1/2 z-30 mb-2 hidden -translate-x-1/2 rounded-2xl border border-white/65 bg-white/90 p-3 text-xs shadow-[0_18px_50px_rgba(15,23,42,0.15)] backdrop-blur-xl group-hover/tooltip:block dark:border-white/[0.08] dark:bg-[#101014]/95 dark:shadow-[0_18px_50px_rgba(0,0,0,0.5)]"
                                >
                                    <p
                                        class="mb-1.5 border-b border-black/[0.055] pb-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400 dark:border-white/[0.07] dark:text-slate-500"
                                    >
                                        Otras empresas
                                    </p>

                                    <ul
                                        class="min-w-[180px] space-y-1.5 whitespace-nowrap"
                                    >
                                        <li
                                            v-for="otherClient in getCompanies(
                                                contact
                                            ).slice(1)"
                                            :key="otherClient.id"
                                            class="flex items-center justify-between gap-3 text-slate-700 dark:text-slate-200"
                                        >
                                            <span class="font-medium">
                                                {{
                                                    getCompanyName(
                                                        otherClient
                                                    )
                                                }}
                                            </span>

                                            <span
                                                v-if="
                                                    isPrimary(otherClient)
                                                "
                                                class="text-[9px] font-semibold uppercase tracking-[0.1em] text-[#0072A8] dark:text-[#4FC3F7]"
                                            >
                                                Principal
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <span
                            v-else
                            class="inline-flex items-center rounded-full border border-dashed border-slate-300/80 px-2.5 py-1 text-[11px] font-medium text-slate-400 dark:border-white/[0.1] dark:text-slate-500"
                        >
                            Independiente
                        </span>
                    </td>

                    <!-- Acciones -->
                    <td
                        class="whitespace-nowrap px-5 py-4 text-right"
                        @click.stop
                    >
                        <div
                            class="flex items-center justify-end gap-1 opacity-0 transition-opacity duration-200 group-hover:opacity-100"
                        >
                            <button
                                type="button"
                                class="flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 transition hover:bg-black/[0.05] hover:text-slate-900 dark:hover:bg-white/[0.08] dark:hover:text-white"
                                title="Editar contacto"
                                @click="emit('edit', contact)"
                            >
                                <PencilSquareIcon
                                    class="h-4 w-4 stroke-[2]"
                                />
                            </button>

                            <button
                                type="button"
                                class="flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 transition hover:bg-rose-500/10 hover:text-rose-500 dark:hover:bg-rose-500/20 dark:hover:text-rose-400"
                                title="Eliminar contacto"
                                @click="emit('delete', contact)"
                            >
                                <TrashIcon
                                    class="h-4 w-4 stroke-[2]"
                                />
                            </button>
                        </div>
                    </td>
                </tr>

                <!-- Empty -->
                <tr v-if="contacts.length === 0">
                    <td
                        colspan="4"
                        class="px-6 py-12 text-center text-sm text-slate-400 dark:text-slate-500"
                    >
                        No se encontraron contactos registrados.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>