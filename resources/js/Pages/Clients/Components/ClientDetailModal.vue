<!-- resources/js/Pages/Clients/Components/ClientDetailModal.vue -->
<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

import Modal from '@/Components/UI/Modal/Modal.vue'
import ViewTransition from '@/Components/UI/Modal/ViewTransition.vue'
import ContactForm from '@/Pages/Clients/Components/ContactForm.vue'

import PrimaryButton from '@/Components/UI/Buttons/PrimaryButton.vue'
import SecondaryButton from '@/Components/UI/Buttons/SecondaryButton.vue'

import {
    PlusIcon,
    EnvelopeIcon,
    PhoneIcon,
    BriefcaseIcon,
    PencilSquareIcon,
    TrashIcon,
    GlobeAltIcon,
    ArrowTopRightOnSquareIcon,
    UserGroupIcon,
    BuildingOffice2Icon,
    UserIcon,   
    ArrowLeftIcon,
    StarIcon,
    DocumentTextIcon,
    CreditCardIcon,
    IdentificationIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    show: Boolean,
    client: Object,
})

const emit = defineEmits(['close', 'refresh'])

const currentView = ref('detail') // 'detail' | 'form'
const transitionDirection = ref('forward') // 'forward' | 'back'
const selectedContact = ref(null)
const formRef = ref(null)

const isPrimaryContact = (contact) => {
    if (!contact) return false
    return (
        contact.pivot?.is_primary === true ||
        contact.pivot?.is_primary === 1 ||
        contact.is_primary === true ||
        contact.is_primary === 1
    )
}

const openCreateContact = () => {
    selectedContact.value = null
    transitionDirection.value = 'forward'
    currentView.value = 'form'
}

const openEditContact = (contact) => {
    selectedContact.value = {
        ...contact,
        type: isPrimaryContact(contact) ? 'primary' : 'secondary',
        position: contact.pivot?.position || contact.position || '',
    }
    transitionDirection.value = 'forward'
    currentView.value = 'form'
}

const backToDetail = () => {
    transitionDirection.value = 'back'
    currentView.value = 'detail'
}

const handleContactSaved = () => {
    emit('refresh')
    backToDetail()
}

const triggerFormSubmit = () => {
    formRef.value?.submit()
}

const setPrimary = (contactId) => {
    router.patch(route('clients.contacts.set-primary', { client: props.client.id, contact: contactId }), {}, {
        preserveScroll: true,
        onSuccess: () => emit('refresh'),
    })
}

const deleteContact = (contactId) => {
    if (confirm('¿Estás seguro de que deseas eliminar o desvincular este contacto?')) {
        router.delete(route('contacts.destroy', contactId), {
            data: { client_id: props.client.id },
            preserveScroll: true,
            onSuccess: () => emit('refresh'),
        })
    }
}

watch([() => props.show, () => props.client?.id], ([newShow]) => {
    if (!newShow) {
        currentView.value = 'detail'
        selectedContact.value = null
    }
})
</script>

<template>
    <Modal :show="show" max-width="2xl" @close="$emit('close')">
        <!-- HEADER DINÁMICO -->
        <template #header>
            <div v-if="client">
                <ViewTransition :direction="transitionDirection">
                    <div v-if="currentView === 'detail'" key="header-detail" class="space-y-1">
                        <div class="flex items-center gap-3">
                            <component
                                :is="client.type === 'company' ? BuildingOffice2Icon : UserIcon"
                                class="h-6 w-6 text-[#007AFF]"
                            />
                            <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                {{ client.company_name || `${client.first_name || ''} ${client.last_name || ''}`.trim() }}
                            </h2>

                            <span class="rounded-xl bg-[#007AFF]/10 px-3 py-1 text-xs font-semibold text-[#007AFF]">
                                {{ client.language || 'es' }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 pt-1 text-sm text-gray-500 dark:text-gray-400">
                            <GlobeAltIcon class="h-4 w-4 shrink-0 text-gray-400" />
                            <a
                                v-if="client.website"
                                :href="client.website.startsWith('http') ? client.website : `https://${client.website}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1 font-medium text-[#007AFF] hover:underline"
                            >
                                <span>{{ client.website }}</span>
                                <ArrowTopRightOnSquareIcon class="h-3.5 w-3.5" />
                            </a>
                            <span v-else class="italic text-gray-400 dark:text-gray-500">Sin sitio web registrado</span>
                        </div>
                    </div>

                    <div v-else key="header-form" class="flex items-center gap-3">
                        <button
                            type="button"
                            @click="backToDetail"
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-black/5 text-gray-600 transition hover:bg-black/10 dark:bg-white/10 dark:text-gray-300 dark:hover:bg-white/20"
                        >
                            <ArrowLeftIcon class="h-4 w-4 stroke-[2.5]" />
                        </button>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ selectedContact ? 'Editar contacto' : 'Nuevo contacto' }}
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ selectedContact ? 'Actualiza los datos del contacto asignado.' : 'Registra un nuevo contacto asociado a este cliente.' }}
                            </p>
                        </div>
                    </div>
                </ViewTransition>
            </div>
        </template>

        <!-- CONTENIDO PRINCIPAL -->
        <ViewTransition :direction="transitionDirection">
            <div v-if="currentView === 'detail' && client" key="body-detail" class="space-y-6">
                <!-- DETALLES Y DATOS ADMINISTRATIVOS DEL CLIENTE -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3.5 rounded-2xl border border-black/5 dark:border-white/10 bg-black/[0.015] dark:bg-white/[0.015]">
                    <div class="flex items-center gap-2.5">
                        <PhoneIcon class="h-4 w-4 text-gray-400 shrink-0" />
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Teléfono Cuenta</p>
                            <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate">
                                {{ client.phone || client.company_phone || 'Sin teléfono' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <IdentificationIcon class="h-4 w-4 text-gray-400 shrink-0" />
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">NIT / Tax ID</p>
                            <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate">
                                {{ client.tax_id || 'Sin NIT/NIT ID' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <CreditCardIcon class="h-4 w-4 text-gray-400 shrink-0" />
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Términos de Pago</p>
                            <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate">
                                {{ client.payment_terms || 'Al recibir' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- NOTAS INTERNAS -->
                <div v-if="client.notes" class="rounded-2xl border border-amber-500/20 bg-amber-500/5 p-4 dark:border-amber-500/30 dark:bg-amber-500/10">
                    <div class="flex items-center gap-2 mb-1.5 text-amber-700 dark:text-amber-400">
                        <DocumentTextIcon class="h-4 w-4 shrink-0" />
                        <h4 class="text-xs font-bold uppercase tracking-wider">Notas Internas</h4>
                    </div>
                    <p class="text-xs font-medium text-amber-900 dark:text-amber-200 leading-relaxed whitespace-pre-line">
                        {{ client.notes }}
                    </p>
                </div>

                <!-- ENCABEZADO CONTACTOS -->
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <UserGroupIcon class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                Contactos asociados
                            </h3>
                            <span class="rounded-full bg-black/5 px-2.5 py-0.5 text-xs font-semibold text-gray-600 dark:bg-white/10 dark:text-gray-300">
                                {{ client.contacts?.length || 0 }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Personas de contacto directo ligadas a esta cuenta.
                        </p>
                    </div>

                    <PrimaryButton type="button" @click="openCreateContact" class="!py-2 text-xs">
                        <PlusIcon class="mr-1.5 h-4 w-4 stroke-[2.5]" />
                        Añadir contacto
                    </PrimaryButton>
                </div>

                <!-- GRID DE CONTACTOS -->
                <div v-if="client.contacts && client.contacts.length > 0" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div
                        v-for="contact in client.contacts"
                        :key="contact.id"
                        class="flex flex-col justify-between space-y-3 rounded-2xl border border-black/5 bg-black/[0.015] p-4 transition hover:border-black/10 dark:border-white/10 dark:bg-white/[0.015] dark:hover:border-white/20"
                    >
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <h4 class="font-semibold text-gray-900 dark:text-white">
                                    {{ contact.first_name }} {{ contact.last_name }}
                                </h4>

                                <span
                                    :class="[
                                        isPrimaryContact(contact)
                                            ? 'border border-emerald-500/20 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                            : 'border border-black/5 bg-black/5 text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-gray-400',
                                    ]"
                                    class="rounded-lg px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                >
                                    {{ isPrimaryContact(contact) ? 'Principal' : 'Secundario' }}
                                </span>
                            </div>

                            <div class="mt-3 space-y-2 text-xs text-gray-600 dark:text-gray-300">
                                <div class="flex items-center gap-2">
                                    <BriefcaseIcon class="h-3.5 w-3.5 shrink-0 text-gray-400" />
                                    <span class="truncate">
                                        {{ contact.pivot?.position || contact.position || 'Sin cargo especificado' }}
                                    </span>
                                </div>
                                <div v-if="contact.email" class="flex items-center gap-2">
                                    <EnvelopeIcon class="h-3.5 w-3.5 shrink-0 text-gray-400" />
                                    <a :href="`mailto:${contact.email}`" class="truncate transition hover:text-[#007AFF]">
                                        {{ contact.email }}
                                    </a>
                                </div>
                                <div v-if="contact.phone" class="flex items-center gap-2">
                                    <PhoneIcon class="h-3.5 w-3.5 shrink-0 text-gray-400" />
                                    <span>{{ contact.phone }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between border-t border-black/5 pt-3 dark:border-white/10">
                            <div>
                                <button
                                    v-if="!isPrimaryContact(contact)"
                                    type="button"
                                    @click="setPrimary(contact.id)"
                                    class="inline-flex items-center gap-1 text-[11px] font-medium text-gray-400 hover:text-amber-500 transition"
                                    title="Marcar como contacto principal"
                                >
                                    <StarIcon class="h-3.5 w-3.5" />
                                    Hacer Principal
                                </button>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="openEditContact(contact)"
                                    class="inline-flex items-center gap-1 rounded-xl bg-black/5 px-2.5 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-black/10 active:scale-95 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white"
                                >
                                    <PencilSquareIcon class="h-3.5 w-3.5 text-gray-400" />
                                    Editar
                                </button>
                                <button
                                    type="button"
                                    @click="deleteContact(contact.id)"
                                    class="inline-flex items-center gap-1 rounded-xl bg-red-500/10 px-2.5 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-500/20 active:scale-95 dark:text-red-400"
                                >
                                    <TrashIcon class="h-3.5 w-3.5" />
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 p-8 text-center dark:border-white/10">
                    <UserGroupIcon class="h-8 w-8 text-gray-400" />
                    <p class="mt-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                        No hay contactos asociados a este cliente todavía.
                    </p>
                </div>
            </div>

            <div v-else-if="currentView === 'form'" key="body-form">
            <ContactForm
                ref="formRef"
                :client="client"
                :contact="selectedContact"
                @saved="handleContactSaved"
            />
        </div>
        </ViewTransition>

        <!-- FOOTER DINÁMICO -->
        <template #footer>
            <ViewTransition :direction="transitionDirection">
                <div v-if="currentView === 'detail'" key="footer-detail">
                    <SecondaryButton type="button" @click="$emit('close')">
                        Cerrar
                    </SecondaryButton>
                </div>

                <div v-else key="footer-form" class="flex items-center gap-3">
                    <SecondaryButton type="button" @click="backToDetail">
                        Cancelar
                    </SecondaryButton>
                    <PrimaryButton type="button" :disabled="formRef?.processing" @click="triggerFormSubmit">
                        {{ selectedContact ? 'Guardar cambios' : 'Crear contacto' }}
                    </PrimaryButton>
                </div>
            </ViewTransition>
        </template>
    </Modal>
</template>