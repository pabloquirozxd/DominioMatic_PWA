<!-- resources/js/Pages/Subscriptions/Components/SubscriptionImportModal.vue -->
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

defineProps({
    show: { type: Boolean, default: false },
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

    form.post(route('subscriptions.import'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    })
}
</script>

<template>
    <Modal :show="show" max-width="xl" @close="closeModal">
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Importar suscripciones
                </h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Cargue un archivo CSV para registrar suscripciones de forma masiva.
                </p>
            </div>
        </template>

        <form @submit.prevent="submit" class="space-y-5 mt-4">
            <!-- Dropzone -->
            <div
                @click="fileInputRef.click()"
                @dragover.prevent
                @drop.prevent="handleDrop"
                class="group relative cursor-pointer overflow-hidden rounded-2xl border border-dashed border-black/10 dark:border-white/15 bg-black/[0.015] dark:bg-white/[0.015] p-6 text-center transition hover:border-[#0072A8] hover:bg-black/[0.03] dark:hover:bg-white/[0.03]"
            >
                <input
                    ref="fileInputRef"
                    type="file"
                    accept=".csv"
                    class="hidden"
                    @change="handleFileSelect"
                />

                <div v-if="!selectedFile" class="flex flex-col items-center">
                    <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-2xl bg-[#0072A8]/10 text-[#0072A8]">
                        <ArrowUpTrayIcon class="h-5 w-5 stroke-[2]" />
                    </div>
                    <p class="text-xs font-semibold text-gray-900 dark:text-white">
                        Haz clic o arrastra tu archivo CSV aquí
                    </p>
                    <p class="mt-1 text-[11px] text-gray-400">
                        Soporta formato UTF-8 hasta 5MB
                    </p>
                </div>

                <div v-else class="flex items-center justify-between rounded-xl bg-black/5 dark:bg-white/5 p-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#0072A8] text-white">
                            <DocumentTextIcon class="h-4 w-4 stroke-[2]" />
                        </div>
                        <div class="text-left">
                            <p class="text-xs font-semibold text-gray-900 dark:text-white truncate max-w-[200px]">
                                {{ selectedFile.name }}
                            </p>
                            <p class="text-[10px] text-gray-400">
                                {{ (selectedFile.size / 1024).toFixed(1) }} KB
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click.stop="selectedFile = null; form.file = null"
                        class="text-xs font-semibold text-gray-500 hover:text-red-500"
                    >
                        Cambiar
                    </button>
                </div>
            </div>

            <div v-if="form.errors.file" class="flex items-center gap-2 text-xs font-medium text-rose-500">
                <ExclamationCircleIcon class="h-4 w-4 shrink-0" />
                <span>{{ form.errors.file }}</span>
            </div>

            <!-- Cabeceras CSV requeridas -->
            <div class="rounded-2xl border border-black/5 dark:border-white/10 bg-black/[0.015] dark:bg-white/[0.015] p-3.5 text-xs space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <span class="font-bold text-gray-900 dark:text-gray-200">
                        Cabeceras aceptadas:
                    </span>

                    <a
                        :href="route('subscriptions.import.template')"
                        download
                        class="inline-flex items-center gap-1 font-semibold text-[#0072A8] transition hover:underline"
                    >
                        <ArrowDownTrayIcon class="h-3.5 w-3.5" />
                        Plantilla (.csv)
                    </a>
                </div>

                <p class="font-mono text-[10px] text-[#0072A8] break-all bg-black/5 dark:bg-white/5 p-2 rounded-xl">
                    client_id, product_id, quantity, billing_cycle, price_list, discount, starts_at, expires_at, status
                </p>
            </div>

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