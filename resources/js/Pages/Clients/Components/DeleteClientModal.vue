<!-- resources/js/Pages/Clients/Components/DeleteClientModal.vue -->
<script setup>
import Modal from '@/Components/UI/Modal/Modal.vue'
import SecondaryButton from '@/Components/UI/Buttons/SecondaryButton.vue'
import { useForm } from '@inertiajs/vue3'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    client: {
        type: Object,
        default: null,
    },
})

const emit = defineEmits(['close', 'deleted'])

const form = useForm({})

const destroy = () => {
    if (!props.client) return

    form.delete(route('clients.destroy', props.client.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('deleted')
            emit('close')
        },
    })
}
</script>

<template>
    <Modal :show="show" max-width="md" @close="emit('close')">
        <template #header>
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-2xl bg-red-500/10 text-red-500 dark:bg-red-500/20">
                    <ExclamationTriangleIcon class="h-6 w-6" />
                </div>
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                        Eliminar cliente
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Esta acción no se puede deshacer.
                    </p>
                </div>
            </div>
        </template>

        <div class="py-2">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                ¿Estás seguro de que deseas eliminar a
                <strong class="font-semibold text-gray-900 dark:text-white">
                    {{ client?.company_name }}
                </strong>?
                Se perderá todo el acceso y la información vinculada.
            </p>
        </div>

        <template #footer>
            <div class="flex justify-end gap-3">
                <SecondaryButton type="button" @click="emit('close')">
                    Cancelar
                </SecondaryButton>

                <button
                    type="button"
                    :disabled="form.processing"
                    @click="destroy"
                    class="inline-flex items-center gap-2 rounded-2xl bg-red-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-red-500/25 transition hover:bg-red-600 active:scale-95 disabled:opacity-50"
                >
                    <svg
                        v-if="form.processing"
                        class="h-4 w-4 animate-spin text-white"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>{{ form.processing ? 'Eliminando...' : 'Eliminar cliente' }}</span>
                </button>
            </div>
        </template>
    </Modal>
</template>