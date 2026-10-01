<!-- resources/js/Pages/AccessRequest/Create.vue -->
<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import FormField from '@/Components/UI/Forms/FormField.vue';
import TextField from '@/Components/UI/Forms/TextField.vue';
import TextareaField from '@/Components/UI/Forms/TextareaField.vue';
import PrimaryButton from '@/Components/UI/Buttons/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    company: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    message: '',
});

const submit = () => {
    form.post(route('join.store', props.company.slug), {
        onFinish: () => form.reset('password', 'password_confirmation', 'message'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="`Solicitar acceso a ${company.name}`" />

        <!-- Header / Logo de la Organización -->
        <div class="mb-8 text-center">
            <div class="mb-4 flex items-center justify-center">
                <img
                    v-if="company.logo_path"
                    :src="`/storage/${company.logo_path}`"
                    :alt="company.name"
                    class="h-12 w-auto object-contain"
                />
                <img
                    v-else
                    src="/images/dominiomatic_logo.png"
                    :alt="company.name"
                    class="h-12 w-auto object-contain"
                />
            </div>

            <h1 class="text-2xl font-bold text-gray-950 dark:text-white">
                Solicitar acceso
            </h1>

            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                A la organización <span class="font-semibold text-gray-800 dark:text-gray-200">{{ company.name }}</span>
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Nombre Completo -->
            <FormField
                for="name"
                label="Nombre completo"
                required
                hint="Ingresa tu nombre y apellido para identificarte dentro de la organización."
                :error="form.errors.name"
            >
                <TextField
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Ej: Edwin Aguilar"
                    :has-error="Boolean(form.errors.name)"
                />
            </FormField>

            <!-- Correo Corporativo -->
            <FormField
                for="email"
                label="Correo electrónico corporativo"
                required
                hint="Te notificaremos a esta dirección cuando el administrador apruebe tu solicitud."
                :error="form.errors.email"
            >
                <TextField
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="username"
                    placeholder="tu-correo@empresa.com"
                    :has-error="Boolean(form.errors.email)"
                />
            </FormField>

            <!-- Contraseña -->
            <FormField
                for="password"
                label="Contraseña"
                required
                hint="Esta contraseña será tu acceso una vez se apruebe tu cuenta."
                :error="form.errors.password"
            >
                <TextField
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                    :has-error="Boolean(form.errors.password)"
                />
            </FormField>

            <!-- Confirmar Contraseña -->
            <FormField
                for="password_confirmation"
                label="Confirmar contraseña"
                required
                :error="form.errors.password_confirmation"
            >
                <TextField
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                    :has-error="Boolean(form.errors.password_confirmation)"
                />
            </FormField>

            <!-- Mensaje o Departamento (Opcional) -->
            <FormField
                for="message"
                label="Mensaje o departamento"
                hint="Puedes agregar notas sobre tu cargo o equipo dentro de la empresa."
                :error="form.errors.message"
            >
                <TextareaField
                    id="message"
                    v-model="form.message"
                    :rows="3"
                    placeholder="Indica tu rol o el motivo de tu solicitud..."
                    :has-error="Boolean(form.errors.message)"
                />
            </FormField>

            <!-- Botón de Envío -->
            <div class="pt-2">
                <PrimaryButton
                    type="submit"
                    :disabled="form.processing"
                    class="w-full justify-center py-3 text-sm font-semibold shadow-lg"
                >
                    {{ form.processing ? 'Enviando solicitud...' : 'Enviar Solicitud' }}
                </PrimaryButton>
            </div>
        </form>

        <p class="mt-6 text-center text-xs text-gray-500 dark:text-gray-400">
            El acceso estará sujeto a la aprobación del administrador de {{ company.name }}.
        </p>
    </GuestLayout>
</template>