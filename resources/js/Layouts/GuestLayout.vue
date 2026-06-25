<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="relative min-h-[100svh] overflow-hidden bg-[#f5f7fb] px-4 py-6 text-gray-950 transition-colors dark:bg-[#0b0b0f] dark:text-white sm:px-6 lg:px-8"
    >
        <!-- Fondo decorativo -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div
                class="absolute -left-32 top-[-160px] h-[420px] w-[420px] rounded-full bg-[#0066cc]/20 blur-3xl dark:bg-[#0066cc]/25"
            />
            <div
                class="absolute -right-32 bottom-[-180px] h-[480px] w-[480px] rounded-full bg-indigo-400/20 blur-3xl dark:bg-indigo-500/20"
            />
            <div
                class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-[#0066cc]/40 to-transparent"
            />
        </div>

        <div
            class="relative z-10 mx-auto flex min-h-[calc(100svh-3rem)] w-full max-w-7xl items-center justify-center"
        >
            <div
                class="grid w-full items-center gap-10 lg:grid-cols-[1.05fr_0.95fr] xl:gap-16"
            >
                <!-- Panel izquierdo solo en pantallas grandes -->
                <section class="hidden lg:block">
                    <Link
                        href="/"
                        class="inline-flex transition duration-300 hover:-translate-y-1"
                    >
                        <img
                            src="/images/dominiomatic_logo.png"
                            alt="DominioMatic"
                            class="h-12 w-auto"
                        />
                    </Link>

                    <div class="mt-12 max-w-xl">
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.35em] text-[#0066cc] dark:text-[#7ab7ff]"
                        >
                            Plataforma de gestión digital
                        </p>

                        <h1
                            class="mt-5 text-5xl font-black tracking-tight text-gray-950 dark:text-white xl:text-6xl"
                        >
                            Gestiona tus clientes, productos y suscripciones en un solo lugar.
                        </h1>

                        <p
                            class="mt-6 max-w-lg text-base leading-8 text-gray-600 dark:text-gray-400 xl:text-lg"
                        >
                            Centraliza la administración comercial de tus servicios digitales con una experiencia moderna, segura y pensada para empresas en crecimiento.
                        </p>

                        <div class="mt-8 grid max-w-lg grid-cols-2 gap-4">
                            <div
                                class="rounded-3xl border border-white/70 bg-white/70 p-5 shadow-xl shadow-slate-950/5 backdrop-blur-xl dark:border-white/10 dark:bg-white/[0.06]"
                            >
                                <p class="text-2xl font-black text-gray-950 dark:text-white">
                                    Clientes
                                </p>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Organiza contactos y datos importantes.
                                </p>
                            </div>

                            <div
                                class="rounded-3xl border border-white/70 bg-white/70 p-5 shadow-xl shadow-slate-950/5 backdrop-blur-xl dark:border-white/10 dark:bg-white/[0.06]"
                            >
                                <p class="text-2xl font-black text-gray-950 dark:text-white">
                                    Suscripciones
                                </p>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Controla servicios activos y vencimientos.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Panel derecho auth -->
                <section class="mx-auto w-full max-w-xl">
                    <!-- Logo en mobile/tablet -->
                    <div class="mb-8 flex justify-center lg:hidden">
                        <Link
                            href="/"
                            class="inline-flex transition duration-300 hover:-translate-y-1"
                        >
                            <img
                                src="/images/dominiomatic_logo.png"
                                alt="DominioMatic"
                                class="h-10 w-auto"
                            />
                        </Link>
                    </div>

                    <div
                        class="rounded-[2rem] border border-white/70 bg-white/90 p-7 shadow-2xl shadow-slate-950/10 backdrop-blur-xl transition-colors dark:border-white/10 dark:bg-white/[0.06] dark:shadow-black/30 sm:p-9 xl:p-10"
                    >
                        <slot />
                    </div>

                    <p class="mt-6 text-center text-xs text-gray-500 dark:text-gray-500">
                        © {{ new Date().getFullYear() }} DominioMatic.com. Todos los derechos reservados.
                    </p>
                </section>
            </div>
        </div>
    </div>
</template>