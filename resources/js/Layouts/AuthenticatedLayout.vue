<!-- resources/js/Layouts/AuthenticatedLayout.vue -->
<script setup>
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import Footer from '@/Components/Footer.vue';
import { Link, usePage } from '@inertiajs/vue3';
import Toast from '@/Components/UI/Toast.vue';

const showingNavigationDropdown = ref(false);
const page = usePage();
</script>

<template>
    <!-- Contenedor flex en columna de pantalla completa -->
    <div class="flex min-h-screen flex-col bg-[#f4f5f7] text-gray-900 transition-colors duration-300 dark:bg-[#060607] dark:text-white">
        <!-- =====================================================
            NAV — Sticky, Siri-grade glass with iridescent edge
        ====================================================== -->
        <nav
            class="sticky top-0 z-50 border-b border-gray-200/40 bg-white/55 backdrop-blur-3xl backdrop-saturate-150 transition-colors duration-300 dark:border-white/[0.06] dark:bg-[#060607]/55"
        >
            <!-- Iridescent light edge (Siri AI signature) -->
            <div
                class="pointer-events-none absolute inset-x-0 -bottom-px h-px opacity-60"
                aria-hidden="true"
                style="
                    background: linear-gradient(
                        90deg,
                        transparent 0%,
                        rgba(0, 114, 168, 0.4) 22%,
                        rgba(33, 178, 75, 0.4) 50%,
                        rgba(0, 114, 168, 0.4) 78%,
                        transparent 100%
                    );
                "
            ></div>

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">

                    <Link :href="route('dashboard')" class="flex items-center gap-3">
                        <img
                            src="/images/dominiomatic_logo.png"
                            alt="DominioMatic"
                            class="h-8 w-auto"
                        />
                    </Link>

                    <div class="hidden items-center gap-2 md:flex">
                        <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </NavLink>

                        <NavLink
                            :href="route('clients.index')" :active="route().current('clients.*')">
                            Clientes
                        </NavLink>

                        <NavLink :href="route('contacts.index')" :active="route().current('contacts.*')">
                            Contactos
                        </NavLink>

                        <NavLink :href="route('products.index')" :active="route().current('products.*')">
                            Productos
                        </NavLink>

                        <NavLink :href="route('subscriptions.index')" :active="route().current('subscriptions.*')">
                            Suscripciones
                        </NavLink>

                        <!-- Pestaña Administración (Visible solo para Admin y Owner) -->
                        <NavLink
                            v-if="['admin', 'owner'].includes($page.props.auth.user?.role)"
                            :href="route('admin.index')"
                            :active="route().current('admin.*')"
                        >
                            Administración
                        </NavLink>
                    </div>

                    <div class="hidden md:flex md:items-center">
                        <Dropdown align="right" width="56">
                            <template #trigger>
                                <button class="flex items-center gap-3 rounded-2xl border border-gray-200/60 bg-white/60 px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-white dark:border-white/10 dark:bg-[#111113]/40 dark:text-gray-200 dark:hover:bg-[#1c1c1f]/60">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-2xl text-sm font-bold text-white shadow-sm"
                                        style="
                                            background: linear-gradient(
                                                135deg,
                                                #0072a8 0%,
                                                #21b24b 100%
                                            );
                                        "
                                    >
                                        <img
                                            v-if="$page.props.auth.user?.avatar"
                                            :src="$page.props.auth.user.avatar"
                                            :alt="$page.props.auth.user.name"
                                            referrerpolicy="no-referrer"
                                            class="h-full w-full object-cover"
                                        />

                                        <span v-else>
                                            {{ $page.props.auth.user?.name?.charAt(0)?.toUpperCase() ?? 'U' }}
                                        </span>
                                    </div>

                                    <span>
                                        {{ $page.props.auth.user.name }}
                                    </span>
                                </button>
                            </template>

                            <template #content>
                                <div class="space-y-0.5">
                                    <DropdownLink
                                        :href="route('profile.edit')"
                                        class="block rounded-2xl px-4 py-2.5 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-gray-100/75 dark:text-gray-200 dark:hover:bg-white/10 dark:hover:text-white"
                                    >
                                        Perfil
                                    </DropdownLink>

                                    <div class="my-1 border-t border-gray-200/40 dark:border-white/10"></div>

                                    <DropdownLink
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                        class="block w-full text-left rounded-2xl px-4 py-2.5 text-sm font-medium text-red-600 transition-all duration-200 hover:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20"
                                    >
                                        Cerrar sesión
                                    </DropdownLink>
                                </div>
                            </template>
                        </Dropdown>
                    </div>

                    <button
                        @click="showingNavigationDropdown = !showingNavigationDropdown"
                        class="rounded-xl p-2 text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5 md:hidden"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Menú Móvil -->
            <div
                v-show="showingNavigationDropdown"
                class="border-t border-gray-200 bg-white/90 backdrop-blur-xl dark:border-white/10 dark:bg-[#111113]/95 md:hidden"
            >
                <div class="space-y-1 p-4">
                    <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                        Dashboard
                    </ResponsiveNavLink>

                    <ResponsiveNavLink
                        v-if="['admin', 'owner'].includes($page.props.auth.user?.role)"
                        :href="route('admin.index')"
                        :active="route().current('admin.*')"
                    >
                        Administración
                    </ResponsiveNavLink>

                    <ResponsiveNavLink
                        :href="route('clients.index')" :active="route().current('clients.*')">
                        Clientes
                    </ResponsiveNavLink>

                    <ResponsiveNavLink :href="route('contacts.index')">
                        Contactos
                    </ResponsiveNavLink>

                    <ResponsiveNavLink :href="route('products.index')">
                        Productos
                    </ResponsiveNavLink>

                    <ResponsiveNavLink :href="route('subscriptions.index')">
                        Suscripciones
                    </ResponsiveNavLink>
                </div>

                <div class="border-t border-gray-200 p-4 dark:border-white/10">
                    <div class="font-medium text-gray-900 dark:text-white">
                        {{ $page.props.auth.user.name }}
                    </div>

                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $page.props.auth.user.email }}
                    </div>

                    <div class="mt-4 space-y-1">
                        <ResponsiveNavLink :href="route('profile.edit')">
                            Perfil
                        </ResponsiveNavLink>

                        <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                            Cerrar sesión
                        </ResponsiveNavLink>
                    </div>
                </div>
            </div>
        </nav>

        <header
            v-if="$slots.header"
            class="border-b border-gray-200/30 bg-transparent transition-colors duration-300 dark:border-white/5"
        >
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- =====================================================
            MAIN — flex-1 para expandirse y empujar el footer
        ====================================================== -->
        <main class="relative flex-1 bg-[#f4f5f7] dark:bg-[#060607]">
            <Transition
                mode="out-in"
                enter-active-class="transition-[opacity,transform] duration-200 ease-out"
                enter-from-class="opacity-0 translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-[opacity,transform] duration-100 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-0.5"
            >
                <div :key="page.component">
                    <slot />
                </div>
            </Transition>
        </main>

        <!-- FOOTER GLOBAL -->
        <Footer />

        <Toast />
    </div>
</template>