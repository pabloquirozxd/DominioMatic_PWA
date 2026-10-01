<!-- resources/js/Pages/Products/Index.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/UI/Buttons/PrimaryButton.vue'
import SecondaryButton from '@/Components/UI/Buttons/SecondaryButton.vue'
import GlassDropdown from '@/Components/UI/GlassDropdown.vue'

import ProductStats from './Components/ProductStats.vue'
import ProductTable from './Components/ProductTable.vue'
import ProductFormModal from './Components/ProductFormModal.vue'
import ProductImportModal from './Components/ProductImportModal.vue'
import ProductDetailModal from './Components/ProductDetailModal.vue'

import { Head, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

import {
    PlusIcon,
    MagnifyingGlassIcon,
    DocumentArrowDownIcon,
    TableCellsIcon,
    ArrowUpTrayIcon,
    ChevronDownIcon,
} from '@heroicons/vue/24/outline'

defineOptions({
    layout: AuthenticatedLayout,
});

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
})

const search = ref('')
const showModal = ref(false)
const showImportModal = ref(false)
const showDetailModal = ref(false)
const editingProduct = ref(null)
const selectedProduct = ref(null)

function isService(item) {
    if (item.type) return item.type === 'service'
    return Boolean(item.is_infinite)
}

const filteredProducts = computed(() => {
    const q = search.value.trim().toLowerCase()
    if (!q) return props.products

    return props.products.filter((product) => {
        const itemType = isService(product) ? 'servicio' : 'producto'
        const stockText = isService(product)
            ? 'servicio'
            : product.is_infinite
              ? 'stock ilimitado'
              : `stock ${product.stock}`

        return [
            product.name,
            product.description,
            itemType,
            stockText,
            product.price_list,
            product.currency,
        ]
            .filter(Boolean)
            .some((val) => String(val).toLowerCase().includes(q))
    })
})

const stats = computed(() => {
    const total = props.products.length
    const services = props.products.filter((p) => isService(p)).length
    const products = total - services

    return { total, services, products }
})

function openCreateModal() {
    editingProduct.value = null
    showModal.value = true
}

function openEditModal(product) {
    editingProduct.value = product
    showModal.value = true
}

function openDetailModal(product) {
    selectedProduct.value = product
    showDetailModal.value = true
}

function closeDetailModal() {
    showDetailModal.value = false
    selectedProduct.value = null
}

function removeProduct(id) {
    if (!confirm('¿Desea eliminar este registro del catálogo?')) return

    router.delete(route('products.destroy', id), {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="Catálogo de Productos & Servicios | DominioMatic" />

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
                            Catálogo & Inventario
                        </h1>
                    </div>

                    <p
                        class="mt-2 max-w-xl text-sm text-slate-500 dark:text-slate-400"
                    >
                        Gestión centralizada de servicios digitales e
                        inventario de productos físicos.
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
                                :href="route('reports.products.pdf')"
                                target="_blank"
                                class="group flex items-center gap-2.5 rounded-2xl px-3.5 py-2.5 text-xs font-semibold text-slate-700 transition-all duration-200 hover:bg-[#0072A8] hover:text-white dark:text-slate-200"
                            >
                                <DocumentArrowDownIcon
                                    class="h-4 w-4 text-rose-500 transition-colors group-hover:text-white"
                                />
                                <span>Reporte en PDF</span>
                            </a>

                            <a
                                :href="route('reports.products.excel')"
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
                        <ArrowUpTrayIcon
                            class="h-4 w-4 stroke-[2.2]"
                        />
                        <span>Importar</span>
                    </SecondaryButton>

                    <PrimaryButton @click="openCreateModal">
                        <PlusIcon class="h-4 w-4 stroke-[2.5]" />
                        <span>Nuevo ítem</span>
                    </PrimaryButton>
                </div>
            </header>

            <!-- STATS -->
            <ProductStats :stats="stats" class="mb-8" />

            <!-- SEARCH + TABLE (single container, matching Contacts) -->
            <div
                class="rounded-[28px] border border-white/65 bg-white/35 p-2 shadow-[0_22px_70px_rgba(15,23,42,0.06),inset_0_1px_2px_rgba(255,255,255,0.85),inset_0_-2px_5px_rgba(0,0,0,0.06)] backdrop-blur-xl dark:border-white/[0.08] dark:bg-[#101014]/65 dark:shadow-[0_22px_70px_rgba(0,0,0,0.28),inset_0_1px_2px_rgba(255,255,255,0.16),inset_0_-2px_6px_rgba(0,0,0,0.5)] sm:p-3"
            >
                <!-- Search field inside the container -->
                <div
                    class="mb-3 rounded-[20px] border border-white/60 bg-white/45 p-1.5 dark:border-white/[0.08] dark:bg-white/[0.03]"
                >
                    <div class="relative flex items-center">
                        <MagnifyingGlassIcon
                            class="absolute left-4 h-4 w-4 text-slate-400 dark:text-slate-500"
                        />

                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar por nombre, tipo, descripción..."
                            class="w-full rounded-[16px] border-0 bg-transparent py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:bg-white/50 focus:ring-2 focus:ring-[#0072A8] dark:bg-transparent dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:bg-black/20 dark:focus:ring-[#0072A8]"
                        />
                    </div>
                </div>

                <!-- Table -->
                <ProductTable
                    :products="filteredProducts"
                    @edit="openEditModal"
                    @delete="removeProduct"
                    @view-detail="openDetailModal"
                />
            </div>
        </div>
    </div>

    <!-- MODALES -->
    <ProductFormModal
        :show="showModal"
        :editing-product="editingProduct"
        @close="showModal = false"
    />

    <ProductImportModal
        :show="showImportModal"
        @close="showImportModal = false"
    />

    <ProductDetailModal
        :show="showDetailModal"
        :product="selectedProduct"
        @close="closeDetailModal"
    />
</template>