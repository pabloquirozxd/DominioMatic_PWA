<script setup>
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
})

const user = usePage().props.auth.user

const form = useForm({
    name: user.name,
    email: user.email,
})
</script>

<template>
    <section>
        <header>
            <h2
                class="text-lg font-semibold tracking-[-0.02em] text-slate-950 dark:text-white"
            >
                Información del Perfil
            </h2>

            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Actualiza la información de tu cuenta y dirección de
                correo electrónico.
            </p>
        </header>

        <form
            class="mt-6 space-y-6"
            @submit.prevent="form.patch(route('profile.update'))"
        >
            <div>
                <InputLabel
                    for="name"
                    value="Nombre"
                    class="mb-2 block font-medium text-slate-700 dark:text-slate-200"
                />

                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel
                    for="email"
                    value="Correo Electrónico"
                    class="mb-2 block font-medium text-slate-700 dark:text-slate-200"
                />

                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-slate-700 dark:text-slate-300">
                    Tu dirección de correo no está verificada.

                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-[#0072A8] underline underline-offset-2 transition hover:text-[#005E8A] focus:outline-none focus:ring-2 focus:ring-[#0072A8] focus:ring-offset-2 dark:text-[#4FC3F7] dark:hover:text-[#7FDBFF] dark:focus:ring-offset-[#101014]"
                    >
                        Haz clic aquí para volver a enviar el correo de
                        verificación.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-[#21B24B] dark:text-[#4ADE80]"
                >
                    Un nuevo enlace de verificación ha sido enviado a tu
                    correo.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">
                    Guardar
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-slate-500 dark:text-slate-400"
                    >
                        Guardado.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>