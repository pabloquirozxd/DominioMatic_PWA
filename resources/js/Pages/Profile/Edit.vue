<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import DeleteUserForm from './Partials/DeleteUserForm.vue'
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue'
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue'
import { Head } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'

defineOptions({
    layout: AuthenticatedLayout,
});

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
})


const currentTheme = ref('system')

onMounted(() => {
    currentTheme.value = localStorage.getItem('theme') || 'system'
})

function setTheme(theme) {
    currentTheme.value = theme
    localStorage.setItem('theme', theme)
    applyTheme(theme)
}

function applyTheme(theme) {
    const html = document.documentElement

    if (theme === 'dark') {
        html.classList.add('dark')
    } else if (theme === 'light') {
        html.classList.remove('dark')
    } else {
        if (
            window.matchMedia('(prefers-color-scheme: dark)').matches
        ) {
            html.classList.add('dark')
        } else {
            html.classList.remove('dark')
        }
    }
}
</script>

<template>
    <Head title="Perfil | DominioMatic" />

    <div
        class="min-h-screen overflow-x-hidden bg-[#f5f5f7] text-slate-950 transition-colors dark:bg-[#050507] dark:text-white"
    >
        <!-- AMBIENT BACKGROUND — DominioMatic brand gradient -->
        <div
            class="pointer-events-none fixed inset-0 overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="absolute -bottom-56 -left-56 h-[44rem] w-[44rem] rounded-full opacity-[0.14] blur-[130px] dark:opacity-[0.24]"
                style="background: radial-gradient(circle, #002B48 0%, transparent 70%)"
            ></div>

            <div
                class="absolute -right-48 -top-48 h-[42rem] w-[42rem] rounded-full opacity-[0.16] blur-[130px] dark:opacity-[0.26]"
                style="background: radial-gradient(circle, #0072A8 0%, transparent 70%)"
            ></div>

            <div
                class="absolute -left-40 top-[18%] h-[38rem] w-[38rem] rounded-full opacity-[0.12] blur-[120px] dark:opacity-[0.20]"
                style="background: radial-gradient(circle, #21B24B 0%, transparent 70%)"
            ></div>

            <div
                class="absolute -right-40 bottom-[8%] h-[40rem] w-[40rem] rounded-full opacity-[0.10] blur-[130px] dark:opacity-[0.18]"
                style="background: radial-gradient(circle, #005E2B 0%, transparent 70%)"
            ></div>

            <div
                class="absolute left-1/2 top-1/3 h-[36rem] w-[36rem] -translate-x-1/2 rounded-full opacity-[0.55] blur-[140px] dark:opacity-[0.06]"
                style="background: radial-gradient(circle, #FFFFFF 0%, transparent 65%)"
            ></div>
        </div>

        <div
            class="relative mx-auto max-w-3xl px-4 pb-20 pt-5 sm:px-6 lg:px-8"
        >
            <!-- HEADER -->
            <header class="mb-8">
                <h1
                    class="text-3xl font-semibold tracking-[-0.045em] text-slate-950 sm:text-4xl dark:text-white"
                >
                    Perfil
                </h1>

                <p
                    class="mt-2 max-w-xl text-sm text-slate-500 dark:text-slate-400"
                >
                    Gestiona tu información personal, la seguridad de
                    tu cuenta y la apariencia del sistema.
                </p>
            </header>

            <div class="space-y-5">
                <!-- CARD: Info del perfil -->
                <div
                    class="rounded-[24px] border border-white/65 bg-white/35 p-6 shadow-[0_14px_40px_rgba(15,23,42,0.04),inset_0_1px_2px_rgba(255,255,255,0.85),inset_0_-2px_5px_rgba(0,0,0,0.06)] backdrop-blur-xl dark:border-white/[0.08] dark:bg-[#101014]/65 dark:shadow-[0_14px_40px_rgba(0,0,0,0.25),inset_0_1px_2px_rgba(255,255,255,0.16),inset_0_-2px_6px_rgba(0,0,0,0.5)] sm:p-7"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </div>

                <!-- CARD: Contraseña -->
                <div
                    class="rounded-[24px] border border-white/65 bg-white/35 p-6 shadow-[0_14px_40px_rgba(15,23,42,0.04),inset_0_1px_2px_rgba(255,255,255,0.85),inset_0_-2px_5px_rgba(0,0,0,0.06)] backdrop-blur-xl dark:border-white/[0.08] dark:bg-[#101014]/65 dark:shadow-[0_14px_40px_rgba(0,0,0,0.25),inset_0_1px_2px_rgba(255,255,255,0.16),inset_0_-2px_6px_rgba(0,0,0,0.5)] sm:p-7"
                >
                    <UpdatePasswordForm />
                </div>

                <!-- CARD: Apariencia -->
                <div
                    class="rounded-[24px] border border-white/65 bg-white/35 p-6 shadow-[0_14px_40px_rgba(15,23,42,0.04),inset_0_1px_2px_rgba(255,255,255,0.85),inset_0_-2px_5px_rgba(0,0,0,0.06)] backdrop-blur-xl dark:border-white/[0.08] dark:bg-[#101014]/65 dark:shadow-[0_14px_40px_rgba(0,0,0,0.25),inset_0_1px_2px_rgba(255,255,255,0.16),inset_0_-2px_6px_rgba(0,0,0,0.5)] sm:p-7"
                >
                    <header>
                        <h2
                            class="text-lg font-semibold tracking-[-0.02em] text-slate-950 dark:text-white"
                        >
                            Apariencia
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Personaliza la apariencia del sistema.
                        </p>
                    </header>

                    <div class="mt-6 flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="rounded-full border px-5 py-2.5 text-xs font-semibold transition-all duration-300 active:scale-95"
                            :class="
                                currentTheme === 'light'
                                    ? 'border-transparent bg-[#007AFF] text-white shadow-[0_0_0_1px_rgba(0,122,255,0.4),0_10px_30px_rgba(0,122,255,0.55),0_4px_12px_rgba(0,122,255,0.4)]'
                                    : 'border-white/65 bg-white/45 text-slate-600 hover:bg-white/70 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-slate-300 dark:hover:bg-white/[0.08]'
                            "
                            @click="setTheme('light')"
                        >
                            Light
                        </button>

                        <button
                            type="button"
                            class="rounded-full border px-5 py-2.5 text-xs font-semibold transition-all duration-300 active:scale-95"
                            :class="
                                currentTheme === 'dark'
                                    ? 'border-transparent bg-[#007AFF] text-white shadow-[0_0_0_1px_rgba(0,122,255,0.4),0_10px_30px_rgba(0,122,255,0.55),0_4px_12px_rgba(0,122,255,0.4)]'
                                    : 'border-white/65 bg-white/45 text-slate-600 hover:bg-white/70 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-slate-300 dark:hover:bg-white/[0.08]'
                            "
                            @click="setTheme('dark')"
                        >
                            Dark
                        </button>

                        <button
                            type="button"
                            class="rounded-full border px-5 py-2.5 text-xs font-semibold transition-all duration-300 active:scale-95"
                            :class="
                                currentTheme === 'system'
                                    ? 'border-transparent bg-[#007AFF] text-white shadow-[0_0_0_1px_rgba(0,122,255,0.4),0_10px_30px_rgba(0,122,255,0.55),0_4px_12px_rgba(0,122,255,0.4)]'
                                    : 'border-white/65 bg-white/45 text-slate-600 hover:bg-white/70 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-slate-300 dark:hover:bg-white/[0.08]'
                            "
                            @click="setTheme('system')"
                        >
                            System
                        </button>
                    </div>
                </div>

                <!-- CARD: Eliminar cuenta (zona de peligro) -->
                <div
                    class="rounded-[24px] border border-rose-200/60 bg-rose-50/30 p-6 shadow-[0_14px_40px_rgba(244,63,94,0.04),inset_0_1px_2px_rgba(255,255,255,0.85)] backdrop-blur-xl dark:border-rose-400/[0.12] dark:bg-rose-500/[0.03] dark:shadow-[0_14px_40px_rgba(0,0,0,0.25),inset_0_1px_2px_rgba(255,255,255,0.06)] sm:p-7"
                >
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </div>
</template>