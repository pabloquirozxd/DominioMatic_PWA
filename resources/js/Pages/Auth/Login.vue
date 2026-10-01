<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import FormField from '@/Components/UI/Forms/FormField.vue';
import TextField from '@/Components/UI/Forms/TextField.vue';
import PrimaryButton from '@/Components/UI/Buttons/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Iniciar sesión" />

        <div class="mb-8 text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#0066cc] dark:text-[#7ab7ff]">
                DominioMatic.com
            </p>

            <h1 class="mt-3 text-2xl font-bold text-gray-950 dark:text-white">
                Bienvenido de nuevo
            </h1>

            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                Ingresa a tu panel para administrar clientes, productos y suscripciones.
            </p>
        </div>

        <div
            v-if="status"
            class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300"
        >
            {{ status }}
        </div>

        <form class="space-y-5" @submit.prevent="submit">
            <!-- Correo electrónico empaquetado en FormField -->
            <FormField
                for="email"
                label="CORREO ELECTRÓNICO"
                required
                :error="form.errors.email"
            >
                <TextField
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="tu-correo@empresa.com"
                    :has-error="Boolean(form.errors.email)"
                />
            </FormField>

            <!-- Contraseña empaquetada en FormField -->
            <FormField
                for="password"
                label="CONTRASEÑA"
                required
                :error="form.errors.password"
            >
                <TextField
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    :has-error="Boolean(form.errors.password)"
                />
            </FormField>

            <div class="flex items-center justify-between gap-4">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        name="remember"
                        class="h-4 w-4 rounded border-gray-300 text-[#0066cc] shadow-sm focus:ring-[#0066cc] dark:border-white/20 dark:bg-[#1c1c1f]"
                    />

                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        Recordarme
                    </span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm font-medium text-[#0066cc] underline-offset-4 transition hover:underline dark:text-[#7ab7ff]"
                >
                    ¿Olvidaste tu contraseña?
                </Link>
            </div>

            <!-- Botón principal usando PrimaryButton -->
            <PrimaryButton
                type="submit"
                :disabled="form.processing"
                class="w-full justify-center py-3 text-sm font-semibold shadow-lg"
            >
                {{ form.processing ? 'Ingresando...' : 'Iniciar sesión' }}
            </PrimaryButton>
        </form>

        <div class="my-6 flex items-center gap-3">
            <div class="h-px flex-1 bg-gray-200 dark:bg-white/10"></div>
            <span class="text-xs font-medium uppercase tracking-wider text-gray-400">
                o continúa con
            </span>
            <div class="h-px flex-1 bg-gray-200 dark:bg-white/10"></div>
        </div>

        <a
            :href="route('google.redirect', { mode: 'login' })"
            class="flex w-full items-center justify-center gap-3 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-gray-50 dark:border-white/10 dark:bg-white/[0.06] dark:text-white dark:hover:bg-white/[0.09]"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#4285F4" d="M21.805 10.023h-9.72v3.955h5.591c-.241 1.27-.972 2.347-2.071 3.068v2.548h3.354c1.964-1.808 3.096-4.47 3.096-7.624 0-.646-.058-1.27-.25-1.947z" />
                <path fill="#34A853" d="M12.085 22c2.803 0 5.157-.923 6.874-2.506l-3.354-2.548c-.923.62-2.104.986-3.52.986-2.703 0-4.99-1.823-5.81-4.276H2.812v2.626C4.52 19.67 8.03 22 12.085 22z" />
                <path fill="#FBBC05" d="M6.275 13.656a5.994 5.994 0 0 1-.316-1.91c0-.662.114-1.302.316-1.91V7.21H2.812A9.93 9.93 0 0 0 1.75 11.746c0 1.633.39 3.177 1.062 4.536l3.463-2.626z" />
                <path fill="#EA4335" d="M12.085 5.56c1.525 0 2.894.524 3.973 1.553l2.977-2.977C17.24 2.463 14.888 1.5 12.085 1.5 8.03 1.5 4.52 3.83 2.812 7.21l3.463 2.626c.82-2.453 3.107-4.276 5.81-4.276z" />
            </svg>

            Continuar con Google
        </a>

        <!-- Enlaces de navegación / Sección Solicitud B2B -->
        <div class="mt-8 space-y-4">
            <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                ¿Todavía no tienes cuenta?
                <Link
                    :href="route('register')"
                    class="font-semibold text-[#0066cc] underline-offset-4 transition hover:underline dark:text-[#7ab7ff]"
                >
                    Crear cuenta
                </Link>
            </div>

            <!-- Acceso al Buscador Inteligente de Organizaciones -->
            <div class="pt-3 border-t border-gray-100 dark:border-white/5">
                <Link
                    :href="route('join.lookup')"
                    class="group flex w-full items-center justify-between rounded-2xl border border-gray-100 bg-gray-50/50 p-3.5 transition-all duration-300 hover:border-gray-200 hover:bg-gray-100/80 dark:border-white/5 dark:bg-white/[0.02] dark:hover:border-white/10 dark:hover:bg-white/[0.04]"
                >
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            ¿Unirte a una organización?
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-200 transition-colors">
                            Busca la empresa por su nombre o sitio web
                        </span>
                    </div>

                    <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-white text-gray-500 shadow-sm group-hover:translate-x-0.5 group-hover:text-[#0066cc] dark:bg-white/10 dark:text-gray-300 dark:group-hover:text-[#7ab7ff] transition-all">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>