<!-- resources/js/Components/UI/Toast.vue -->
<script setup>
import { ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { CheckCircleIcon, XCircleIcon, InformationCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const page = usePage()
const visible = ref(false)
const message = ref('')
const type = ref('success') // 'success' | 'error' | 'info'
let timer = null

const showToast = (msg, toastType = 'success') => {
    message.value = msg
    type.value = toastType
    visible.value = true

    if (timer) clearTimeout(timer)
    timer = setTimeout(() => {
        visible.value = false
    }, 4000) // Auto oculta en 4s
}

// Escuchar mensajes Flash de Inertia / Laravel
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            showToast(flash.success, 'success')
        } else if (flash?.error) {
            showToast(flash.error, 'error')
        } else if (flash?.info) {
            showToast(flash.info, 'info')
        }
    },
    { deep: true, immediate: true }
)

defineExpose({ showToast })
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transform transition duration-300 ease-out"
            enter-from-class="-translate-y-4 opacity-0 scale-95"
            enter-to-class="translate-y-0 opacity-100 scale-100"
            leave-active-class="transform transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100 scale-100"
            leave-to-class="-translate-y-4 opacity-0 scale-95"
        >
            <div
                v-if="visible"
                class="fixed top-5 left-1/2 z-50 flex -translate-x-1/2 items-center gap-3 rounded-full border border-black/5 bg-white/80 px-5 py-3 shadow-[0_20px_50px_rgba(0,0,0,0.15)] backdrop-blur-2xl dark:border-white/10 dark:bg-[#1c1c1e]/80"
            >
                <!-- Iconos estilo Apple -->
                <CheckCircleIcon
                    v-if="type === 'success'"
                    class="h-5 w-5 text-[#34C759]"
                />
                <XCircleIcon
                    v-else-if="type === 'error'"
                    class="h-5 w-5 text-[#FF3B30]"
                />
                <InformationCircleIcon
                    v-else
                    class="h-5 w-5 text-[#007AFF]"
                />

                <!-- Mensaje -->
                <span class="text-xs font-semibold tracking-tight text-gray-900 dark:text-white sm:text-sm">
                    {{ message }}
                </span>

                <!-- Botón Cerrar -->
                <button
                    type="button"
                    @click="visible = false"
                    class="ml-1 rounded-full p-0.5 text-gray-400 transition hover:bg-black/5 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-200"
                >
                    <XMarkIcon class="h-4 w-4" />
                </button>
            </div>
        </Transition>
    </Teleport>
</template>