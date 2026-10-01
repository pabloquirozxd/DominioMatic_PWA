<!-- resources/js/Components/UI/Buttons/GhostButton.vue -->
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
})
</script>

<template>
    <button
        :type="type"
        :disabled="disabled"
        class="inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-semibold
                text-gray-600 dark:text-gray-400
                transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]
                hover:bg-black/[0.04] hover:text-gray-900 dark:hover:bg-white/[0.06] dark:hover:text-white
                active:scale-95 disabled:pointer-events-none disabled:opacity-50"
    >
        <slot />
    </button>
</template>