<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const currentTheme = ref('system');

onMounted(() => {
    currentTheme.value =
        localStorage.getItem('theme')
        || 'system';
});

function setTheme(theme) {
    currentTheme.value = theme;

    localStorage.setItem(
        'theme',
        theme
    );

    applyTheme(theme);
}

function applyTheme(theme) {
    const html = document.documentElement;
    if (theme === 'dark') {
        html.classList.add('dark');
    } else if (theme === 'light') {
        html.classList.remove('dark');
    } else {
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
    }
}
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold tracking-tight text-gray-800 dark:text-white">
                Perfil
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                
                <div class="bg-white p-4 shadow-sm sm:rounded-[2rem] sm:p-8 border border-gray-200/60 dark:bg-[#121214]/75 dark:backdrop-blur-[24px] dark:backdrop-saturate-[180%] dark:border-white/[0.12] dark:shadow-[0_30px_70px_rgba(0,0,0,0.85),inset_0_1px_1px_rgba(255,255,255,0.2)]">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="bg-white p-4 shadow-sm sm:rounded-[2rem] sm:p-8 border border-gray-200/60 dark:bg-[#121214]/75 dark:backdrop-blur-[24px] dark:backdrop-saturate-[180%] dark:border-white/[0.12] dark:shadow-[0_30px_70px_rgba(0,0,0,0.85),inset_0_1px_1px_rgba(255,255,255,0.2)]">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="bg-white p-4 shadow-sm sm:rounded-[2rem] sm:p-8 border border-gray-200/60 dark:bg-[#121214]/75 dark:backdrop-blur-[24px] dark:backdrop-saturate-[180%] dark:border-white/[0.12] dark:shadow-[0_30px_70px_rgba(0,0,0,0.85),inset_0_1px_1px_rgba(255,255,255,0.2)]">
                    <DeleteUserForm class="max-w-xl" />
                </div>

                <div class="bg-white p-6 shadow-sm rounded-[2rem] border border-gray-200/60 dark:bg-[#121214]/75 dark:backdrop-blur-[24px] dark:backdrop-saturate-[180%] dark:border-white/[0.12] dark:shadow-[0_30px_70px_rgba(0,0,0,0.85),inset_0_1px_1px_rgba(255,255,255,0.2)]">
                    <h3 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-white">
                        Apariencia
                    </h3>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Personaliza la apariencia del sistema.
                    </p>

                    <div class="mt-5 flex gap-3">

                        <button
                            @click="setTheme('light')"
                            :class="
                                currentTheme === 'light'
                                    ? 'bg-gradient-to-r from-[#0066cc] via-[#7c3aed] to-[#ff2d55] text-white shadow-lg shadow-purple-500/30'
                                    : 'bg-white dark:bg-[#1c1c1f] text-gray-600 dark:text-gray-400'
                            "
                            class="
                                rounded-xl
                                border border-gray-200
                                dark:border-white/10
                                px-4 py-2
                                text-sm font-medium
                                transition-all
                                duration-500
                                hover:scale-105
                            "
                        >
                            Light
                        </button>

                        <button
                            @click="setTheme('dark')"
                            :class="
                                currentTheme === 'dark'
                                    ? 'bg-gradient-to-r from-[#0066cc] via-[#7c3aed] to-[#ff2d55] text-white shadow-lg shadow-purple-500/30'
                                    : 'bg-white dark:bg-[#1c1c1f] text-gray-600 dark:text-gray-400'
                            "
                            class="
                                rounded-xl
                                border border-gray-200
                                dark:border-white/10
                                px-4 py-2
                                text-sm font-medium
                                transition-all
                                duration-500
                                hover:scale-105
                            "
                        >
                            Dark
                        </button>

                        <button
                            @click="setTheme('system')"
                            :class="
                                currentTheme === 'system'
                                    ? 'bg-gradient-to-r from-[#0066cc] via-[#7c3aed] to-[#ff2d55] text-white shadow-lg shadow-purple-500/30'
                                    : 'bg-white dark:bg-[#1c1c1f] text-gray-600 dark:text-gray-400'
                            "
                            class="
                                rounded-xl
                                border border-gray-200
                                dark:border-white/10
                                px-4 py-2
                                text-sm font-medium
                                transition-all
                                duration-500
                                hover:scale-105
                            "
                        >
                            System
                        </button>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>