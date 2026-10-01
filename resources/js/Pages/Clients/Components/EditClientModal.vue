<!-- resources/js/Pages/Clients/Components/EditClientModal.vue -->
<script setup>
import Modal from '@/Components/UI/Modal/Modal.vue'
import ClientForm from './ClientForm.vue'

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    client: {
        type: Object,
        default: null,
    },
})

const emit = defineEmits(['close', 'success'])

const handleSuccess = () => {
    emit('success')
    emit('close')
}
</script>

<template>
    <Modal :show="show" max-width="2xl" @close="emit('close')">
        <!-- Header del Modal -->
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Editar cliente
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Modifica los datos de la empresa o cliente individual.
                </p>
            </div>
        </template>

        <!-- Formulario (Escucha tanto 'submitted' como 'success') -->
        <ClientForm
            v-if="show && client"
            :key="client.id"
            :client="client"
            @submitted="handleSuccess"
            @success="handleSuccess"
            @cancel="emit('close')"
        />
    </Modal>
</template>