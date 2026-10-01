<!-- resources/js/Pages/Clients/Components/ImportClientsModal.vue -->
<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

// Componentes del DominioMatic UI Kit
import Modal from '@/Components/UI/Modal/Modal.vue'
import PrimaryButton from '@/Components/UI/Buttons/PrimaryButton.vue'
import SecondaryButton from '@/Components/UI/Buttons/SecondaryButton.vue'

// Íconos
import {
    ArrowUpTrayIcon,
    ArrowDownTrayIcon,
    DocumentTextIcon,
    ExclamationCircleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['close'])

const fileInputRef = ref(null)
const selectedFile = ref(null)

const form = useForm({
    file: null,
})

const handleFileSelect = (event) => {
    const file = event.target.files[0]
    if (file) {
        selectedFile.value = file
        form.file = file
        form.clearErrors('file')
    }
}

const handleDrop = (event) => {
    const file = event.dataTransfer?.files[0]
    if (file && (file.type === 'text/csv' || file.name.endsWith('.csv'))) {
        selectedFile.value = file
        form.file = file
        form.clearErrors('file')
    }
}

const closeModal = () => {
    form.reset()
    form.clearErrors()
    selectedFile.value = null
    emit('close')
}

const submit = () => {
    if (!form.file) return

    form.post(route('clients.import'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal()
        },
    })
}
</script>

<template>
    <Modal :show="show" max-width="xl" @close="closeModal">
        <!-- Header con la tipografía e interlineado del UI Kit -->
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Importar clientes
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Sube un archivo CSV para registrar empresas o personas de forma masiva.
                </p>
            </div>
        </template>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Zona Drag & Drop con estilo Glass Dropzone -->
            <div
                @click="fileInputRef.click()"
                @dragover.prevent
                @drop.prevent="handleDrop"
                class="group relative cursor-pointer overflow-hidden rounded-2xl border border-dashed border-black/10 dark:border-white/15 bg-black/[0.015] dark:bg-white/[0.015] p-8 text-center transition hover:border-[#007AFF] hover:bg-black/[0.03] dark:hover:bg-white/[0.03]"
            >
                <input
                    ref="fileInputRef"
                    type="file"
                    accept=".csv"
                    class="hidden"
                    @change="handleFileSelect"
                />

                <div v-if="!selectedFile" class="flex flex-col items-center">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#007AFF]/10 text-[#007AFF] transition group-hover:scale-110">
                        <ArrowUpTrayIcon class="h-6 w-6 stroke-[2]" />
                    </div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        Haz clic o arrastra tu archivo CSV aquí
                    </p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                        Soporta codificación UTF-8 hasta 5MB
                    </p>
                </div>

                <!-- Vista previa del archivo seleccionado -->
                <div v-else class="flex items-center justify-between rounded-xl bg-black/5 dark:bg-white/5 p-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#007AFF] text-white">
                            <DocumentTextIcon class="h-5 w-5 stroke-[2]" />
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate max-w-[220px] sm:max-w-[320px]">
                                {{ selectedFile.name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ (selectedFile.size / 1024).toFixed(1) }} KB
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click.stop="selectedFile = null; form.file = null"
                        class="text-xs font-semibold text-gray-500 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-400 transition"
                    >
                        Cambiar
                    </button>
                </div>
            </div>

            <!-- Alerta de Errores -->
            <div v-if="form.errors.file" class="flex items-center gap-2 text-xs font-medium text-rose-500">
                <ExclamationCircleIcon class="h-4 w-4 shrink-0" />
                <span>{{ form.errors.file }}</span>
            </div>

            <!-- Estructura/Formato esperado del CSV con enlace de descarga -->
            <div class="rounded-2xl border border-black/5 dark:border-white/10 bg-black/[0.015] dark:bg-white/[0.015] p-4 text-xs space-y-2.5 text-gray-600 dark:text-gray-400">
                <div class="flex items-center justify-between gap-2">
                    <span class="font-bold text-gray-900 dark:text-gray-200">
                        Cabeceras aceptadas en la primera fila:
                    </span>

                    <a
                        :href="route('clients.import.template')"
                        download
                        class="inline-flex items-center gap-1.5 font-semibold text-[#007AFF] transition hover:underline shrink-0"
                    >
                        <ArrowDownTrayIcon class="h-3.5 w-3.5 stroke-[2.5]" />
                        Descargar plantilla (.csv)
                    </a>
                </div>

                <p class="font-mono text-[11px] text-[#007AFF] break-all bg-black/5 dark:bg-white/5 p-2 rounded-xl">
                    type, company_name, first_name, last_name, email, phone, website, language, portal_enabled
                </p>
            </div>

            <!-- Acciones del Formulario utilizando los Buttons del UI Kit -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <SecondaryButton type="button" @click="closeModal">
                    Cancelar
                </SecondaryButton>

                <PrimaryButton type="submit" :loading="form.processing" :disabled="!selectedFile">
                    Importar registros
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>