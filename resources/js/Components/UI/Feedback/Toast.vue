<!-- resources/js/Components/UI/Feedback/Toast.vue -->
<script setup>
import { ref, watch, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import {
    CheckCircleIcon,
    ExclamationCircleIcon,
    InformationCircleIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline'

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
    }, 4000)
}

// Escuchar cambios en los flash messages de Inertia
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

onUnmounted(() => {
    if (timer) clearTimeout(timer)
})
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transform transition duration-300 ease-out"
            enter-from-class="translate-y-[-100%] opacity-0 scale-95"
            enter-to-class="translate-y-0 opacity-100 scale-100"
            leave-active-class="transform transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100 scale-100"
            leave-to-class="translate-y-[-50%] opacity-0 scale-95"
        >
            <div
                v-if="visible"
                class="fixed top-5 left-1/2 z-[100] -translate-x-1/2 pointer-events-auto"
            >
                <div
                    class="flex items-center gap-3 rounded-full border border-black/10 bg-white/80 px-4 py-2.5 shadow-[0_10px_30px_rgba(0,0,0,0.12)] backdrop-blur-xl dark:border-white/15 dark:bg-[#1c1c1e]/85 dark:shadow-[0_20px_50px_rgba(0,0,0,0.5)]"
                >
                    <!-- Ícono según estado -->
                    <div
                        :class="[
                            'flex h-6 w-6 items-center justify-center rounded-full',
                            type === 'success' ? 'text-[#30D158]' : '',
                            type === 'error' ? 'text-[#FF453A]' : '',
                            type === 'info' ? 'text-[#0A84FF]' : ''
                        ]"
                    >
                        <CheckCircleIcon v-if="type === 'success'" class="h-5 w-5 stroke-[2.2]" />
                        <ExclamationCircleIcon v-else-if="type === 'error'" class="h-5 w-5 stroke-[2.2]" />
                        <InformationCircleIcon v-else class="h-5 w-5 stroke-[2.2]" />
                    </div>

                    <!-- Mensaje -->
                    <p class="text-xs font-semibold text-gray-900 dark:text-white pr-1">
                        {{ message }}
                    </p>

                    <!-- Botón Cerrar -->
                    <button
                        type="button"
                        @click="visible = false"
                        class="flex h-5 w-5 items-center justify-center rounded-full text-gray-400 transition hover:bg-black/5 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-white/10 dark:hover:text-white"
                    >
                        <XMarkIcon class="h-3.5 w-3.5 stroke-[2.5]" />
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>