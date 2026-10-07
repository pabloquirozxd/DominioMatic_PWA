<!-- resources/js/Pages/Subscriptions/Components/SubscriptionTable.vue -->
<script setup>
import { PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline'

defineProps({
    subscriptions: {
        type: Array,
        required: true,
    },
})

const emit = defineEmits(['edit', 'delete'])

const statusMap = {
    active: {
        label: 'Activa',
        class:
            'border-[#21B24B]/20 bg-[#21B24B]/10 text-[#21B24B] dark:border-[#21B24B]/30 dark:bg-[#21B24B]/15 dark:text-[#4ADE80]',
        dot: 'bg-[#21B24B]',
    },
    expired: {
        label: 'Vencida',
        class:
            'border-rose-500/20 bg-rose-500/10 text-rose-600 dark:border-rose-400/30 dark:bg-rose-400/15 dark:text-rose-300',
        dot: 'bg-rose-500',
    },
    suspended: {
        label: 'Suspendida',
        class:
            'border-slate-500/20 bg-slate-500/10 text-slate-600 dark:border-white/[0.08] dark:bg-white/[0.05] dark:text-slate-300',
        dot: 'bg-slate-400 dark:bg-slate-500',
    },
}

function formatMoney(amount, currency) {
    const num = Number(amount || 0)
    const isBob =
        String(currency || '').trim().toUpperCase() === 'BOB'

    const formatted = num.toLocaleString(isBob ? 'es-BO' : 'en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })

    return isBob ? `Bs. ${formatted}` : `$${formatted}`
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
                        Cliente
                    </th>
                    <th
                        scope="col"
                        class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                    >
                        Producto
                    </th>
                    <th
                        scope="col"
                        class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                    >
                        Neto Total
                    </th>
                    <th
                        scope="col"
                        class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                    >
                        Estado
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
                    v-for="sub in subscriptions"
                    :key="sub.id"
                    class="group transition-colors hover:bg-black/[0.02] dark:hover:bg-white/[0.03]"
                >
                    <td class="px-5 py-4">
                        <span
                            class="text-sm font-semibold text-slate-950 dark:text-white"
                        >
                            {{ sub.client_name || sub.contact_name || 'Sin Empresa' }}
                        </span>
                    </td>

                    <td class="px-5 py-4">
                        <span
                            class="text-sm text-slate-600 dark:text-slate-300"
                        >
                            {{ sub.product_name }}
                        </span>
                    </td>

                    <td class="px-5 py-4">
                        <span
                            class="text-sm font-semibold text-slate-950 dark:text-white"
                        >
                            {{
                                formatMoney(
                                    sub.total_neto ?? sub.price_list,
                                    sub.currency
                                )
                            }}
                        </span>
                    </td>

                    <td class="px-5 py-4 whitespace-nowrap">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-semibold"
                            :class="statusMap[sub.status]?.class"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full"
                                :class="statusMap[sub.status]?.dot"
                            ></span>

                            {{
                                statusMap[sub.status]?.label || sub.status
                            }}
                        </span>
                    </td>

                    <td class="px-5 py-4 text-right whitespace-nowrap">
                        <div
                            class="flex items-center justify-end gap-1 opacity-0 transition-opacity duration-200 group-hover:opacity-100"
                        >
                            <button
                                type="button"
                                class="flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 transition hover:bg-[#0072A8]/10 hover:text-[#0072A8] dark:hover:bg-[#0072A8]/20 dark:hover:text-[#4FC3F7]"
                                title="Editar"
                                @click="emit('edit', sub)"
                            >
                                <PencilSquareIcon
                                    class="h-4 w-4 stroke-[2]"
                                />
                            </button>

                            <button
                                type="button"
                                class="flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 transition hover:bg-rose-500/10 hover:text-rose-500 dark:hover:bg-rose-500/20 dark:hover:text-rose-400"
                                title="Eliminar"
                                @click="emit('delete', sub.id)"
                            >
                                <TrashIcon class="h-4 w-4 stroke-[2]" />
                            </button>
                        </div>
                    </td>
                </tr>

                <!-- Empty -->
                <tr v-if="subscriptions.length === 0">
                    <td
                        colspan="5"
                        class="px-6 py-12 text-center text-sm text-slate-400 dark:text-slate-500"
                    >
                        No se encontraron suscripciones registradas.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>