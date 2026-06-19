<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="w-full rounded-2xl border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm transition-all duration-200 focus:border-gray-400 focus:ring-0 dark:border-white/5 dark:bg-[#161619] dark:text-white dark:placeholder-gray-500 dark:focus:border-white/20 dark:focus:bg-[#1b1b1f]"
        v-model="model"
        ref="input"
    />
</template>