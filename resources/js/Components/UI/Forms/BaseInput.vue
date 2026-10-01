<!-- resources/js/Components/UI/Forms/BaseInput.vue -->
<script setup>
import { ref, onMounted } from 'vue'

defineOptions({
    inheritAttrs: false,
})

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    type: {
        type: String,
        default: 'text',
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

const inputRef = ref(null)

onMounted(() => {
    if (props.autofocus) {
        inputRef.value?.focus()
    }
})

const focus = () => inputRef.value?.focus()

defineExpose({ focus, inputRef })
</script>

<template>
    <div
        class="group relative flex w-full items-center rounded-2xl border transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]"
        :class="[
            !hasError
                ?   [
                        'bg-black/[0.02] dark:bg-white/[0.04]',
                        'border-black/5 dark:border-white/10',
                        'hover:bg-black/[0.04] dark:hover:bg-white/[0.06]',
                        'focus-within:scale-[1.01] focus-within:border-[#007AFF]/40 focus-within:bg-white dark:focus-within:bg-white/[0.08]',
                        'focus-within:shadow-[0_0_0_1px_rgba(0,122,255,0.25),0_12px_30px_rgba(0,122,255,0.12)]'
                    ]
                :   [
                        'bg-red-500/[0.03] dark:bg-red-500/[0.05]',
                        'border-red-500/50',
                        'focus-within:scale-[1.01] focus-within:border-red-500 focus-within:bg-white dark:focus-within:bg-white/[0.08]',
                        'focus-within:shadow-[0_0_0_1px_rgba(239,68,68,0.25),0_12px_30px_rgba(239,68,68,0.12)]'
                    ]
        ]"
    >
        <!-- Slot Leading (Ícono izquierdo) -->
        <div v-if="$slots.leading" class="pl-4 text-gray-400 dark:text-gray-500">
            <slot name="leading" />
        </div>

        <input
            ref="inputRef"
            v-bind="$attrs"
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
            @input="emit('update:modelValue', $event.target.value)"
            @focus="emit('focus', $event)"
            @blur="emit('blur', $event)"
            class="w-full rounded-2xl bg-transparent py-3.5 text-sm outline-none transition-colors
                text-gray-900 placeholder-gray-400 dark:text-white dark:placeholder-gray-500
                disabled:cursor-not-allowed disabled:opacity-50"
            :class="[
                $slots.leading ? 'pl-2.5' : 'pl-5',
                $slots.trailing ? 'pr-2.5' : 'pr-5',
            ]"
        />

        <!-- Slot Trailing (Ícono derecho/botón de limpiar) -->
        <div v-if="$slots.trailing" class="pr-4 text-gray-400 dark:text-gray-500">
            <slot name="trailing" />
        </div>
    </div>
</template>