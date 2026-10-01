<!-- resources/js/Components/UI/Forms/TextareaField.vue -->
<script setup>
defineOptions({
    inheritAttrs: false,
})

defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    rows: {
        type: [Number, String],
        default: 4,
    },
    placeholder: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    hasError: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['update:modelValue', 'focus', 'blur'])
</script>

<template>
    <textarea
        v-bind="$attrs"
        :rows="rows"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        @input="emit('update:modelValue', $event.target.value)"
        @focus="emit('focus', $event)"
        @blur="emit('blur', $event)"
        class="w-full rounded-2xl border px-5 py-3.5 text-sm outline-none
                transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]
                disabled:cursor-not-allowed disabled:opacity-50 resize-y"
        :class="[
            !hasError
                ?   [
                        'bg-black/[0.02] text-gray-900 placeholder-gray-400 dark:bg-white/[0.04] dark:text-white dark:placeholder-gray-500',
                        'border-black/5 dark:border-white/10',
                        'hover:bg-black/[0.04] dark:hover:bg-white/[0.06]',
                        'focus:scale-[1.005] focus:bg-white focus:border-[#007AFF]/40 dark:focus:bg-white/[0.08]',
                        'focus:shadow-[0_0_0_1px_rgba(0,122,255,0.25),0_12px_30px_rgba(0,122,255,0.12)]'
                    ]
                :   [
                        'bg-red-500/[0.03] text-gray-900 placeholder-gray-400 dark:bg-red-500/[0.05] dark:text-white',
                        'border-red-500/50',
                        'focus:scale-[1.005] focus:border-red-500 focus:bg-white dark:focus:bg-white/[0.08]',
                        'focus:shadow-[0_0_0_1px_rgba(239,68,68,0.25),0_12px_30px_rgba(239,68,68,0.12)]'
                    ]
        ]"
    />
</template>