<!-- resources/js/Pages/Clients/Components/CreateClientModal.vue -->
<script setup>
import Modal from '@/Components/UI/Modal/Modal.vue'
import ClientForm from './ClientForm.vue'

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['close'])
</script>

<template>
    <Modal :show="show" max-width="xl" @close="emit('close')">
        <!-- Header estilo Apple -->
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Nuevo cliente
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Registra una empresa o un cliente individual.
                </p>
            </div>
        </template>

        <!-- Formulario desacoplado -->
        <ClientForm @submitted="emit('close')" @cancel="emit('close')" />
    </Modal>
</template>