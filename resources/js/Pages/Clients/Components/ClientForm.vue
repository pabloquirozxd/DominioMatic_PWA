<!-- resources/js/Pages/Clients/Components/ClientForm.vue -->
<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useForm } from '@inertiajs/vue3'
import axios from 'axios'

// Componentes del DominioMatic UI Kit
import FormField from '@/Components/UI/Forms/FormField.vue'
import TextField from '@/Components/UI/Forms/TextField.vue'
import SelectField from '@/Components/UI/Forms/SelectField.vue'
import LiquidToggle from '@/Components/UI/Toggles/LiquidToggle.vue'
import SegmentedControl from '@/Components/UI/Navigation/SegmentedControl.vue'
import PrimaryButton from '@/Components/UI/Buttons/PrimaryButton.vue'
import SecondaryButton from '@/Components/UI/Buttons/SecondaryButton.vue'

// Íconos
import {
    BuildingOffice2Icon,
    GlobeAltIcon,
    LanguageIcon,
    UserIcon,
    EnvelopeIcon,
    PhoneIcon,
    IdentificationIcon,
    CreditCardIcon,
    DocumentTextIcon,
    CheckCircleIcon,
    PencilSquareIcon,
    UserMinusIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    client: {
        type: Object,
        default: null,
    },
})

const emit = defineEmits(['submitted', 'success', 'cancel'])

const firstInputRef = ref(null)
const dropdownRef = ref(null)

// Estados para Autocompletado de Contacto
const suggestions = ref([])
const showSuggestions = ref(false)
const isContactLocked = ref(false)
let searchTimeout = null

// Configuración para SegmentedControl
const typeOptions = [
    { label: 'Empresa', value: 'company', icon: BuildingOffice2Icon },
    { label: 'Individual', value: 'person', icon: UserIcon },
]

const languageOptions = [
    { label: 'Español', value: 'Español' },
    { label: 'English', value: 'English' },
    { label: 'Português', value: 'Português' },
]

const paymentTermsOptions = [
    { label: 'Due on Receipt (Al recibir)', value: 'Due on Receipt' },
    { label: 'Net 15 (15 días)', value: 'Net 15' },
    { label: 'Net 30 (30 días)', value: 'Net 30' },
    { label: 'Net 60 (60 días)', value: 'Net 60' },
]

const getPrimaryContact = (clientObj) => {
    return clientObj?.contacts?.find(c => c.pivot?.is_primary) || clientObj?.contacts?.[0] || clientObj?.primary_contact
}

const form = useForm({
    contact_id: null,
    type: props.client?.type ?? 'company',
    company_name: props.client?.company_name ?? '',
    company_phone: props.client?.company_phone ?? '',
    tax_id: props.client?.tax_id ?? '',
    payment_terms: props.client?.payment_terms ?? 'Due on Receipt',
    notes: props.client?.notes ?? '',
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    language: props.client?.language ?? 'Español',
    website: props.client?.website ? props.client.website.replace(/^https?:\/\//, '') : '',
    portal_enabled: props.client?.portal_enabled ?? false,
})

// Búsqueda en tiempo real de contactos
const onFirstNameInput = (e) => {
    if (isContactLocked.value) return

    clearTimeout(searchTimeout)
    const val = e.target.value

    if (!val || val.trim().length < 2) {
        suggestions.value = []
        showSuggestions.value = false
        return
    }

    searchTimeout = setTimeout(async () => {
        try {
            const { data } = await axios.get(route('contacts.search'), { params: { q: val } })
            suggestions.value = data
            showSuggestions.value = data.length > 0
        } catch (error) {
            console.error('Error buscando contactos:', error)
        }
    }, 300)
}

// Seleccionar un contacto existente desde las sugerencias
const selectContact = (contact) => {
    form.contact_id = contact.id
    form.first_name = contact.first_name
    form.last_name = contact.last_name || ''
    form.email = contact.email || ''
    form.phone = contact.phone || ''

    isContactLocked.value = true
    showSuggestions.value = false
    suggestions.value = []
}

// Desbloquear campos manteniendo el contact_id (Edita a la persona actual)
const unlockContact = () => {
    isContactLocked.value = false
}

// Desvincular contacto actual (Permite buscar o ingresar a una persona diferente)
const detachContact = () => {
    form.contact_id = null
    form.first_name = ''
    form.last_name = ''
    form.email = ''
    form.phone = ''
    isContactLocked.value = false
}

// Cerrar dropdown si se hace clic fuera del buscador
const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        showSuggestions.value = false
    }
}

const focusFirstInput = () => {
    nextTick(() => {
        firstInputRef.value?.focus()
    })
}

onMounted(() => {
    focusFirstInput()
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})

watch(
    () => props.client,
    (newClient) => {
        if (newClient) {
            const primary = getPrimaryContact(newClient)
            form.contact_id = primary?.id || null
            form.type = newClient.type || 'company'
            form.company_name = newClient.company_name || ''
            form.company_phone = newClient.company_phone || ''
            form.tax_id = newClient.tax_id || ''
            form.payment_terms = newClient.payment_terms || 'Due on Receipt'
            form.notes = newClient.notes || ''
            form.first_name = primary?.first_name || newClient.first_name || ''
            form.last_name = primary?.last_name || newClient.last_name || ''
            form.email = primary?.email || newClient.email || ''
            form.phone = primary?.phone || newClient.phone || ''
            form.website = newClient.website ? newClient.website.replace(/^https?:\/\//, '') : ''
            form.language = newClient.language || 'Español'
            form.portal_enabled = Boolean(newClient.portal_enabled)

            isContactLocked.value = Boolean(primary?.id)
        } else {
            form.reset()
            form.contact_id = null
            isContactLocked.value = false
        }
        focusFirstInput()
    },
    { immediate: true }
)

const handleSuccess = () => {
    emit('submitted')
    emit('success')
}

const submit = () => {
    if (props.client?.id) {
        form.put(route('clients.update', props.client.id), {
            preserveScroll: true,
            onSuccess: handleSuccess,
        })
    } else {
        form.post(route('clients.store'), {
            preserveScroll: true,
            onSuccess: handleSuccess,
        })
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <!-- Selector Tipo de Cliente -->
        <FormField label="Tipo de Cuenta">
            <SegmentedControl
                v-model="form.type"
                :options="typeOptions"
            />
        </FormField>

        <!-- Bloque: Datos de la Empresa -->
        <div v-if="form.type === 'company'" class="space-y-4">
            <FormField
                for="company_name"
                label="Nombre de la empresa"
                :icon="BuildingOffice2Icon"
                required
                :error="form.errors.company_name"
            >
                <TextField
                    id="company_name"
                    ref="firstInputRef"
                    v-model="form.company_name"
                    placeholder="ej. Torre Fuerte Ekklesia"
                    :has-error="Boolean(form.errors.company_name)"
                    autocomplete="organization"
                />
            </FormField>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <FormField
                    for="company_phone"
                    label="Teléfono de la Empresa"
                    :icon="PhoneIcon"
                    :error="form.errors.company_phone"
                >
                    <TextField
                        id="company_phone"
                        v-model="form.company_phone"
                        type="tel"
                        placeholder="+591 3 3330000"
                        :has-error="Boolean(form.errors.company_phone)"
                    />
                </FormField>

                <FormField
                    for="tax_id"
                    label="ID Impuestos / NIT"
                    :icon="IdentificationIcon"
                    :error="form.errors.tax_id"
                >
                    <TextField
                        id="tax_id"
                        v-model="form.tax_id"
                        placeholder="ej. 1020304050"
                        :has-error="Boolean(form.errors.tax_id)"
                    />
                </FormField>
            </div>
        </div>

        <!-- Bloque: Contacto Principal (Con Autocompletado) -->
        <div class="space-y-4 rounded-2xl border border-black/5 bg-black/[0.015] p-4 dark:border-white/10 dark:bg-white/[0.015]">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    {{ form.type === 'company' ? 'Contacto Principal (Receptor de Facturación)' : 'Datos Personales' }}
                </h4>

                <!-- Estado del Contacto: Vinculado / Edición / Cambio -->
                <div v-if="isContactLocked" class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                        <CheckCircleIcon class="h-3.5 w-3.5" /> Vinculado
                    </span>
                    <button
                        type="button"
                        @click="unlockContact"
                        class="flex items-center gap-1 text-xs text-gray-500 hover:text-amber-500 transition"
                        title="Permitir editar los datos de este contacto"
                    >
                        <PencilSquareIcon class="h-3.5 w-3.5" /> Editar
                    </button>
                    <span class="text-gray-300 dark:text-gray-700">•</span>
                    <button
                        type="button"
                        @click="detachContact"
                        class="flex items-center gap-1 text-xs text-gray-500 hover:text-rose-500 transition"
                        title="Desvincular para seleccionar o crear a otra persona"
                    >
                        <UserMinusIcon class="h-3.5 w-3.5" /> Cambiar
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <!-- Nombre con Autocomplete -->
                <div class="relative" ref="dropdownRef">
                    <FormField
                        for="first_name"
                        label="Nombre"
                        :icon="UserIcon"
                        required
                        :error="form.errors.first_name"
                    >
                        <TextField
                            id="first_name"
                            :ref="form.type === 'person' ? 'firstInputRef' : null"
                            v-model="form.first_name"
                            :disabled="isContactLocked"
                            :placeholder="form.type === 'company' ? 'ej. Gonzalo' : 'ej. Gisela'"
                            :has-error="Boolean(form.errors.first_name)"
                            autocomplete="off"
                            @input="onFirstNameInput"
                            @focus="onFirstNameInput"
                        />
                    </FormField>

                    <!-- Menú Flotante de Sugerencias -->
                    <div
                        v-if="showSuggestions && !isContactLocked"
                        class="absolute z-50 left-0 right-0 mt-1 rounded-xl border border-gray-200 bg-white shadow-xl dark:border-white/10 dark:bg-[#2c2c2e] overflow-hidden"
                    >
                        <div class="p-2 text-[10px] font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100 dark:border-white/5">
                            Contactos Existentes
                        </div>
                        <ul class="max-h-48 overflow-y-auto">
                            <li
                                v-for="item in suggestions"
                                :key="item.id"
                                @click="selectContact(item)"
                                class="flex flex-col px-3 py-2 text-sm hover:bg-[#007AFF]/10 cursor-pointer transition"
                            >
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    {{ item.first_name }} {{ item.last_name }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ item.email || 'Sin correo' }} {{ item.phone ? '• ' + item.phone : '' }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Apellido (Opcional) -->
                <FormField
                    for="last_name"
                    label="Apellido"
                    :icon="UserIcon"
                    :error="form.errors.last_name"
                >
                    <TextField
                        id="last_name"
                        v-model="form.last_name"
                        :disabled="isContactLocked"
                        :placeholder="form.type === 'company' ? 'ej. Zenteno' : 'ej. Rodriguez'"
                        :has-error="Boolean(form.errors.last_name)"
                        autocomplete="family-name"
                    />
                </FormField>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <!-- Email -->
                <FormField
                    for="email"
                    label="Correo Electrónico"
                    :icon="EnvelopeIcon"
                    :error="form.errors.email"
                >
                    <TextField
                        id="email"
                        v-model="form.email"
                        type="email"
                        :disabled="isContactLocked"
                        placeholder="correo@ejemplo.com"
                        :has-error="Boolean(form.errors.email)"
                        autocomplete="email"
                    />
                </FormField>

                <!-- Teléfono Móvil -->
                <FormField
                    for="phone"
                    label="Teléfono Móvil"
                    :icon="PhoneIcon"
                    :error="form.errors.phone"
                >
                    <TextField
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        :disabled="isContactLocked"
                        placeholder="+591 70000000"
                        :has-error="Boolean(form.errors.phone)"
                        autocomplete="tel"
                    />
                </FormField>
            </div>
        </div>

        <!-- Sitio Web e Idioma -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <FormField
                for="website"
                label="Sitio Web"
                :icon="GlobeAltIcon"
                :error="form.errors.website"
            >
                <TextField
                    id="website"
                    v-model="form.website"
                    placeholder="empresa.com"
                    :has-error="Boolean(form.errors.website)"
                    autocomplete="url"
                />
            </FormField>

            <FormField
                for="language"
                label="Idioma de Notificaciones"
                :icon="LanguageIcon"
                :error="form.errors.language"
            >
                <SelectField
                    id="language"
                    v-model="form.language"
                    :options="languageOptions"
                    :has-error="Boolean(form.errors.language)"
                />
            </FormField>
        </div>

        <!-- Términos de Pago -->
        <FormField
            for="payment_terms"
            label="Términos de Pago"
            :icon="CreditCardIcon"
            :error="form.errors.payment_terms"
        >
            <SelectField
                id="payment_terms"
                v-model="form.payment_terms"
                :options="paymentTermsOptions"
                :has-error="Boolean(form.errors.payment_terms)"
            />
        </FormField>

        <!-- Notas Internas -->
        <FormField
            for="notes"
            label="Notas Internas"
            :icon="DocumentTextIcon"
            :error="form.errors.notes"
        >
            <textarea
                id="notes"
                v-model="form.notes"
                rows="3"
                placeholder="Observaciones o notas internas acerca del cliente..."
                class="w-full rounded-2xl border border-black/10 bg-white p-3.5 text-sm text-gray-900 outline-none transition focus:border-[#007AFF] dark:border-white/10 dark:bg-[#1b1b1d] dark:text-white"
            ></textarea>
        </FormField>

        <!-- Toggle: Portal del Cliente -->
        <div class="flex items-center justify-between rounded-2xl border border-black/5 bg-black/[0.02] p-4 dark:border-white/10 dark:bg-white/[0.02]">
            <div>
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                    Portal del cliente
                </h4>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Permite acceso directo al panel de autogestión.
                </p>
            </div>

            <LiquidToggle v-model="form.portal_enabled" />
        </div>

        <!-- Acciones del Formulario -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-black/5 dark:border-white/10">
            <SecondaryButton type="button" @click="emit('cancel')">
                Cancelar
            </SecondaryButton>

            <PrimaryButton type="submit" :loading="form.processing">
                {{ client ? 'Guardar cambios' : 'Crear cliente' }}
            </PrimaryButton>
        </div>
    </form>
</template>