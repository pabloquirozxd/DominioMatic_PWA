<!-- resources/js/Components/UI/SegmentedControl.vue -->
<script setup>
import { ref, computed, watch, nextTick } from 'vue'

const props = defineProps({
    modelValue: {
        type: [String, Number],
        required: true,
    },
    options: {
        type: Array,
        required: true,
    },
})

const emit = defineEmits(['update:modelValue'])

const containerRef = ref(null)
const isDragging = ref(false)
const isWobbling = ref(false)

const activeIndex = computed(() => {
    const idx = props.options.findIndex((opt) => opt.value === props.modelValue)
    return idx !== -1 ? idx : 0
})

// Disparar el bamboleo de agua al cambiar de opción
watch(activeIndex, () => {
    triggerWobble()
})

const triggerWobble = () => {
    isWobbling.value = false
    nextTick(() => {
        isWobbling.value = true
    })
}

const selectOption = (val) => {
    emit('update:modelValue', val)
}

// Lógica de arrastre suave
const handleMouseDown = () => {
    isDragging.value = true
}

const handleMouseMove = (e) => {
    if (!isDragging.value || !containerRef.value) return

    const rect = containerRef.value.getBoundingClientRect()
    const x = e.clientX - rect.left
    const percentage = Math.max(0, Math.min(1, x / rect.width))
    const index = Math.floor(percentage * props.options.length)

    if (props.options[index] && props.options[index].value !== props.modelValue) {
        emit('update:modelValue', props.options[index].value)
    }
}

const handleMouseUp = () => {
    isDragging.value = false
}
</script>

<template>
    <!-- Canaleta Base (Track) -->
    <div
        ref="containerRef"
        @mousedown="handleMouseDown"
        @mousemove="handleMouseMove"
        @mouseup="handleMouseUp"
        @mouseleave="handleMouseUp"
        class="relative flex w-full select-none items-center rounded-2xl p-1.5 transition-colors duration-300
               bg-black/[0.04] dark:bg-white/[0.02]
               border border-black/5 dark:border-white/[0.06]
               backdrop-blur-xl"
    >
        <!-- CAPA 1: Desplazamiento X Lento y Suave -->
        <div
            class="absolute top-1.5 bottom-1.5 pointer-events-none transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)]"
            :style="{
                width: `calc((100% - 0.75rem) / ${options.length})`,
                transform: `translateX(calc(${activeIndex} * 100%))`
            }"
        >
            <!-- CAPA 2: Lente de Agua Ultra-Transparente (Casi invisible) -->
            <div
                class="h-full w-full rounded-xl transition-all
                       bg-white/40 dark:bg-white/[0.08]
                       backdrop-blur-2xl
                       border border-white/60 dark:border-white/10
                       shadow-[0_2px_12px_rgba(0,0,0,0.04)] dark:shadow-[0_4px_16px_rgba(0,0,0,0.3)]"
                :class="[
                    isWobbling ? 'animate-water-lens' : '',
                    isDragging ? 'scale-x-[1.02] scale-y-[0.98]' : ''
                ]"
                @animationend="isWobbling = false"
            />
        </div>

        <!-- Opciones (Texto e Iconos) -->
        <button
            v-for="option in options"
            :key="option.value"
            type="button"
            @click="selectOption(option.value)"
            class="relative z-10 flex flex-1 items-center justify-center gap-2.5 py-2.5 text-sm font-medium transition-colors duration-300"
            :class="[
                modelValue === option.value
                    ? 'text-gray-950 font-semibold dark:text-white'
                    : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200',
            ]"
        >
            <component
                v-if="option.icon"
                :is="option.icon"
                class="h-4 w-4 transition-transform duration-300"
                :class="{ 'scale-105': modelValue === option.value }"
            />
            <span>{{ option.label }}</span>
        </button>
    </div>
</template>

<style scoped>
/* Deformación elástica de tensión superficial de agua (Lenta y sutil) */
@keyframes waterLens {
    0% {
        transform: scale(1, 1);
    }
    35% {
        transform: scale(1.035, 0.965); /* Leve extensión horizontal al deslizar */
    }
    70% {
        transform: scale(0.985, 1.015); /* Micro contracción de frenado */
    }
    100% {
        transform: scale(1, 1);
    }
}

.animate-water-lens {
    animation: waterLens 0.85s cubic-bezier(0.2, 0.8, 0.2, 1);
}
</style>