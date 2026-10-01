<!-- resources/js/Pages/Products/Components/ProductFormModal.vue -->
<script setup>
import PrimaryButton from '@/Components/UI/Buttons/PrimaryButton.vue'
import SecondaryButton from '@/Components/UI/Buttons/SecondaryButton.vue'
import SegmentedControl from '@/Components/UI/Navigation/SegmentedControl.vue'
import RichTextEditor from '@/Components/UI/RichTextEditor.vue' // <-- IMPORTACIÓN AGREGADA
import { XMarkIcon, SparklesIcon, ArchiveBoxIcon } from '@heroicons/vue/24/outline'
import { useForm } from '@inertiajs/vue3'
import { computed, watch } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    editingProduct: {
        type: Object,
        default: null,
    },
})

const emit = defineEmits(['close'])

const itemTypeOptions = [
    { label: 'Servicio', value: 'service', icon: SparklesIcon },
    { label: 'Producto', value: 'product', icon: ArchiveBoxIcon },
]

const form = useForm({
    type: 'service',
    name: '',
    description: '',
    price_list: '',
    currency: 'USD',
    is_infinite: true,
    stock: 0,
})

// Cargar o resetear datos cuando abre el modal o se edita un objeto
watch(
    () => props.show,
    (isOpen) => {
        if (isOpen) {
            form.clearErrors()
            if (props.editingProduct) {
                const isServ = props.editingProduct.type === 'service' || Boolean(props.editingProduct.is_infinite)
                form.type = props.editingProduct.type || (isServ ? 'service' : 'product')
                form.name = props.editingProduct.name ?? ''
                form.description = props.editingProduct.description ?? ''
                form.price_list = props.editingProduct.price_list ?? ''
                form.currency = props.editingProduct.currency ?? 'USD'
                form.is_infinite = Boolean(props.editingProduct.is_infinite)
                form.stock = props.editingProduct.stock ?? 0
            } else {
                form.reset()
                form.type = 'service'
                form.currency = 'USD'
                form.is_infinite = true
                form.stock = 0
            }
        }
    }
)

// Ajustar stock automáticamente si cambia el tipo
watch(() => form.type, (newType) => {
    if (newType === 'service') {
        form.is_infinite = true
        form.stock = 0
    }
})

const isEditing = computed(() => Boolean(props.editingProduct?.id))

const modalTitle = computed(() => {
    if (isEditing.value) {
        return form.type === 'service' ? 'Editar Servicio' : 'Editar Producto'
    }
    return form.type === 'service' ? 'Nuevo Servicio' : 'Nuevo Producto'
})

const submitButtonLabel = computed(() => {
    if (isEditing.value) {
        return form.type === 'service' ? 'Guardar Servicio' : 'Guardar Producto'
    }
    return form.type === 'service' ? 'Crear Servicio' : 'Crear Producto'
})

const currencySymbol = computed(() => (form.currency === 'BOB' ? 'Bs.' : '$'))

function submit() {
    const isServ = form.type === 'service'
    
    form.is_infinite = isServ ? true : form.is_infinite
    form.stock = isServ ? 0 : (form.is_infinite ? 0 : form.stock)

    if (isEditing.value) {
        form.put(route('products.update', props.editingProduct.id), {
            preserveScroll: true,
            onSuccess: () => emit('close'),
        })
        return
    }

    form.post(route('products.store'), {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    })
}
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-md transition-all"
    >
        <div class="w-full max-w-xl rounded-3xl bg-white/95 dark:bg-[#1c1c1e]/95 p-6 shadow-2xl border border-white/60 dark:border-white/10 backdrop-blur-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ modalTitle }}
                    </h3>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        {{ form.type === 'service' ? 'Ingrese la información técnica y valor del servicio.' : 'Especifique el producto y su disponibilidad de inventario.' }}
                    </p>
                </div>

                <button
                    type="button"
                    class="rounded-xl p-1 text-gray-400 hover:bg-black/5 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white"
                    @click="emit('close')"
                >
                    <XMarkIcon class="h-5 w-5" />
                </button>
            </div>

            <form class="mt-5 space-y-4" @submit.prevent="submit">
                <!-- Tipo de Ítem -->
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Tipo de Ítem
                    </label>
                    <SegmentedControl
                        v-model="form.type"
                        :options="itemTypeOptions"
                    />
                </div>

                <!-- Nombre -->
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Nombre
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        :placeholder="form.type === 'service' ? 'ej. Plan Cloud Hosting Pro' : 'ej. Router Mikrotik RB4011'"
                        class="w-full rounded-2xl border-0 bg-gray-100/60 dark:bg-white/5 px-4 py-2.5 text-sm text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-[#0047BA]"
                        required
                    />
                </div>

                <!-- Descripción WYSIWYG -->
                <div>
                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Descripción
                    </label>
                    
                    <RichTextEditor
                        v-model="form.description"
                        placeholder="Ingrese la descripción detallada del producto o servicio..."
                    />
                </div>

                <!-- Bloque Precio & Moneda Dinámica -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Precio ({{ currencySymbol }})
                        </label>
                        <div class="relative flex items-center">
                            <input
                                v-model="form.price_list"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                class="w-full rounded-2xl border-0 bg-gray-100/60 dark:bg-white/5 px-4 py-2.5 text-sm text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-[#0047BA]"
                                required
                            />
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Moneda
                        </label>
                        <select
                            v-model="form.currency"
                            class="w-full rounded-2xl border-0 bg-gray-100/60 dark:bg-[#2c2c2e] px-4 py-2.5 text-sm text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-[#0047BA]"
                        >
                            <option value="USD">Dólares (USD $)</option>
                            <option value="BOB">Bolivianos (BOB Bs.)</option>
                        </select>
                    </div>
                </div>

                <!-- Controles de Stock (Sólo para Productos) -->
                <template v-if="form.type === 'product'">
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Tipo de Stock
                        </label>
                        <select
                            v-model="form.is_infinite"
                            class="w-full rounded-2xl border-0 bg-gray-100/60 dark:bg-[#2c2c2e] px-4 py-2.5 text-sm text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-[#0047BA]"
                        >
                            <option :value="false">Con stock limitado</option>
                            <option :value="true">Ilimitado</option>
                        </select>
                    </div>

                    <div v-if="!form.is_infinite">
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Cantidad disponible
                        </label>
                        <input
                            v-model="form.stock"
                            type="number"
                            min="0"
                            placeholder="0"
                            class="w-full rounded-2xl border-0 bg-gray-100/60 dark:bg-white/5 px-4 py-2.5 text-sm text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-[#0047BA]"
                        />
                    </div>
                </template>

                <!-- Acciones Modal -->
                <div class="mt-6 flex items-center justify-end gap-2 pt-2">
                    <SecondaryButton type="button" @click="emit('close')">
                        Cancelar
                    </SecondaryButton>

                    <PrimaryButton type="submit" :disabled="form.processing">
                        {{ submitButtonLabel }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </div>
</template>