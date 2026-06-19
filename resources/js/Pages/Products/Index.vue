<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
});

const search = ref('');
const showModal = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    description: '',
    price_list: '',
    is_infinite: true,
    stock: 0,
});

const filteredProducts = computed(() => {
    const q = search.value.trim().toLowerCase();

    if (!q) return props.products;

    return props.products.filter((product) => {
        return [
            product.name,
            product.description,
            product.is_infinite ? 'ilimitado' : `stock ${product.stock}`,
        ]
            .filter(Boolean)
            .some((value) => String(value).toLowerCase().includes(q));
    });
});

const stats = computed(() => {
    const total = props.products.length;
    const infinite = props.products.filter((p) => Boolean(p.is_infinite)).length;
    const finite = total - infinite;

    return { total, infinite, finite };
});

function openCreateModal() {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    form.is_infinite = true;
    form.stock = 0;
    showModal.value = true;
}

function openEditModal(product) {
    editingId.value = product.id;
    form.name = product.name ?? '';
    form.description = product.description ?? '';
    form.price_list = product.price_list ?? '';
    form.is_infinite = Boolean(product.is_infinite);
    form.stock = product.stock ?? 0;
    form.clearErrors();
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    form.is_infinite = true;
    form.stock = 0;
}

function submit() {
    const payload = {
        name: form.name,
        description: form.description,
        price_list: form.price_list,
        is_infinite: form.is_infinite,
        stock: form.is_infinite ? 0 : form.stock,
    };

    if (editingId.value) {
        form.transform(() => payload).put(route('products.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
        return;
    }

    form.transform(() => payload).post(route('products.store'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
}

function removeProduct(id) {
    if (!confirm('¿Eliminar este producto?')) return;

    router.delete(route('products.destroy', id), {
        preserveScroll: true,
    });
}

function formatMoney(value) {
    const number = Number(value || 0);
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 2,
    }).format(number);
}
</script>

<template>
    <Head title="Productos | DominioMatic" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        Productos
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Servicios, dominios y recursos que administra tu empresa.
                    </p>
                </div>

                <div class="flex gap-3">
                    <Link
                        :href="route('dashboard')"
                        class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-white/10 dark:bg-[#111113] dark:text-gray-200 dark:hover:bg-[#1c1c1f]"
                    >
                        Volver al dashboard
                    </Link>

                    <button
                        type="button"
                        @click="openCreateModal"
                        class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-slate-800 dark:bg-white dark:text-black"
                    >
                        Nuevo producto
                    </button>
                </div>
            </div>
        </template>

        <div class="py-10 text-gray-900 transition-colors duration-300 dark:text-gray-100">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="glass-card p-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total productos</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
                    </div>

                    <div class="glass-card p-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Servicios ilimitados</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.infinite }}</p>
                    </div>

                    <div class="glass-card p-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Productos con stock</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.finite }}</p>
                    </div>
                </div>

                <div class="mt-8 glass-card p-6">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Listado de productos</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Registros reales de tus productos.</p>
                        </div>

                        <div class="w-full md:w-80">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Buscar producto..."
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-gray-100 dark:placeholder:text-gray-500"
                            />
                        </div>
                    </div>

                    <div class="mt-6 overflow-hidden rounded-2xl border border-gray-100 dark:border-white/10">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 dark:divide-white/10">
                                <thead class="bg-gray-50 dark:bg-[#1c1c1f]">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                            Nombre
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                            Precio
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                            Inventario
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                            Descripción
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100 bg-white dark:divide-white/10 dark:bg-transparent">
                                    <tr
                                        v-for="product in filteredProducts"
                                        :key="product.id"
                                        class="hover:bg-gray-50/80 dark:hover:bg-[#1c1c1f] transition-colors"
                                    >
                                        <td class="px-4 py-4">
                                            <div class="font-medium text-gray-900 dark:text-white">
                                                {{ product.name }}
                                            </div>
                                            <div class="text-xs text-gray-400 dark:text-gray-500">
                                                ID {{ product.id }}
                                            </div>
                                        </td>

                                        <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                            {{ formatMoney(product.price_list) }}
                                        </td>

                                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">
                                            <span
                                                v-if="product.is_infinite"
                                                class="inline-flex rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold text-cyan-700 dark:bg-cyan-500/10 dark:text-cyan-300"
                                            >
                                                Ilimitado
                                            </span>
                                            <span
                                                v-else
                                                class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 dark:bg-white/10 dark:text-gray-200"
                                            >
                                                Stock: {{ product.stock }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ product.description || '—' }}
                                        </td>

                                        <td class="px-4 py-4">
                                            <div class="flex gap-2">
                                                <button
                                                    type="button"
                                                    @click="openEditModal(product)"
                                                    class="rounded-2xl border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-[#1c1c1f]"
                                                >
                                                    Editar
                                                </button>

                                                <button
                                                    type="button"
                                                    @click="removeProduct(product.id)"
                                                    class="rounded-2xl border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/20 dark:text-rose-300 dark:hover:bg-rose-500/10"
                                                >
                                                    Eliminar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr v-if="filteredProducts.length === 0">
                                        <td colspan="5" class="px-4 py-14 text-center text-sm text-gray-500 dark:text-gray-400">
                                            No hay productos registrados.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
        >
            <div class="glass-card w-full max-w-2xl p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ editingId ? 'Editar producto' : 'Nuevo producto' }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Registra servicios y recursos administrados por DominioMatic.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="text-gray-400 transition hover:text-gray-700 dark:hover:text-gray-200"
                        @click="closeModal"
                    >
                        ✕
                    </button>
                </div>

                <form class="mt-6 grid gap-4 md:grid-cols-2" @submit.prevent="submit">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                        <textarea
                            v-model="form.description"
                            rows="4"
                            class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white"
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Precio de lista</label>
                        <input
                            v-model="form.price_list"
                            type="number"
                            step="0.01"
                            min="0"
                            class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white"
                        />
                        <p v-if="form.errors.price_list" class="mt-1 text-sm text-red-600">
                            {{ form.errors.price_list }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Inventario</label>
                        <select
                            v-model="form.is_infinite"
                            class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white"
                        >
                            <option :value="true">Ilimitado</option>
                            <option :value="false">Con stock</option>
                        </select>
                        <p v-if="form.errors.is_infinite" class="mt-1 text-sm text-red-600">
                            {{ form.errors.is_infinite }}
                        </p>
                    </div>

                    <div v-if="!form.is_infinite">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Stock</label>
                        <input
                            v-model="form.stock"
                            type="number"
                            min="0"
                            class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white"
                        />
                        <p v-if="form.errors.stock" class="mt-1 text-sm text-red-600">
                            {{ form.errors.stock }}
                        </p>
                    </div>

                    <div class="md:col-span-2 mt-2 flex justify-end gap-3">
                        <button
                            type="button"
                            class="rounded-2xl border border-gray-200 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-[#1c1c1f]"
                            @click="closeModal"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white dark:text-black"
                        >
                            Guardar producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>