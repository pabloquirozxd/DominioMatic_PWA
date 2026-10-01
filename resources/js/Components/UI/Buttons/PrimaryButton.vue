<!-- resources/js/Components/UI/Buttons/PrimaryButton.vue -->
<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
    loading: {
        type: Boolean,
        default: false,
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
        :disabled="disabled || loading"
        class="relative inline-flex items-center justify-center rounded-2xl bg-[#007AFF] px-6 py-3.5 text-sm font-semibold text-white
            shadow-[0_4px_16px_rgba(0,122,255,0.3)]
            transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]
            hover:bg-[#0071E3] hover:shadow-[0_6px_20px_rgba(0,122,255,0.4)]
            active:scale-95 disabled:pointer-events-none disabled:opacity-50"
    >
        <!-- Spinner para estado de carga -->
        <svg
            v-if="loading"
            class="-ml-1 mr-2.5 h-4 w-4 animate-spin text-white"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>

        <slot />
    </button>
</template>