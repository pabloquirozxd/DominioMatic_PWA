<script setup>
import {
    UsersIcon,
    StarIcon,
    UserGroupIcon,
} from '@heroicons/vue/24/outline'

defineProps({
    stats: {
        type: Object,
        required: true,
    },
})
</script>

<template>
    <div class="grid gap-4 md:grid-cols-3">
        <!-- Total -->
        <div
            class="flex items-center justify-between rounded-[24px] border border-white/65 bg-white/35 p-5 shadow-[0_14px_40px_rgba(15,23,42,0.04),inset_0_1px_2px_rgba(255,255,255,0.85),inset_0_-2px_5px_rgba(0,0,0,0.06)] backdrop-blur-xl dark:border-white/[0.08] dark:bg-[#101014]/65 dark:shadow-[0_14px_40px_rgba(0,0,0,0.25),inset_0_1px_2px_rgba(255,255,255,0.16),inset_0_-2px_6px_rgba(0,0,0,0.5)]"
        >
            <div>
                <p
                    class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500"
                >
                    Total Contactos
                </p>

                <p
                    class="mt-1.5 text-3xl font-semibold tracking-[-0.04em] text-slate-950 dark:text-white"
                >
                    {{ stats.total }}
                </p>
            </div>

            <div
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#0072A8]/10 text-[#0072A8] dark:bg-[#0072A8]/20 dark:text-[#4FC3F7]"
            >
                <UsersIcon class="h-[18px] w-[18px] stroke-[1.8]" />
            </div>
        </div>

        <!-- Principales -->
        <div
            class="flex items-center justify-between rounded-[24px] border border-white/65 bg-white/35 p-5 shadow-[0_14px_40px_rgba(15,23,42,0.04),inset_0_1px_2px_rgba(255,255,255,0.85),inset_0_-2px_5px_rgba(0,0,0,0.06)] backdrop-blur-xl dark:border-white/[0.08] dark:bg-[#101014]/65 dark:shadow-[0_14px_40px_rgba(0,0,0,0.25),inset_0_1px_2px_rgba(255,255,255,0.16),inset_0_-2px_6px_rgba(0,0,0,0.5)]"
        >
            <div>
                <p
                    class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500"
                >
                    Contactos Principales
                </p>

                <p
                    class="mt-1.5 text-3xl font-semibold tracking-[-0.04em] text-slate-950 dark:text-white"
                >
                    {{ stats.primary }}
                </p>
            </div>

            <div
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#21B24B]/10 text-[#21B24B] dark:bg-[#21B24B]/20 dark:text-[#4ADE80]"
            >
                <StarIcon class="h-[18px] w-[18px] stroke-[1.8]" />
            </div>
        </div>

        <!-- Secundarios -->
        <div
            class="flex items-center justify-between rounded-[24px] border border-white/65 bg-white/35 p-5 shadow-[0_14px_40px_rgba(15,23,42,0.04),inset_0_1px_2px_rgba(255,255,255,0.85),inset_0_-2px_5px_rgba(0,0,0,0.06)] backdrop-blur-xl dark:border-white/[0.08] dark:bg-[#101014]/65 dark:shadow-[0_14px_40px_rgba(0,0,0,0.25),inset_0_1px_2px_rgba(255,255,255,0.16),inset_0_-2px_6px_rgba(0,0,0,0.5)]"
        >
            <div>
                <p
                    class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500"
                >
                    Contactos Secundarios
                </p>

                <p
                    class="mt-1.5 text-3xl font-semibold tracking-[-0.04em] text-slate-950 dark:text-white"
                >
                    {{ stats.secondary }}
                </p>
            </div>

            <div
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-500/10 text-slate-500 dark:bg-white/[0.06] dark:text-slate-400"
            >
                <UserGroupIcon class="h-[18px] w-[18px] stroke-[1.8]" />
            </div>
        </div>
    </div>
</template>