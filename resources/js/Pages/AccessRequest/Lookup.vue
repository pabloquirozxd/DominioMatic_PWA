<!-- resources/js/Pages/AccessRequest/Lookup.vue -->
<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import FormField from '@/Components/UI/Forms/FormField.vue';
import TextField from '@/Components/UI/Forms/TextField.vue';
import PrimaryButton from '@/Components/UI/Buttons/PrimaryButton.vue';

const form = useForm({
    company_query: '',
});

const submit = () => {
    form.post(route('join.find'));
};
</script>

<template>
    <Head title="Buscar Empresa" />

    <div class="min-h-screen flex flex-col justify-center items-center bg-gray-50 dark:bg-[#0b0c0e] p-4 sm:p-6 transition-colors duration-300">
        <div class="w-full max-w-md space-y-8 bg-white dark:bg-[#121316] p-8 rounded-3xl shadow-2xl border border-gray-100 dark:border-white/10 backdrop-blur-md">
            
            <!-- Header Limpio y Jerárquico -->
            <div class="text-center space-y-5">
                <!-- Brand Logo -->
                <div class="flex justify-center">
                    <img 
                        src="/images/dominiomatic_logo.png" 
                        alt="DominioMatic" 
                        class="h-9 w-auto object-contain"
                    />
                </div>

                <!-- Badge Contextual Minimalista -->
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold tracking-wide bg-[#0066cc]/10 text-[#0066cc] dark:bg-[#7ab7ff]/10 dark:text-[#7ab7ff] border border-[#0066cc]/20 dark:border-[#7ab7ff]/20">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Acceso a Organizaciones
                    </span>
                </div>

                <!-- Textos Principales -->
                <div class="space-y-2">
                    <h1 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                        Encuentra tu espacio de trabajo
                    </h1>
                    <p class="text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                        Escribe el nombre o el sitio web de tu empresa para enviarle una solicitud de acceso al administrador.
                    </p>
                </div>
            </div>

            <!-- Formulario de Búsqueda -->
            <form @submit.prevent="submit" class="space-y-6">
                <FormField
                    label="NOMBRE O DOMINIO DE LA EMPRESA"
                    :error="form.errors.company_query"
                >
                    <TextField
                        v-model="form.company_query"
                        type="text"
                        placeholder="Ejemplo: DominioMatic.com"
                        required
                        autofocus
                        :has-error="Boolean(form.errors.company_query)"
                    />
                </FormField>

                <PrimaryButton
                    type="submit"
                    class="w-full justify-center py-3.5 text-sm font-semibold shadow-lg"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Buscando empresa...</span>
                    <span v-else class="flex items-center gap-2">
                        Buscar Empresa
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </span>
                </PrimaryButton>
            </form>

            <!-- Footer / Volver -->
            <div class="pt-4 border-t border-gray-100 dark:border-white/5 text-center">
                <Link
                    :href="route('login')"
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors"
                >
                    <span>&larr;</span> Volver a Iniciar Sesión
                </Link>
            </div>
        </div>
    </div>
</template>