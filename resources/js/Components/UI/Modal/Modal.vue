<!-- resources/js/Components/UI/Modal/Modal.vue -->
<script setup>
import { TransitionRoot, TransitionChild, Dialog, DialogPanel } from '@headlessui/vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: '',
    },
    subtitle: {
        type: String,
        default: '',
    },
    maxWidth: {
        type: String,
        default: 'xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
})

const emit = defineEmits(['close'])

const close = () => {
    if (props.closeable) {
        emit('close')
    }
}

const sizes = {
    sm: 'max-w-md',
    md: 'max-w-lg',
    lg: 'max-w-xl',
    xl: 'max-w-2xl',
    '2xl': 'max-w-3xl',
    '3xl': 'max-w-5xl',
}
</script>

<template>
    <TransitionRoot :show="show" appear as="template">
        <Dialog as="div" class="relative z-50" @close="close">
            
            <!-- BACKDROP: Animamos SOLO la opacidad. 
                 El backdrop-blur se mantiene fijo y forzado a la GPU con [transform:translateZ(0)] 
                 para evitar que el navegador lo apague durante las animaciones. -->
            <TransitionChild
                as="template"
                enter="transition-opacity duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="transition-opacity duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div 
                    class="fixed inset-0 bg-black/40 dark:bg-black/70 backdrop-blur-md [transform:translateZ(0)] [will-change:opacity]" 
                />
            </TransitionChild>

            <!-- Contenedor Centrado -->
            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 sm:p-6">
                    <!-- PANEL DEL MODAL: Eliminamos el 'transform-gpu' para evitar que solicite 
                         redibujado del plano de fondo mientras escala -->
                    <TransitionChild
                        as="template"
                        enter="transition duration-300 ease-out"
                        enter-from="opacity-0 scale-95 translate-y-4"
                        enter-to="opacity-100 scale-100 translate-y-0"
                        leave="transition duration-200 ease-in"
                        leave-from="opacity-100 scale-100 translate-y-0"
                        leave-to="opacity-0 scale-95 translate-y-2"
                    >
                        <DialogPanel
                            :class="[
                                'relative w-full overflow-hidden p-6 text-left sm:p-8',
                                'rounded-[30px]',
                                'border border-black/10 dark:border-white/10',
                                'bg-white/95 dark:bg-[#1c1c1e]/95',
                                'shadow-[0_25px_80px_rgba(0,0,0,0.25)] dark:shadow-[0_40px_120px_rgba(0,0,0,0.8)]',
                                sizes[maxWidth] || sizes['xl']
                            ]"
                        >
                            <!-- Botón de Cierre -->
                            <button
                                v-if="closeable"
                                type="button"
                                @click="close"
                                class="absolute right-6 top-6 flex h-8 w-8 items-center justify-center rounded-full bg-black/5 text-gray-500 transition hover:bg-black/10 hover:text-gray-800 dark:bg-white/10 dark:text-gray-400 dark:hover:bg-white/20 dark:hover:text-white z-10"
                            >
                                <svg class="h-4 w-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>

                            <!-- Header -->
                            <div v-if="title || $slots.header" class="mb-6 pr-10">
                                <slot name="header">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ title }}
                                    </h3>
                                    <p v-if="subtitle" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        {{ subtitle }}
                                    </p>
                                </slot>
                            </div>

                            <!-- Slot Principal -->
                            <div class="relative">
                                <slot />
                            </div>

                            <!-- Slot Footer -->
                            <div v-if="$slots.footer" class="mt-8 flex items-center justify-end gap-3">
                                <slot name="footer" />
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>