<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    company_identifier: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const continueWithGoogle = () => {
    if (!form.company_identifier.trim()) {
        form.setError('company_identifier', 'Primero escribe el nombre de tu empresa para continuar con Google.');
        return;
    }

    window.location.href = route('google.redirect', {
        mode: 'register',
        company_identifier: form.company_identifier,
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Crear cuenta" />

        <div class="mb-8 text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#0066cc] dark:text-[#7ab7ff]">
                DominioMatic.com
            </p>

            <h1 class="mt-3 text-2xl font-bold text-gray-950 dark:text-white">
                Crea tu cuenta
            </h1>

            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                Regístrate con tu empresa para acceder al panel de clientes, productos y suscripciones.
            </p>
        </div>

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <label
                    for="name"
                    class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400"
                >
                    Nombre completo
                </label>

                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Ej: Pablo Quiroz"
                    class="block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-[#0066cc] focus:ring-4 focus:ring-[#0066cc]/10 dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white dark:placeholder:text-gray-500 dark:focus:border-[#7ab7ff] dark:focus:ring-[#7ab7ff]/10"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <label
                    for="email"
                    class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400"
                >
                    Correo electrónico
                </label>

                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="username"
                    placeholder="tu-correo@empresa.com"
                    class="block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-[#0066cc] focus:ring-4 focus:ring-[#0066cc]/10 dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white dark:placeholder:text-gray-500 dark:focus:border-[#7ab7ff] dark:focus:ring-[#7ab7ff]/10"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <label
                    for="company_identifier"
                    class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400"
                >
                    Empresa
                </label>

                <input
                    id="company_identifier"
                    v-model="form.company_identifier"
                    type="text"
                    required
                    autocomplete="organization"
                    placeholder="Ej: DominioMatic.com"
                    class="block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-[#0066cc] focus:ring-4 focus:ring-[#0066cc]/10 dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white dark:placeholder:text-gray-500 dark:focus:border-[#7ab7ff] dark:focus:ring-[#7ab7ff]/10"
                />

                <p class="mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400">
                    Escribe el nombre exacto de la empresa registrada.
                </p>

                <InputError class="mt-2" :message="form.errors.company_identifier" />
            </div>

            <div>
                <label
                    for="password"
                    class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400"
                >
                    Contraseña
                </label>

                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                    class="block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-[#0066cc] focus:ring-4 focus:ring-[#0066cc]/10 dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white dark:placeholder:text-gray-500 dark:focus:border-[#7ab7ff] dark:focus:ring-[#7ab7ff]/10"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <label
                    for="password_confirmation"
                    class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400"
                >
                    Confirmar contraseña
                </label>

                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                    class="block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-[#0066cc] focus:ring-4 focus:ring-[#0066cc]/10 dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white dark:placeholder:text-gray-500 dark:focus:border-[#7ab7ff] dark:focus:ring-[#7ab7ff]/10"
                />

                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="flex w-full items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-950/10 transition hover:-translate-y-0.5 hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-black dark:hover:bg-gray-200"
            >
                {{ form.processing ? 'Creando cuenta...' : 'Crear cuenta' }}
            </button>
        </form>

        <div class="my-6 flex items-center gap-3">
            <div class="h-px flex-1 bg-gray-200 dark:bg-white/10"></div>
            <span class="text-xs font-medium uppercase tracking-wider text-gray-400">
                o continúa con
            </span>
            <div class="h-px flex-1 bg-gray-200 dark:bg-white/10"></div>
        </div>

        <button
            type="button"
            @click="continueWithGoogle"
            class="flex w-full items-center justify-center gap-3 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-gray-50 dark:border-white/10 dark:bg-white/[0.06] dark:text-white dark:hover:bg-white/[0.09]"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#4285F4" d="M21.805 10.023h-9.72v3.955h5.591c-.241 1.27-.972 2.347-2.071 3.068v2.548h3.354c1.964-1.808 3.096-4.47 3.096-7.624 0-.646-.058-1.27-.25-1.947z" />
                <path fill="#34A853" d="M12.085 22c2.803 0 5.157-.923 6.874-2.506l-3.354-2.548c-.923.62-2.104.986-3.52.986-2.703 0-4.99-1.823-5.81-4.276H2.812v2.626C4.52 19.67 8.03 22 12.085 22z" />
                <path fill="#FBBC05" d="M6.275 13.656a5.994 5.994 0 0 1-.316-1.91c0-.662.114-1.302.316-1.91V7.21H2.812A9.93 9.93 0 0 0 1.75 11.746c0 1.633.39 3.177 1.062 4.536l3.463-2.626z" />
                <path fill="#EA4335" d="M12.085 5.56c1.525 0 2.894.524 3.973 1.553l2.977-2.977C17.24 2.463 14.888 1.5 12.085 1.5 8.03 1.5 4.52 3.83 2.812 7.21l3.463 2.626c.82-2.453 3.107-4.276 5.81-4.276z" />
            </svg>

            Registrarme con Google
        </button>

        <div class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
            ¿Ya tienes una cuenta?

            <Link
                :href="route('login')"
                class="font-semibold text-[#0066cc] underline-offset-4 transition hover:underline dark:text-[#7ab7ff]"
            >
                Inicia sesión
            </Link>
        </div>
    </GuestLayout>
</template>