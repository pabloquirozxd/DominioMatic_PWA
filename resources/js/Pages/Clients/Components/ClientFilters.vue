<script setup>
defineProps({
    search: {
        type: String,
        default: '',
    },
})

defineEmits(['update:search'])
</script>

<template>
    <div class="flex items-center gap-4">
        <div class="relative flex-1">
            <input
                :value="search"
                @input="$emit('update:search', $event.target.value)"
                type="text"
                placeholder="Buscar clientes..."
                class="w-full rounded-xl border border-black/10 bg-white/5 px-4 py-2 text-sm text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none dark:border-white/10 dark:text-white dark:placeholder-gray-400"
            />
        </div>
    </div>
</template>