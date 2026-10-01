<!-- resources/js/Components/UI/Modal/CloseButton.vue -->
<script setup>
import { XMarkIcon } from '@heroicons/vue/24/outline'

defineEmits(['click'])
</script>

<template>
    <button
        type="button"
        @click="$emit('click')"
        class="inline-flex h-8 w-8 items-center justify-center rounded-full
                bg-black/[0.05] text-gray-500 backdrop-blur-md
                transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]
                hover:bg-black/10 hover:text-gray-900 hover:scale-105
                dark:bg-white/[0.08] dark:text-gray-400 dark:hover:bg-white/[0.15] dark:hover:text-white
                active:scale-95"
    >
        <XMarkIcon class="h-4 w-4" />
    </button>
</template>