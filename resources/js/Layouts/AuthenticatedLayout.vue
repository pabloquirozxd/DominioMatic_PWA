<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-[#f4f5f7] text-gray-900 transition-colors duration-300 dark:bg-[#060607] dark:text-white">
        <nav class="sticky top-0 z-50 border-b border-gray-200/40 bg-white/40 backdrop-blur-3xl transition-colors duration-300 dark:border-white/5 dark:bg-[#060607]/20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">

                    <Link :href="route('dashboard')" class="flex items-center gap-3">
                        <img
                            src="/images/dominiomatic_logo.png"
                            alt="DominioMatic"
                            class="h-8 w-auto"
                        />
                        <span class="bg-gradient-to-r from-[#0066cc] via-[#c600bf] to-[#ff2d55] bg-clip-text font-semibold tracking-tight text-transparent">
                            DominioMatic
                        </span>
                    </Link>

                    <div class="hidden items-center gap-2 md:flex">
                        <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
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
                    </div>

                    <div class="hidden md:flex md:items-center">
                        <Dropdown align="right" width="56">
                            <template #trigger>
                                <button class="flex items-center gap-3 rounded-2xl border border-gray-200/60 bg-white/60 px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-white dark:border-white/10 dark:bg-[#111113]/40 dark:text-gray-200 dark:hover:bg-[#1c1c1f]/60">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-gradient-to-br from-[#0066cc] via-[#c600bf] to-[#ff2d55] text-xs font-semibold text-white">
                                        {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <span>
                                        {{ $page.props.auth.user.name }}
                                    </span>
                                </button>
                            </template>

                            <template #content>
                                <div class="p-2 rounded-3xl bg-white/75 border border-white/50 shadow-[0_12px_40px_rgba(0,0,0,0.04),inset_0_1px_1px_rgba(255,255,255,0.5)] backdrop-blur-[24px] backdrop-saturate-[180%] dark:bg-[#121214]/40 dark:border-white/[0.15] dark:shadow-[0_30px_70px_rgba(0,0,0,0.85),inset_0_1px_0px_rgba(255,255,255,0.2)]">
                                    <DropdownLink 
                                        :href="route('profile.edit')"
                                        class="block rounded-2xl px-4 py-2.5 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-black/5 dark:text-gray-200 dark:hover:bg-white/10 dark:hover:text-white"
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

            <div
                v-show="showingNavigationDropdown"
                class="border-t border-gray-200 bg-white/90 backdrop-blur-xl dark:border-white/10 dark:bg-[#111113]/95 md:hidden"
            >
                <div class="space-y-1 p-4">
                    <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                        Dashboard
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

        <main>
            <slot />
        </main>
    </div>
</template>