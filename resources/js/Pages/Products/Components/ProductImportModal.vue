<script setup>
import PrimaryButton from '@/Components/UI/Buttons/PrimaryButton.vue'
import SecondaryButton from '@/Components/UI/Buttons/SecondaryButton.vue'
import { CloudArrowUpIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { useForm } from '@inertiajs/vue3'

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['close'])

const importForm = useForm({
    file: null,
})

function handleFileChange(e) {
    const file = e.target.files[0]
    if (file) importForm.file = file
}

function submitImport() {
    if (!importForm.file) return
    importForm.post(route('products.import'), {
        preserveScroll: true,
        onSuccess: () => {
            importForm.reset()
            emit('close')
        },
    })
}
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-md transition-all"
    >
        <div class="w-full max-w-md rounded-3xl bg-white/90 dark:bg-[#1c1c1e]/90 p-6 shadow-2xl border border-white/60 dark:border-white/10 backdrop-blur-2xl">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Importar Catálogo</h3>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Cargue un archivo .csv o .xlsx para carga masiva.</p>
                </div>
                <button type="button" class="rounded-xl p-1 text-gray-400 hover:bg-black/5 dark:hover:bg-white/10" @click="emit('close')">
                    <XMarkIcon class="h-5 w-5" />
                </button>
            </div>

            <form class="mt-6 space-y-4" @submit.prevent="submitImport">
                <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-black/10 dark:border-white/10 p-6 text-center hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                    <CloudArrowUpIcon class="h-10 w-10 text-[#0047BA]" />
                    <label class="mt-2 cursor-pointer text-xs font-semibold text-[#0047BA] hover:underline">
                        <span>Seleccionar archivo local</span>
                        <input type="file" accept=".csv, .xlsx" class="sr-only" @change="handleFileChange" />
                    </label>
                    <p class="mt-1 text-[11px] text-gray-400">
                        {{ importForm.file ? importForm.file.name : 'CSV o XLSX' }}
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <SecondaryButton type="button" @click="emit('close')">
                        Cancelar
                    </SecondaryButton>
                    <PrimaryButton type="submit" :disabled="!importForm.file || importForm.processing">
                        Procesar Carga
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </div>
</template>