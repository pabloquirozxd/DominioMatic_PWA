<!-- resources/js/Pages/AccessRequest/Submitted.vue -->
<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/UI/Buttons/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    company: {
        type: Object,
        required: true,
    },
    request: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <GuestLayout>
        <Head title="Solicitud Enviada" />

        <div class="py-2 text-center animate-apple-entry">
            <!-- Icono de Éxito Estilo Apple -->
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-500 ring-8 ring-emerald-500/5 dark:bg-emerald-400/10 dark:text-emerald-400">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#0066cc] dark:text-[#7ab7ff]">
                Solicitud Recibida
            </p>

            <h1 class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">
                Hemos notificado a la empresa
            </h1>

            <p class="mt-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400 px-2">
                Hemos notificado a los administradores de <strong class="text-gray-900 dark:text-white">{{ company.name }}</strong>. Te llegará un correo electrónico en cuanto tu solicitud sea aprobada.
            </p>

            <!-- Card con Resumen Informativo (Si viene 'request' desde el backend) -->
            <div
                v-if="request"
                class="my-6 rounded-2xl border border-gray-100 bg-gray-50/80 p-4 text-left dark:border-white/5 dark:bg-white/[0.02]"
            >
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>Correo registrado:</span>
                    <span class="font-medium text-gray-900 dark:text-gray-200">{{ request.email }}</span>
                </div>
                <div v-if="request.requested_at" class="mt-2 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>Enviada:</span>
                    <span class="font-medium text-gray-900 dark:text-gray-200">{{ request.requested_at }}</span>
                </div>
            </div>

            <!-- Botón Volver -->
            <div class="mt-8">
                <Link :href="route('login')">
                    <PrimaryButton class="w-full justify-center py-3 text-sm font-semibold">
                        Volver a Iniciar Sesión
                    </PrimaryButton>
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
@keyframes appleEntry {
    0% {
        opacity: 0;
        transform: scale(0.96);
        filter: blur(4px);
    }
    100% {
        opacity: 1;
        transform: scale(1);
        filter: blur(0);
    }
}

.animate-apple-entry {
    animation: appleEntry 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>