<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? `
        block w-full
        rounded-xl
        px-4 py-3

        bg-gradient-to-r
        from-[#0066cc]
        via-[#c600bf]
        to-[#ff2d55]

        text-white
        font-semibold
        `
        : `
        block w-full
        rounded-xl
        px-4 py-3

        text-gray-700
        dark:text-gray-300

        hover:bg-gray-100
        dark:hover:bg-white/10

        transition-all
        duration-300
        `
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
