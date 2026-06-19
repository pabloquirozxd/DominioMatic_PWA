<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: String,
    active: Boolean,
});

const classes = computed(() =>
    props.active
        ? `
        inline-flex items-center
        px-4 py-2
        rounded-xl
        text-sm font-semibold

        bg-gradient-to-r
        from-[#0066cc]
        via-[#c600bf]
        to-[#ff2d55]

        text-white
        shadow-lg
        shadow-fuchsia-500/20

        transition-all
        duration-300
        `
        : `
        inline-flex items-center
        px-4 py-2
        rounded-xl

        text-sm font-medium

        text-gray-600
        dark:text-gray-300

        hover:bg-black/5
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