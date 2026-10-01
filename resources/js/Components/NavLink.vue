<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: String,
    active: Boolean,
});

const baseClasses = [
    'relative inline-flex items-center rounded-full px-4 py-2',
    'text-sm tracking-[-0.01em]',
    // Sin transition — el estado cambia al instante
    'outline-none focus:outline-none focus-visible:outline-none',
    'ring-0 focus:ring-0 focus-visible:ring-0',
].join(' ');

const activeClasses = [
    baseClasses,
    'font-semibold',
    'text-slate-900 dark:text-white',

    // Background
    'bg-gradient-to-b from-white/85 to-white/55',
    'dark:from-[#0c1016] dark:to-[#040608]',

    // Border
    'border border-[#0072A8]/15 dark:border-white/[0.06]',

    // Light-mode shadow stack
    'shadow-[inset_0_1px_0_rgba(255,255,255,0.95),inset_0_-1px_0_rgba(0,114,168,0.3),inset_0_-8px_16px_rgba(33,178,75,0.06),inset_0_4px_12px_rgba(0,114,168,0.07),0_4px_14px_rgba(15,23,42,0.06),0_1px_3px_rgba(15,23,42,0.04)]',

    // Dark-mode shadow stack
    'dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.14),inset_0_-1px_0_rgba(0,114,168,0.45),inset_0_-10px_20px_rgba(33,178,75,0.08),inset_0_6px_16px_rgba(0,114,168,0.14),0_6px_20px_rgba(0,0,0,0.35),0_2px_6px_rgba(0,0,0,0.25)]',

    // Iridescent line
    'after:content-[""] after:pointer-events-none after:absolute after:left-[15%] after:right-[15%] after:-bottom-px after:h-px after:rounded-full after:opacity-[0.85] after:blur-[0.4px]',
    'after:bg-[linear-gradient(90deg,transparent_0%,rgba(0,114,168,0.9)_25%,rgba(33,178,75,1)_50%,rgba(0,114,168,0.9)_75%,transparent_100%)]',
].join(' ');

const inactiveClasses = [
    baseClasses,
    'font-medium',
    'text-slate-500',
    'dark:text-slate-400',
    // Hover puede tener transición corta, el estado base no
    'hover:text-slate-900 hover:bg-black/[0.05] hover:transition-colors hover:duration-150',
    'dark:hover:text-white dark:hover:bg-white/[0.07]',
].join(' ');

const classes = computed(() =>
    props.active ? activeClasses : inactiveClasses
);
</script>

<template>
    <Link
        :href="href"
        :class="classes"
        prefetch
        style="outline: none;"
    >
        <slot />
    </Link>
</template>