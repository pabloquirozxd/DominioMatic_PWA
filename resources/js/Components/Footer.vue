<!-- resources/js/Components/Footer.vue -->
<script setup>
import { Link } from '@inertiajs/vue3'

const currentYear = new Date().getFullYear()
</script>

<template>
    <footer
        class="relative mt-auto border-t border-gray-200/40 bg-white/40 py-8 backdrop-blur-xl dark:border-white/[0.06] dark:bg-[#060607]/60"
    >
        <!-- Borde superior con degradado corporativo sutil (Liquid-GL) -->
        <div
            class="pointer-events-none absolute inset-x-0 top-0 h-px opacity-70"
            aria-hidden="true"
            style="
                background: linear-gradient(
                    90deg,
                    transparent 0%,
                    rgba(0, 114, 168, 0.35) 25%,
                    rgba(33, 178, 75, 0.35) 50%,
                    rgba(0, 114, 168, 0.35) 75%,
                    transparent 100%
                );
            "
        ></div>

        <!-- Capa de brillo ambiental con resplandor en degradado -->
        <div
            class="pointer-events-none absolute inset-0 bg-gradient-to-r from-transparent via-white/[0.02] to-transparent"
        ></div>

        <div
            class="relative mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 px-4 text-center md:flex-row md:px-6 lg:px-8 md:text-left"
        >
            <!-- Logo corporativo con enlace externo -->
            <div class="flex items-center">
                <a
                    href="https://dominomatic.com"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-block transition-transform hover:scale-105 active:scale-95"
                    title="Ir a DominioMatic.com"
                >
                    <img
                        src="/images/dominiomatic_logo.png"
                        alt="DominioMatic"
                        class="h-7 w-auto object-contain transition-opacity hover:opacity-90"
                    />
                </a>
            </div>

            <!-- Directorio de Enlaces de Navegación -->
            <nav class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs font-medium text-gray-600 dark:text-gray-400">
                <Link
                    :href="route('dashboard')"
                    class="transition-colors hover:text-[#0072a8] dark:hover:text-[#38bdf8]"
                >
                    Dashboard
                </Link>

                <Link
                    :href="route('clients.index')"
                    class="transition-colors hover:text-[#0072a8] dark:hover:text-[#38bdf8]"
                >
                    Clientes
                </Link>

                <Link
                    :href="route('contacts.index')"
                    class="transition-colors hover:text-[#0072a8] dark:hover:text-[#38bdf8]"
                >
                    Contactos
                </Link>

                <Link
                    :href="route('products.index')"
                    class="transition-colors hover:text-[#0072a8] dark:hover:text-[#38bdf8]"
                >
                    Productos
                </Link>

                <Link
                    :href="route('subscriptions.index')"
                    class="transition-colors hover:text-[#0072a8] dark:hover:text-[#38bdf8]"
                >
                    Suscripciones
                </Link>

                <Link
                    v-if="['admin', 'owner'].includes($page.props.auth?.user?.role)"
                    :href="route('admin.index')"
                    class="transition-colors hover:text-[#0072a8] dark:hover:text-[#38bdf8]"
                >
                    Administración
                </Link>

                <Link
                    :href="route('profile.edit')"
                    class="transition-colors hover:text-[#0072a8] dark:hover:text-[#38bdf8]"
                >
                    Perfil
                </Link>
            </nav>

            <!-- Leyenda de derechos reservados con enlace -->
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                © {{ currentYear }}
                <a
                    href="https://dominomatic.com"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="font-bold text-gray-800 hover:text-[#0072a8] dark:text-gray-200 dark:hover:text-[#38bdf8] transition-colors"
                >
                    DominioMatic.com
                </a>
                — Todos los derechos reservados.
            </p>
        </div>
    </footer>
</template>