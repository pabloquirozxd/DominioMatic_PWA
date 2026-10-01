<script setup>
import Modal from '@/Components/UI/Modal/Modal.vue'
import PrimaryButton from '@/Components/UI/Buttons/PrimaryButton.vue'
import SecondaryButton from '@/Components/UI/Buttons/SecondaryButton.vue'
import {
    CreditCardIcon,
    BuildingOfficeIcon,
    ShoppingBagIcon,
    CurrencyDollarIcon,
    TagIcon,
    CalendarIcon,
    ClockIcon,
    HashtagIcon,
} from '@heroicons/vue/24/outline'
import { useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'

const props = defineProps({
    show: { type: Boolean, default: false },
    editingSubscription: { type: Object, default: null },
    clients: { type: Array, default: () => [] },
    products: { type: Array, default: () => [] },
})

const emit = defineEmits(['close'])

const discountType = ref('fixed')

const form = useForm({
    client_id: '',
    product_id: '',
    quantity: 1,
    billing_cycle: 'monthly',
    price_list: 0,
    currency: 'USD',
    discount: 0,
    starts_at: '',
    expires_at: '',
    status: 'active',
})

const isEditing = computed(() =>
    Boolean(props.editingSubscription?.id)
)

function normalizeCurrency(rawCurrency) {
    if (!rawCurrency) return 'USD'

    const c = String(rawCurrency).trim().toUpperCase()

    if (['BOB', 'BS', 'BS.', 'BOLIVIANO', 'BOLIVIANOS'].includes(c)) {
        return 'BOB'
    }

    return 'USD'
}

watch(
    () => props.show,
    (isOpen) => {
        if (isOpen) {
            form.clearErrors()
            discountType.value = 'fixed'

            if (props.editingSubscription) {
                const sub = props.editingSubscription

                const associatedProduct = props.products.find(
                    (p) => String(p.id) === String(sub.product_id)
                )

                form.client_id = sub.client_id || ''
                form.product_id = sub.product_id || ''
                form.quantity = sub.quantity ?? 1
                form.billing_cycle = sub.billing_cycle || 'monthly'
                form.price_list =
                    sub.price_list ??
                    (associatedProduct?.price_list ?? 0)

                const rawCurrency =
                    associatedProduct?.currency ||
                    sub.currency ||
                    'USD'

                form.currency = normalizeCurrency(rawCurrency)
                form.discount = sub.discount || 0
                form.starts_at = formatDateForInput(sub.starts_at)
                form.expires_at = formatDateForInput(sub.expires_at)
                form.status = sub.status || 'active'
            } else {
                form.reset()
                form.quantity = 1
                form.billing_cycle = 'monthly'
                form.currency = 'USD'
                form.status = 'active'
            }
        }
    }
)

function formatDateForInput(dateString) {
    if (!dateString) return ''

    if (dateString.includes('/')) {
        const parts = dateString.split('/')
        if (parts.length === 3) {
            return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`
        }
    }

    return dateString
}

function handleProductChange(event) {
    const selectedProdId = event.target.value

    const product = props.products.find(
        (p) => String(p.id) === String(selectedProdId)
    )

    if (product) {
        form.price_list = product.price_list ?? 0
        form.currency = normalizeCurrency(product.currency)
    }
}

function autoCalculateExpiration() {
    if (!form.starts_at || form.billing_cycle === 'custom') return

    const startDate = new Date(form.starts_at + 'T00:00:00')
    if (isNaN(startDate.getTime())) return

    const endDate = new Date(startDate)

    if (form.billing_cycle === 'weekly') {
        endDate.setDate(endDate.getDate() + 7)
    } else if (form.billing_cycle === 'monthly') {
        endDate.setMonth(endDate.getMonth() + 1)
    } else if (form.billing_cycle === 'yearly') {
        endDate.setFullYear(endDate.getFullYear() + 1)
    }

    form.expires_at = endDate.toISOString().split('T')[0]
}

const currencySymbol = computed(() =>
    form.currency === 'BOB' ? 'Bs.' : '$'
)

const calculatedSubtotal = computed(() => {
    const price = parseFloat(form.price_list) || 0
    const qty = parseInt(form.quantity) || 1
    return price * qty
})

const calculatedNetTotal = computed(() => {
    const baseTotal = calculatedSubtotal.value
    const discountVal = parseFloat(form.discount) || 0

    if (discountType.value === 'percent') {
        const discountAmount = baseTotal * (discountVal / 100)
        return Math.max(0, baseTotal - discountAmount).toFixed(2)
    }

    return Math.max(0, baseTotal - discountVal).toFixed(2)
})

function submit() {
    const baseTotal = calculatedSubtotal.value
    const rawDiscount = parseFloat(form.discount) || 0

    form.discount =
        discountType.value === 'percent'
            ? baseTotal * (rawDiscount / 100)
            : rawDiscount

    if (isEditing.value) {
        form.put(
            route('subscriptions.update', props.editingSubscription.id),
            {
                preserveScroll: true,
                onSuccess: () => emit('close'),
            }
        )
        return
    }

    form.post(route('subscriptions.store'), {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    })
}

const inputClass =
    'w-full rounded-2xl border border-white/60 bg-white/50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#0072A8] focus:ring-2 focus:ring-[#0072A8]/20 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-white dark:placeholder:text-slate-500 dark:focus:border-[#0072A8]'

const disabledInputClass =
    'w-full cursor-not-allowed rounded-2xl border border-white/60 bg-white/25 px-4 py-3 text-sm font-semibold text-slate-500 outline-none dark:border-white/[0.08] dark:bg-white/[0.02] dark:text-slate-400'

const labelClass =
    'mb-1.5 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400'
</script>

<template>
    <Modal :show="show" max-width="2xl" @close="emit('close')">
        <div class="p-6 sm:p-7">
            <!-- Header -->
            <div>
                <h3
                    class="flex items-center gap-2 text-xl font-semibold tracking-[-0.025em] text-slate-950 dark:text-white"
                >
                    <CreditCardIcon
                        class="h-5 w-5 text-[#0072A8] dark:text-[#4FC3F7]"
                    />
                    {{
                        isEditing
                            ? 'Editar Suscripción'
                            : 'Nueva Suscripción'
                    }}
                </h3>

                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Configura el cliente, ítem, cantidad y ciclo de cobro de la suscripción.
                </p>
            </div>

            <form class="mt-6 space-y-5" @submit.prevent="submit">
                <!-- Empresa/Cliente + Producto/Servicio -->
                <div
                    class="space-y-4 rounded-2xl border border-white/60 bg-white/25 p-4 dark:border-white/[0.06] dark:bg-white/[0.02]"
                >
                    <!-- Cliente (Empresa) -->
                    <div>
                        <label :class="labelClass">
                            <BuildingOfficeIcon
                                class="h-3.5 w-3.5 text-[#0072A8] dark:text-[#4FC3F7]"
                            />
                            Empresa / Cliente *
                        </label>

                        <select
                            v-model="form.client_id"
                            :class="inputClass"
                            required
                        >
                            <option value="" disabled>
                                Seleccionar empresa...
                            </option>

                            <option
                                v-for="client in clients"
                                :key="client.id"
                                :value="client.id"
                            >
                                {{ client.company_name || client.name }}
                            </option>
                        </select>

                        <span
                            v-if="form.errors.client_id"
                            class="mt-1 text-xs text-rose-500"
                        >
                            {{ form.errors.client_id }}
                        </span>
                    </div>

                    <!-- Producto o Servicio -->
                    <div>
                        <label :class="labelClass">
                            <ShoppingBagIcon
                                class="h-3.5 w-3.5 text-[#0072A8] dark:text-[#4FC3F7]"
                            />
                            Producto / Servicio *
                        </label>

                        <select
                            v-model="form.product_id"
                            :class="inputClass"
                            required
                            @change="handleProductChange"
                        >
                            <option value="" disabled>
                                Seleccionar producto o servicio...
                            </option>

                            <option
                                v-for="product in products"
                                :key="product.id"
                                :value="product.id"
                            >
                                {{ product.name }} ({{
                                    normalizeCurrency(product.currency) === 'BOB' ? 'Bs.' : '$'
                                }}{{ product.price_list }})
                            </option>
                        </select>

                        <span
                            v-if="form.errors.product_id"
                            class="mt-1 text-xs text-rose-500"
                        >
                            {{ form.errors.product_id }}
                        </span>
                    </div>
                </div>

                <!-- Cantidad y Frecuencia de Cobranza -->
                <div class="grid gap-4 md:grid-cols-2">
                    <!-- Cantidad (Unidades / Licencias / Cuentas) -->
                    <div>
                        <label :class="labelClass">
                            <HashtagIcon class="h-3.5 w-3.5" />
                            Cantidad (Unidades / Licencias) *
                        </label>

                        <input
                            v-model.number="form.quantity"
                            type="number"
                            min="1"
                            step="1"
                            placeholder="1"
                            :class="inputClass"
                            required
                        />

                        <span
                            v-if="form.errors.quantity"
                            class="mt-1 text-xs text-rose-500"
                        >
                            {{ form.errors.quantity }}
                        </span>
                    </div>

                    <!-- Ciclo / Frecuencia de Cobro -->
                    <div>
                        <label :class="labelClass">
                            <ClockIcon class="h-3.5 w-3.5" />
                            Frecuencia de Cobro *
                        </label>

                        <select
                            v-model="form.billing_cycle"
                            :class="inputClass"
                            @change="autoCalculateExpiration"
                        >
                            <option value="weekly">Semanal (+7 días)</option>
                            <option value="monthly">Mensual (+1 mes)</option>
                            <option value="yearly">Anual (+1 año)</option>
                            <option value="custom">Personalizable (Fecha exacta)</option>
                        </select>
                    </div>
                </div>

                <!-- Precio Unitario + Moneda + Descuento -->
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label :class="labelClass">
                            <CurrencyDollarIcon class="h-3.5 w-3.5" />
                            Precio Unitario
                        </label>

                        <input
                            :value="`${currencySymbol} ${form.price_list}`"
                            type="text"
                            readonly
                            disabled
                            :class="disabledInputClass"
                        />
                    </div>

                    <div>
                        <label :class="labelClass">Moneda</label>

                        <input
                            :value="
                                form.currency === 'BOB'
                                    ? 'Bolivianos (BOB Bs.)'
                                    : 'Dólares (USD $)'
                            "
                            type="text"
                            readonly
                            disabled
                            :class="disabledInputClass"
                        />
                    </div>

                    <div>
                        <label :class="labelClass">
                            <TagIcon class="h-3.5 w-3.5" />
                            Descuento
                        </label>

                        <div class="flex gap-2">
                            <input
                                v-model="form.discount"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0"
                                :class="inputClass"
                            />

                            <select
                                v-model="discountType"
                                class="w-24 shrink-0 cursor-pointer rounded-2xl border border-white/60 bg-white/50 px-3 py-3 text-center text-sm font-semibold text-slate-900 outline-none transition focus:border-[#0072A8] focus:ring-2 focus:ring-[#0072A8]/20 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-white"
                            >
                                <option value="fixed">
                                    {{ currencySymbol }}
                                </option>
                                <option value="percent">%</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Resumen de cobro calculado -->
                <div
                    class="rounded-2xl border border-[#0072A8]/20 bg-[#0072A8]/[0.06] p-4 text-xs dark:border-[#0072A8]/25 dark:bg-[#0072A8]/[0.10]"
                >
                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-300">
                        <span>
                            Subtotal ({{ form.quantity || 1 }} x {{ currencySymbol }} {{ form.price_list }}):
                        </span>
                        <span class="font-semibold">
                            {{ currencySymbol }} {{ calculatedSubtotal.toFixed(2) }}
                        </span>
                    </div>

                    <div class="mt-2 flex items-center justify-between border-t border-[#0072A8]/15 pt-2 text-sm font-bold text-[#0072A8] dark:text-[#4FC3F7]">
                        <span>Total Neto a Cobrar por Periodo:</span>
                        <span class="text-base tracking-[-0.02em]">
                            {{ currencySymbol }} {{ calculatedNetTotal }}
                        </span>
                    </div>
                </div>

                <!-- Fechas -->
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label :class="labelClass">
                            <CalendarIcon class="h-3.5 w-3.5" />
                            Fecha de Inicio
                        </label>

                        <input
                            v-model="form.starts_at"
                            type="date"
                            :class="inputClass"
                            required
                            @change="autoCalculateExpiration"
                        />
                    </div>

                    <div>
                        <label :class="labelClass">
                            <CalendarIcon class="h-3.5 w-3.5" />
                            Fecha de Expiración / Vencimiento
                        </label>

                        <input
                            v-model="form.expires_at"
                            type="date"
                            :readonly="form.billing_cycle !== 'custom'"
                            :disabled="form.billing_cycle !== 'custom'"
                            :class="form.billing_cycle !== 'custom' ? disabledInputClass : inputClass"
                            required
                        />
                    </div>
                </div>

                <!-- Estado -->
                <div>
                    <label :class="labelClass">
                        <TagIcon class="h-3.5 w-3.5" />
                        Estado de la Suscripción
                    </label>

                    <select v-model="form.status" :class="inputClass">
                        <option value="active">Activa</option>
                        <option value="expired">Vencida</option>
                        <option value="suspended">Suspendida</option>
                    </select>
                </div>

                <!-- Acciones -->
                <div class="flex justify-end gap-3 pt-3">
                    <SecondaryButton
                        type="button"
                        @click="emit('close')"
                    >
                        Cancelar
                    </SecondaryButton>

                    <PrimaryButton
                        type="submit"
                        :disabled="form.processing"
                    >
                        {{
                            isEditing
                                ? 'Guardar cambios'
                                : 'Guardar suscripción'
                        }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>