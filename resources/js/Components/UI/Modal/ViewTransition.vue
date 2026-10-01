<!-- resources/js/Components/UI/Modal/ViewTransition.vue -->
<script setup>
defineProps({
    // 'forward' desliza de derecha a izquierda (siguiente vista)
    // 'back' desliza de izquierda a derecha (volver)
    direction: {
        type: String,
        default: 'forward',
    },
})
</script>

<template>
    <Transition
        mode="out-in"
        :enter-active-class="`transition-all duration-300 [transition-timing-function:cubic-bezier(0.32,0.72,0,1)] [will-change:transform,opacity]`"
        :enter-from-class="direction === 'forward' ? 'opacity-0 translate-x-6' : 'opacity-0 -translate-x-6'"
        enter-to-class="opacity-100 translate-x-0"
        :leave-active-class="`transition-all duration-200 [transition-timing-function:cubic-bezier(0.32,0.72,0,1)] [will-change:transform,opacity]`"
        leave-from-class="opacity-100 translate-x-0"
        :leave-to-class="direction === 'forward' ? 'opacity-0 -translate-x-6' : 'opacity-0 translate-x-6'"
    >
        <slot />
    </Transition>
</template>