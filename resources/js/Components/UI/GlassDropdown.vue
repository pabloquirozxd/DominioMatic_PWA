<!-- resources/js/Components/UI/GlassDropdown.vue -->
<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    align: {
        type: String,
        default: 'right', // 'left' | 'right'
    },
    width: {
        type: String,
        default: 'w-52',
    },
})

const isOpen = ref(false)
const dropdownRef = ref(null)

const toggle = () => {
    isOpen.value = !isOpen.value
}

const close = () => {
    isOpen.value = false
}

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        close()
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))
</script>

<template>
    <div ref="dropdownRef" class="relative inline-block text-left">
        <!-- Disparador (Trigger Slot) -->
        <div @click="toggle" class="cursor-pointer">
            <slot name="trigger" :isOpen="isOpen" />
        </div>

        <!-- Menú Desplegable con Animación Bubble Inflation -->
        <Transition
            enter-active-class="transition duration-400 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
            enter-from-class="transform scale-50 opacity-0 -translate-y-6 blur-lg"
            enter-to-class="transform scale-100 opacity-100 translate-y-0 blur-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="transform scale-100 opacity-100 translate-y-0 blur-0"
            leave-to-class="transform scale-75 opacity-0 -translate-y-4 blur-md"
        >
            <div
                v-if="isOpen"
                class="absolute z-50 mt-2 rounded-3xl p-1.5
                    bg-white/70 dark:bg-[#1c1c1e]/80
                    backdrop-blur-3xl backdrop-saturate-200
                    border border-white/80 dark:border-white/20
                    shadow-[0_20px_50px_rgba(0,0,0,0.25),inset_0_1px_2px_rgba(255,255,255,0.6)]
                    dark:shadow-[0_20px_50px_rgba(0,0,0,0.5),inset_0_1px_1px_rgba(255,255,255,0.2)]
                    origin-top"
                :class="[
                    align === 'right' ? 'right-0' : 'left-0',
                    width
                ]"
                @click="close"
            >
                <!-- Resplandor Iridiscente/Siri Lip Superior -->
                <div class="pointer-events-none absolute -top-px left-1/2 -translate-x-1/2 h-[2px] w-3/4 rounded-full bg-gradient-to-r from-transparent via-cyan-400/80 to-transparent blur-[1px]"></div>

                <div class="space-y-1">
                    <slot name="content" />
                </div>
            </div>
        </Transition>
    </div>
</template>