<!-- resources/js/Pages/Products/Components/ProductTable.vue -->
<script setup>
import {
    PencilSquareIcon,
    TrashIcon,
    SparklesIcon,
    ArchiveBoxIcon,
    InformationCircleIcon,
} from '@heroicons/vue/24/outline'

defineProps({
    products: {
        type: Array,
        required: true,
    },
})

const emit = defineEmits(['edit', 'delete', 'view-detail'])

function isService(item) {
    if (item.type) return item.type === 'service'
    return Boolean(item.is_infinite)
}

function formatMoney(value, currency) {
    const number = Number(value || 0)
    const rawCurrency = String(currency || '').trim().toUpperCase()

    if (
        rawCurrency === 'BOB' ||
        rawCurrency === 'BS' ||
        rawCurrency === 'BOLIVIANOS'
    ) {
        return `Bs. ${number.toLocaleString('es-BO', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`
    }

    return `$${number.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`
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
                        Ítem / Descripción
                    </th>
                    <th
                        scope="col"
                        class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                    >
                        Clasificación
                    </th>
                    <th
                        scope="col"
                        class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                    >
                        Disponibilidad
                    </th>
                    <th
                        scope="col"
                        class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                    >
                        Precio Lista
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
                    v-for="product in products"
                    :key="product.id"
                    class="group cursor-pointer transition-colors hover:bg-black/[0.02] dark:hover:bg-white/[0.03]"
                    @click="emit('view-detail', product)"
                >
                    <!-- Ítem y descripción -->
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
                                :class="
                                    isService(product)
                                        ? 'bg-[#0072A8]/10 text-[#0072A8] dark:bg-[#0072A8]/20 dark:text-[#4FC3F7]'
                                        : 'bg-slate-500/10 text-slate-500 dark:bg-white/[0.06] dark:text-slate-400'
                                "
                            >
                                <component
                                    :is="
                                        isService(product)
                                            ? SparklesIcon
                                            : ArchiveBoxIcon
                                    "
                                    class="h-[18px] w-[18px] stroke-[1.8]"
                                />
                            </div>

                            <div class="flex min-w-0 flex-col">
                                <span
                                    class="truncate text-sm font-semibold text-slate-950 dark:text-white"
                                >
                                    {{ product.name }}
                                </span>

                                <p
                                    class="line-clamp-1 text-xs text-slate-400 dark:text-slate-500"
                                >
                                    {{
                                        product.description
                                            ? product.description.replace(
                                                  /<[^>]*>?/gm,
                                                  ''
                                              )
                                            : 'Sin descripción'
                                    }}
                                </p>
                            </div>
                        </div>
                    </td>

                    <!-- Clasificación -->
                    <td class="whitespace-nowrap px-5 py-4">
                        <span
                            v-if="isService(product)"
                            class="inline-flex items-center gap-1.5 rounded-full border border-[#21B24B]/20 bg-[#21B24B]/10 px-2.5 py-1 text-[11px] font-semibold text-[#21B24B] dark:border-[#21B24B]/30 dark:bg-[#21B24B]/15 dark:text-[#4ADE80]"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full bg-[#21B24B]"
                            ></span>
                            Servicio Digital
                        </span>

                        <span
                            v-else
                            class="inline-flex items-center gap-1.5 rounded-full border border-slate-500/20 bg-slate-500/10 px-2.5 py-1 text-[11px] font-semibold text-slate-600 dark:border-white/[0.08] dark:bg-white/[0.05] dark:text-slate-300"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full bg-slate-400 dark:bg-slate-500"
                            ></span>
                            Producto Físico
                        </span>
                    </td>

                    <!-- Disponibilidad -->
                    <td class="whitespace-nowrap px-5 py-4">
                        <span
                            v-if="isService(product)"
                            class="text-xs font-medium text-slate-400 dark:text-slate-500"
                        >
                            Acceso ilimitado
                        </span>

                        <span
                            v-else-if="product.is_infinite"
                            class="inline-flex items-center gap-1 rounded-full border border-white/55 bg-white/40 px-2.5 py-1 text-[11px] font-semibold text-slate-600 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-slate-300"
                        >
                            Stock ilimitado
                        </span>

                        <div v-else class="flex items-baseline gap-1">
                            <span
                                class="text-sm font-semibold text-slate-950 dark:text-white"
                            >
                                {{ product.stock }}
                            </span>

                            <span
                                class="text-[11px] font-medium text-slate-400 dark:text-slate-500"
                            >
                                unidades
                            </span>
                        </div>
                    </td>

                    <!-- Precio -->
                    <td class="whitespace-nowrap px-5 py-4">
                        <span
                            class="text-sm font-semibold text-slate-950 dark:text-white"
                        >
                            {{
                                formatMoney(
                                    product.price_list,
                                    product.currency
                                )
                            }}
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
                                class="flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 transition hover:bg-[#0072A8]/10 hover:text-[#0072A8] dark:hover:bg-[#0072A8]/20 dark:hover:text-[#4FC3F7]"
                                title="Ver detalles"
                                @click="emit('view-detail', product)"
                            >
                                <InformationCircleIcon
                                    class="h-4 w-4 stroke-[2]"
                                />
                            </button>

                            <button
                                type="button"
                                class="flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 transition hover:bg-black/[0.05] hover:text-slate-900 dark:hover:bg-white/[0.08] dark:hover:text-white"
                                title="Editar"
                                @click="emit('edit', product)"
                            >
                                <PencilSquareIcon
                                    class="h-4 w-4 stroke-[2]"
                                />
                            </button>

                            <button
                                type="button"
                                class="flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 transition hover:bg-rose-500/10 hover:text-rose-500 dark:hover:bg-rose-500/20 dark:hover:text-rose-400"
                                title="Eliminar"
                                @click="emit('delete', product.id)"
                            >
                                <TrashIcon class="h-4 w-4 stroke-[2]" />
                            </button>
                        </div>
                    </td>
                </tr>

                <!-- Empty -->
                <tr v-if="products.length === 0">
                    <td
                        colspan="5"
                        class="px-6 py-12 text-center text-sm text-slate-400 dark:text-slate-500"
                    >
                        No se encontraron ítems en el catálogo.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>