<!-- resources/js/Components/UI/Forms/FormField.vue -->
<script setup>
defineProps({
    for: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        default: '',
    },
    icon: {
        type: [Object, Function],
        default: null,
    },
    required: {
        type: Boolean,
        default: false,
    },
    hint: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
})
</script>

<template>
    <div class="w-full space-y-1.5">
        <!-- Header: Label + Icono + Asterisco -->
        <div v-if="label || icon" class="flex items-center gap-2">
            <component
                v-if="icon"
                :is="icon"
                class="h-4 w-4 text-gray-400 dark:text-gray-500"
            />
            <label
                v-if="label"
                :for="for"
                class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400"
            >
                {{ label }}
                <span v-if="required" class="ml-0.5 text-[#0066cc] dark:text-[#7ab7ff]">*</span>
            </label>
        </div>

        <!-- Slot para TextField, Select, Textarea, etc. -->
        <div>
            <slot />
        </div>

        <!-- Texto de ayuda (Hint) -->
        <p v-if="hint && !error" class="text-xs text-gray-500 dark:text-gray-400">
            {{ hint }}
        </p>

        <!-- Mensaje de Error Animado -->
        <Transition
            enter-active-class="transition duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-1"
        >
            <p v-if="error" class="text-xs font-medium text-red-500 dark:text-red-400">
                {{ error }}
            </p>
        </Transition>
    </div>
</template>