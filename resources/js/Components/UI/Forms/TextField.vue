<!-- resources/js/Components/UI/Forms/TextField.vue -->
<script setup>
import { ref } from 'vue'
import BaseInput from './BaseInput.vue'

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    placeholder: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    readonly: {
        type: Boolean,
        default: false,
    },
    autofocus: {
        type: Boolean,
        default: false,
    },
    hasError: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['update:modelValue', 'focus', 'blur'])

const baseInputRef = ref(null)

const focus = () => baseInputRef.value?.focus()

defineExpose({ focus })
</script>

<template>
    <BaseInput
        ref="baseInputRef"
        type="text"
        :model-value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :autofocus="autofocus"
        :has-error="hasError"
        @update:model-value="emit('update:modelValue', $event)"
        @focus="emit('focus', $event)"
        @blur="emit('blur', $event)"
    />
</template>