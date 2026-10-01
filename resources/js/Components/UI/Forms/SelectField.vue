<!-- resources/js/Components/UI/Forms/SelectField.vue -->
<script setup>
import { ChevronDownIcon } from '@heroicons/vue/24/outline'

defineOptions({
    inheritAttrs: false,
})

const props = defineProps({
    modelValue: {
        type: [String, Number, Boolean],
        default: '',
    },
    options: {
        type: Array,
        default: () => [], // Formato: [{ label: 'Español', value: 'es' }] o ['Español', 'English']
    },
    placeholder: {
        type: String,
        default: 'Seleccionar...',
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
        <select
            v-bind="$attrs"
            :value="modelValue"
            :disabled="disabled"
            @change="emit('update:modelValue', $event.target.value)"
            @focus="emit('focus', $event)"
            @blur="emit('blur', $event)"
            class="w-full !appearance-none bg-none rounded-2xl bg-transparent py-3.5 pl-5 pr-12 text-sm outline-none transition-colors
                text-gray-900 dark:text-white disabled:cursor-not-allowed disabled:opacity-50 [&::-ms-expand]:hidden"
        >
            <option v-if="placeholder" value="" disabled selected class="dark:bg-[#1c1c1e]">
                {{ placeholder }}
            </option>
            <template v-for="(item, index) in options" :key="index">
                <option
                    :value="typeof item === 'object' ? item.value : item"
                    class="bg-white text-gray-900 dark:bg-[#1c1c1e] dark:text-white"
                >
                    {{ typeof item === 'object' ? item.label : item }}
                </option>
            </template>
        </select>

        <!-- Flecha estilo iOS -->
        <div class="pointer-events-none absolute right-4 text-gray-400 dark:text-gray-500">
            <ChevronDownIcon class="h-4 w-4" />
        </div>
    </div>
</template>