<!-- resources/js/Components/UI/LiquidToggle.vue -->
<script setup>
import { ref } from 'vue'

const props = defineProps({
    modelValue: {
        type: Boolean,
        required: true
    }
})

const emit = defineEmits(['update:modelValue'])

const toggleRef = ref(null)
const isPressed = ref(false)
let startX = 0
let hasDragged = false

// Inicia la interacción (convierte a cristal líquido y estira)
const handlePointerDown = (e) => {
    isPressed.value = true
    hasDragged = false
    startX = e.clientX || (e.touches && e.touches[0].clientX)
    
    window.addEventListener('pointerup', handlePointerUp)
    window.addEventListener('pointermove', handlePointerMove)
}

// Lógica de arrastre
const handlePointerMove = (e) => {
    if (!isPressed.value) return
    
    const currentX = e.clientX || (e.touches && e.touches[0].clientX)
    
    // Si se mueve más de 4px, se considera arrastre, no click
    if (Math.abs(currentX - startX) > 4) {
        hasDragged = true
    }
    
    if (!toggleRef.value) return
    const rect = toggleRef.value.getBoundingClientRect()
    const x = currentX - rect.left
    const percentage = x / rect.width

    // Si arrastra más allá de la mitad, cambiamos el estado visualmente
    if (props.modelValue === false && percentage > 0.55) {
        emit('update:modelValue', true)
    } else if (props.modelValue === true && percentage < 0.45) {
        emit('update:modelValue', false)
    }
}

// Suelta el click/arrastre (vuelve a estado sólido)
const handlePointerUp = () => {
    isPressed.value = false
    window.removeEventListener('pointerup', handlePointerUp)
    window.removeEventListener('pointermove', handlePointerMove)
    
    // Si fue un click rápido (sin arrastrar), alternamos el estado
    if (!hasDragged) {
        emit('update:modelValue', !props.modelValue)
    }
}
</script>

<template>
    <div
        ref="toggleRef"
        @pointerdown.prevent="handlePointerDown"
        class="relative flex h-8 w-14 cursor-pointer select-none items-center rounded-full transition-colors duration-500 ease-in-out"
        :class="modelValue ? 'bg-[#007AFF]' : 'bg-gray-300 dark:bg-gray-600'"
    >
        <!-- 
            Píldora / Gota
            La magia ocurre en el cruce de las clases de reposo y presionado.
            Usamos 'w-7' (sólido) y 'w-11' (estirado).
            'translate-x' define la posición y se ajusta para compensar el estiramiento.
        -->
        <div
            class="absolute top-[2px] h-7 rounded-full transition-all duration-400 ease-[cubic-bezier(0.25,1,0.5,1)]"
            :class="[
                // --- ESTADO REPOSO (Sólido) ---
                !isPressed 
                    ? 'bg-white shadow-[0_2px_5px_rgba(0,0,0,0.2)] dark:bg-gray-100' 
                    : '',
                    
                // --- ESTADO PRESIONADO/ARRASTRE (Liquid Glass de la imagen) ---
                isPressed 
                    ? 'bg-white/40 dark:bg-white/30 backdrop-blur-md backdrop-saturate-150 border border-white/60 dark:border-white/30 shadow-[inset_0_4px_6px_rgba(0,0,0,0.2),inset_0_-4px_6px_rgba(0,0,0,0.2),0_2px_10px_rgba(0,0,0,0.1)]' 
                    : '',

                // --- POSICIONES Y ESTIRAMIENTO ---
                // OFF + Reposo
                !modelValue && !isPressed ? 'w-7 translate-x-[2px]' : '',
                // OFF + Presionado (Se estira hacia la derecha, ancla izquierda)
                !modelValue && isPressed  ? 'w-11 translate-x-[2px]' : '',
                // ON + Reposo
                modelValue && !isPressed  ? 'w-7 translate-x-[26px]' : '',
                // ON + Presionado (Se estira hacia la izquierda, ancla derecha)
                modelValue && isPressed   ? 'w-11 translate-x-[10px]' : ''
            ]"
        >
            <!-- Brillo superior (Reflejo especular visible en modo Liquid Glass) -->
            <div 
                v-if="isPressed" 
                class="absolute inset-x-2 top-0.5 h-1 rounded-full bg-gradient-to-b from-white/80 to-transparent transition-opacity duration-300"
            />
        </div>
    </div>
</template>