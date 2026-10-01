<!-- resources/js/Components/UI/Buttons/SecondaryButton.vue -->
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
        class="relative inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-medium
            text-gray-800 dark:text-gray-100
            bg-white/40 dark:bg-white/10
            backdrop-blur-2xl backdrop-saturate-150
            border border-white/60 dark:border-white/15
            shadow-[0_4px_20px_rgba(0,0,0,0.03),inset_0_1px_1px_rgba(255,255,255,0.6)]
            dark:shadow-[0_4px_20px_rgba(0,0,0,0.2),inset_0_1px_1px_rgba(255,255,255,0.15)]
            hover:bg-white/60 dark:hover:bg-white/20
            hover:border-white/80 dark:hover:border-white/25
            hover:shadow-[0_6px_24px_rgba(0,0,0,0.06),inset_0_1px_1px_rgba(255,255,255,0.8)]
            active:scale-[0.97]
            transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]
            disabled:pointer-events-none disabled:opacity-40"
    >
        <slot />
    </button>
</template>