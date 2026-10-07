<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

import Modal from '@/Components/UI/Modal/Modal.vue'
import PrimaryButton from '@/Components/UI/Buttons/PrimaryButton.vue'
import SecondaryButton from '@/Components/UI/Buttons/SecondaryButton.vue'

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
    if (file && (file.type === 'text/csv' || file.name.endsWith('.csv') || file.name.endsWith('.xlsx'))) {
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
        <template #header>
            <div class="px-1">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Importar clientes
                </h2>
                <p class="mt-2 text-base text-gray-600 dark:text-gray-300">
                    Sube un archivo CSV para registrar empresas o personas de forma masiva.
                </p>
            </div>
        </template>

        <form @submit.prevent="submit" class="space-y-8 px-1">
            <div
                @click="fileInputRef.click()"
                @dragover.prevent
                @drop.prevent="handleDrop"
                class="group relative cursor-pointer overflow-hidden rounded-3xl border border-black/10 dark:border-white/20 bg-white/40 dark:bg-white/5 backdrop-blur-xl p-10 text-center shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)] transition-all duration-300 hover:border-[#007AFF]/50 hover:bg-white/60 dark:hover:bg-white/10 hover:shadow-lg hover:shadow-[#007AFF]/5"
            >
                <input
                    ref="fileInputRef"
                    type="file"
                    accept=".csv, .xlsx"
                    class="hidden"
                    @change="handleFileSelect"
                />

                <div v-if="!selectedFile" class="flex flex-col items-center">
                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-[#007AFF]/10 text-[#007AFF] shadow-inner transition-transform duration-300 group-hover:scale-110 group-hover:bg-[#007AFF]/15">
                        <ArrowUpTrayIcon class="h-8 w-8 stroke-[2]" />
                    </div>
                    <p class="text-base font-semibold text-gray-900 dark:text-white">
                        Haz clic o arrastra tu archivo CSV aquí
                    </p>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Soporta codificación UTF-8 hasta 5MB
                    </p>
                </div>

                <div v-else class="flex items-center justify-between rounded-2xl border border-black/5 dark:border-white/10 bg-black/5 dark:bg-black/20 p-4 backdrop-blur-md">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#007AFF] text-white shadow-md shadow-[#007AFF]/20">
                            <DocumentTextIcon class="h-6 w-6 stroke-[2]" />
                        </div>
                        <div class="text-left">
                            <p class="text-base font-semibold text-gray-900 dark:text-white truncate max-w-[200px] sm:max-w-[300px]">
                                {{ selectedFile.name }}
                            </p>
                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                {{ (selectedFile.size / 1024).toFixed(1) }} KB
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click.stop="selectedFile = null; form.file = null"
                        class="rounded-lg px-3 py-1.5 text-sm font-semibold text-gray-500 transition-colors hover:bg-red-500/10 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-400"
                    >
                        Cambiar
                    </button>
                </div>
            </div>

            <div v-if="form.errors.file" class="flex items-center gap-2 rounded-xl bg-red-50 dark:bg-red-500/10 p-3 text-sm font-medium text-red-600 dark:text-red-400">
                <ExclamationCircleIcon class="h-5 w-5 shrink-0" />
                <span>{{ form.errors.file }}</span>
            </div>

            <div class="rounded-2xl border border-black/5 dark:border-white/10 bg-black/5 dark:bg-white/[0.03] p-5 text-sm space-y-3 backdrop-blur-md">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <span class="font-semibold text-gray-900 dark:text-gray-200">
                        Cabeceras aceptadas (primera fila):
                    </span>

                    <a
                        :href="route('clients.import.template')"
                        download
                        class="inline-flex items-center gap-1.5 rounded-lg text-sm font-semibold text-[#007AFF] transition hover:text-[#0056b3] dark:hover:text-[#3399ff] hover:underline shrink-0"
                    >
                        <ArrowDownTrayIcon class="h-4 w-4 stroke-[2.5]" />
                        Descargar plantilla
                    </a>
                </div>

                <div class="overflow-x-auto rounded-xl bg-white/50 dark:bg-black/30 p-3 shadow-inner">
                    <p class="whitespace-nowrap font-mono text-[13px] text-[#007AFF] dark:text-[#3399ff]">
                        type, company_name, first_name, last_name, email, phone, website, language, portal_enabled
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <SecondaryButton @click="closeModal" type="button">
                    Cancelar
                </SecondaryButton>

                <PrimaryButton :disabled="!selectedFile" :loading="form.processing" type="submit">
                    Importar registros
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>