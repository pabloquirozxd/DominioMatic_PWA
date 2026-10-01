<!-- resources/js/Components/UI/Buttons/IconButton.vue -->
<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    variant: {
        type: String,
        default: 'ghost', // 'ghost' | 'glass'
    },
})
</script>

<template>
    <button
        :type="type"
        :disabled="disabled"
        class="inline-flex h-10 w-10 items-center justify-center rounded-2xl
                transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]
                active:scale-90 disabled:pointer-events-none disabled:opacity-50"
        :class="[
            variant === 'glass'
                ? 'bg-black/[0.03] text-gray-700 dark:bg-white/[0.05] dark:text-gray-200 border border-black/5 dark:border-white/10 hover:bg-black/[0.06] dark:hover:bg-white/[0.1]'
                : 'text-gray-500 hover:bg-black/[0.04] hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/[0.06] dark:hover:text-white'
        ]"
    >
        <slot />
    </button>
</template>