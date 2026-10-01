<!-- resources/js/Pages/Admin/Index.vue -->
<script setup>
import { ref, computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router, usePage, useForm } from '@inertiajs/vue3'

import SegmentedControl from '@/Components/UI/Navigation/SegmentedControl.vue'
import PrimaryButton from '@/Components/UI/Buttons/PrimaryButton.vue'
import SecondaryButton from '@/Components/UI/Buttons/SecondaryButton.vue'
import FormField from '@/Components/UI/Forms/FormField.vue'
import TextField from '@/Components/UI/Forms/TextField.vue'
import SelectField from '@/Components/UI/Forms/SelectField.vue'

import {
    TrashIcon,
    PencilIcon,
    BuildingOfficeIcon,
    UserGroupIcon,
    InboxArrowDownIcon,
    PlusIcon,
    XMarkIcon,
    EnvelopeIcon,
    BuildingOffice2Icon,
    UserIcon,
} from '@heroicons/vue/24/outline'

defineOptions({
    layout: AuthenticatedLayout,
});

const props = defineProps({
    authRole: String,
    userCompany: String,
    users: Array,
    accessRequests: Array,
    companies: Array,
    pendingCount: Number,
})

const page = usePage()
const currentUser = computed(() => page.props.auth.user)

const isSuperOwner = computed(() => {
    return (
        props.authRole === 'owner' ||
        currentUser.value?.email === 'pablo@quiroz.me'
    )
})

const activeTab = ref('members')
const requestStatusFilter = ref('pending')

/* ------------------------------------------------------------------ */
/* Tabs                                                                */
/* ------------------------------------------------------------------ */

const tabOptions = computed(() => {
    return [
        { label: 'Miembros', value: 'members', icon: UserGroupIcon },
        {
            label:
                props.pendingCount > 0
                    ? `Solicitudes (${props.pendingCount})`
                    : 'Solicitudes',
            value: 'requests',
            icon: InboxArrowDownIcon,
        },
    ]
})

const requestStatusOptions = [
    { label: 'Pendientes', value: 'pending' },
    { label: 'Aprobadas', value: 'approved' },
    { label: 'Rechazadas', value: 'rejected' },
]

const filteredRequests = computed(() => {
    if (!props.accessRequests) return []
    if (requestStatusFilter.value === 'all') return props.accessRequests
    return props.accessRequests.filter(
        (r) => r.status === requestStatusFilter.value
    )
})

/* ------------------------------------------------------------------ */
/* Agrupación de usuarios por empresa                                  */
/* ------------------------------------------------------------------ */

const groupedUsers = computed(() => {
    if (!isSuperOwner.value || !props.users) return []

    const groups = {}

    props.users.forEach((user) => {
        const companyName = user.company_name || 'Sin Empresa Asignada'

        if (!groups[companyName]) {
            const companyData = (props.companies || []).find(
                (c) => c.name === companyName
            )

            groups[companyName] = {
                name: companyName,
                slug: companyData?.slug || null,
                logo: companyData?.logo || null,
                is_active: companyData?.is_active ?? true,
                company_id: companyData?.id || null,
                users: [],
            }
        }

        groups[companyName].users.push(user)
    })

    return Object.values(groups)
})

/* ------------------------------------------------------------------ */
/* Logo de empresa                                                     */
/* ------------------------------------------------------------------ */

const getCompanyLogo = (group) => {
    if (!group) return null

    // Si la empresa tiene logo propio, usarlo
    if (group.logo) return group.logo

    // DominioMatic siempre muestra su favicon de marca
    const slug = String(group.slug || '').toLowerCase()
    const name = String(group.name || '').toLowerCase()

    if (
        slug === 'dominiomatic' ||
        name === 'dominiomatic' ||
        name === 'dominiomatic.com'
    ) {
        return '/images/favicon_dominiomatic.png'
    }

    return null
}

/* ------------------------------------------------------------------ */
/* Acciones de usuario                                                 */
/* ------------------------------------------------------------------ */

const updateRole = (userId, newRole) => {
    router.patch(
        route('admin.users.update-role', userId),
        { role: newRole },
        { preserveScroll: true }
    )
}

const deleteUser = (userId) => {
    if (
        confirm(
            '¿Estás seguro de remover este miembro de la organización?'
        )
    ) {
        router.delete(route('admin.users.destroy', userId), {
            preserveScroll: true,
        })
    }
}

/* ------------------------------------------------------------------ */
/* Acciones de solicitudes                                             */
/* ------------------------------------------------------------------ */

const approveRequest = (uuid) => {
    router.post(
        route('admin.access-requests.approve', uuid),
        {},
        { preserveScroll: true }
    )
}

const rejectRequest = (uuid) => {
    router.post(
        route('admin.access-requests.reject', uuid),
        {},
        { preserveScroll: true }
    )
}

/* ------------------------------------------------------------------ */
/* Invitación                                                          */
/* ------------------------------------------------------------------ */

const showInviteModal = ref(false)

const inviteForm = useForm({
    email: '',
    company_id: isSuperOwner.value ? 'new' : 'current',
    new_company_name: '',
    role: 'admin',
})

const companySelectOptions = computed(() => {
    const opts = [{ label: '+ Crear nueva empresa', value: 'new' }]
    if (props.companies) {
        props.companies.forEach((c) => {
            opts.push({ label: c.name, value: c.id })
        })
    }
    return opts
})

const roleOptions = [
    { label: 'Administrador', value: 'admin' },
    { label: 'Miembro', value: 'member' },
]

const submitInvite = () => {
    inviteForm.post(route('admin.users.invite'), {
        preserveScroll: true,
        onSuccess: () => {
            showInviteModal.value = false
            inviteForm.reset()
        },
    })
}

/* ------------------------------------------------------------------ */
/* CRUD de empresas                                                    */
/* ------------------------------------------------------------------ */

const showCompanyModal = ref(false)
const isEditingCompany = ref(false)

const companyForm = useForm({
    id: null,
    name: '',
    slug: '',
    is_active: 1,
})

const openCreateCompany = () => {
    isEditingCompany.value = false
    companyForm.reset()
    companyForm.is_active = 1
    showCompanyModal.value = true
}

const openEditCompany = (company) => {
    isEditingCompany.value = true
    companyForm.id = company.id || company.company_id
    companyForm.name = company.name
    companyForm.slug = company.slug || ''
    companyForm.is_active = company.is_active ? 1 : 0
    showCompanyModal.value = true
}

const submitCompany = () => {
    if (isEditingCompany.value) {
        companyForm.patch(
            route('admin.companies.update', companyForm.id),
            {
                preserveScroll: true,
                onSuccess: () => {
                    showCompanyModal.value = false
                    companyForm.reset()
                },
            }
        )
    } else {
        companyForm.post(route('admin.companies.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showCompanyModal.value = false
                companyForm.reset()
            },
        })
    }
}

const deleteCompany = (id) => {
    if (
        confirm(
            'PELIGRO: ¿Eliminar definitivamente esta empresa y desvincular a todos sus usuarios?'
        )
    ) {
        router.delete(route('admin.companies.destroy', id), {
            preserveScroll: true,
        })
    }
}
</script>

<template>
    <Head title="Administración | DominioMatic" />

    <div
        class="min-h-screen overflow-x-hidden bg-[#f5f5f7] text-slate-950 transition-colors dark:bg-[#050507] dark:text-white"
    >
        <!-- AMBIENT BACKGROUND -->
        <div
            class="pointer-events-none fixed inset-0 overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="absolute -bottom-56 -left-56 h-[44rem] w-[44rem] rounded-full opacity-[0.14] blur-[130px] dark:opacity-[0.24]"
                style="background: radial-gradient(circle, #002B48 0%, transparent 70%)"
            ></div>

            <div
                class="absolute -right-48 -top-48 h-[42rem] w-[42rem] rounded-full opacity-[0.16] blur-[130px] dark:opacity-[0.26]"
                style="background: radial-gradient(circle, #0072A8 0%, transparent 70%)"
            ></div>

            <div
                class="absolute -left-40 top-[18%] h-[38rem] w-[38rem] rounded-full opacity-[0.12] blur-[120px] dark:opacity-[0.20]"
                style="background: radial-gradient(circle, #21B24B 0%, transparent 70%)"
            ></div>

            <div
                class="absolute -right-40 bottom-[8%] h-[40rem] w-[40rem] rounded-full opacity-[0.10] blur-[130px] dark:opacity-[0.18]"
                style="background: radial-gradient(circle, #005E2B 0%, transparent 70%)"
            ></div>

            <div
                class="absolute left-1/2 top-1/3 h-[36rem] w-[36rem] -translate-x-1/2 rounded-full opacity-[0.55] blur-[140px] dark:opacity-[0.06]"
                style="background: radial-gradient(circle, #FFFFFF 0%, transparent 65%)"
            ></div>
        </div>

        <div
            class="relative mx-auto max-w-[1480px] px-4 pb-20 pt-5 sm:px-6 lg:px-8"
        >
            <!-- HEADER -->
            <header
                class="mb-8 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
            >
                <div class="flex items-center gap-3">
                    <h1
                        class="text-3xl font-semibold tracking-[-0.045em] text-slate-950 sm:text-4xl dark:text-white"
                    >
                        Administración
                    </h1>

                    <span
                        class="inline-flex items-center rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em]"
                        :class="
                            isSuperOwner
                                ? 'border-[#0072A8]/25 bg-[#0072A8]/10 text-[#0072A8] dark:border-[#0072A8]/30 dark:bg-[#0072A8]/15 dark:text-[#4FC3F7]'
                                : 'border-slate-200/70 bg-white/55 text-slate-600 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-slate-300'
                        "
                    >
                        {{ isSuperOwner ? 'Súper Owner' : userCompany }}
                    </span>
                </div>

                <div
                    class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center"
                >
                    <div
                        class="w-full min-w-[380px] shrink-0 sm:w-auto lg:min-w-[430px]"
                    >
                        <SegmentedControl
                            v-model="activeTab"
                            :options="tabOptions"
                        />
                    </div>

                    <PrimaryButton
                        type="button"
                        class="shrink-0 justify-center whitespace-nowrap"
                        @click="showInviteModal = true"
                    >
                        <PlusIcon class="h-4 w-4 stroke-[2.5]" />
                        <span>
                            {{
                                isSuperOwner
                                    ? 'Invitar Usuario'
                                    : 'Invitar Miembro'
                            }}
                        </span>
                    </PrimaryButton>
                </div>
            </header>

            <!-- =====================================================
                 TAB 1: MIEMBROS
            ====================================================== -->
            <div
                v-if="activeTab === 'members'"
                class="animate-in fade-in slide-in-from-bottom-2 duration-300 space-y-6"
            >
                <!-- ============================================
                     Vista SUPEROWNER: agrupado por empresa
                ============================================= -->
                <template v-if="isSuperOwner">
                    <!-- Barra de sección: título + botón secundario sutil -->
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <h2
                            class="text-lg font-semibold tracking-[-0.02em] text-slate-950 dark:text-white"
                        >
                            Empresas y sus miembros
                        </h2>

                        <SecondaryButton
                            type="button"
                            class="shrink-0"
                            @click="openCreateCompany"
                        >
                            <PlusIcon class="h-4 w-4 stroke-[2.5]" />
                            <span>Nueva Empresa</span>
                        </SecondaryButton>
                    </div>

                    <!-- Cards por empresa -->
                    <div
                        v-for="group in groupedUsers"
                        :key="group.name"
                        class="overflow-hidden rounded-[28px] border border-white/65 bg-white/35 shadow-[0_22px_70px_rgba(15,23,42,0.06),inset_0_1px_2px_rgba(255,255,255,0.85),inset_0_-2px_5px_rgba(0,0,0,0.06)] backdrop-blur-xl dark:border-white/[0.08] dark:bg-[#101014]/65 dark:shadow-[0_22px_70px_rgba(0,0,0,0.28),inset_0_1px_2px_rgba(255,255,255,0.16),inset_0_-2px_6px_rgba(0,0,0,0.5)]"
                    >
                        <!-- Header de la card: logo + nombre + acciones -->
                        <div
                            class="flex flex-wrap items-center justify-between gap-4 border-b border-black/[0.055] px-6 py-5 dark:border-white/[0.07]"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-white/60 bg-white/60 shadow-sm dark:border-white/[0.06] dark:bg-white/[0.04]"
                                >
                                    <img
                                        v-if="getCompanyLogo(group)"
                                        :src="getCompanyLogo(group)"
                                        :alt="group.name"
                                        class="h-full w-full object-contain p-1.5"
                                    />

                                    <BuildingOfficeIcon
                                        v-else
                                        class="h-6 w-6 text-[#0072A8] dark:text-[#4FC3F7]"
                                    />
                                </div>

                                <div>
                                    <div class="flex items-center gap-2.5">
                                        <h3
                                            class="text-xl font-semibold tracking-[-0.02em] text-slate-950 dark:text-white"
                                        >
                                            {{ group.name }}
                                        </h3>

                                        <span
                                            v-if="!group.is_active"
                                            class="rounded-full bg-slate-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.1em] text-slate-600 dark:bg-white/[0.04] dark:text-slate-400"
                                        >
                                            Inactiva
                                        </span>
                                    </div>

                                    <p
                                        class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        {{ group.users.length }}
                                        {{
                                            group.users.length === 1
                                                ? 'miembro'
                                                : 'miembros'
                                        }}
                                    </p>
                                </div>
                            </div>

                            <!-- Acciones de empresa (solo si tiene id real) -->
                            <div
                                v-if="group.company_id"
                                class="flex items-center gap-2"
                            >
                                <button
                                    type="button"
                                    class="rounded-xl p-2 text-slate-500 transition hover:bg-black/[0.05] hover:text-slate-900 active:scale-90 dark:text-slate-400 dark:hover:bg-white/[0.08] dark:hover:text-white"
                                    title="Editar empresa"
                                    @click="openEditCompany(group)"
                                >
                                    <PencilIcon class="h-4 w-4" />
                                </button>

                                <button
                                    type="button"
                                    class="rounded-xl p-2 text-slate-500 transition hover:bg-rose-500/10 hover:text-rose-500 active:scale-90 dark:text-slate-400 dark:hover:bg-rose-400/10 dark:hover:text-rose-300"
                                    title="Eliminar empresa"
                                    @click="deleteCompany(group.company_id)"
                                >
                                    <TrashIcon class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <!-- Tabla de usuarios de la empresa -->
                        <div class="overflow-x-auto">
                            <table class="w-full table-fixed text-left text-sm">
                                <colgroup>
                                    <col class="w-[42%]" />
                                    <col class="w-[22%]" />
                                    <col class="w-[22%]" />
                                    <col class="w-[14%]" />
                                </colgroup>

                                <thead
                                    class="border-b border-black/[0.055] dark:border-white/[0.07]"
                                >
                                    <tr
                                        class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                                    >
                                        <th class="px-6 py-4">Usuario</th>
                                        <th class="px-6 py-4">
                                            Rol Asignado
                                        </th>
                                        <th class="px-6 py-4">
                                            Miembro Desde
                                        </th>
                                        <th class="px-6 py-4 text-right">
                                            Acción
                                        </th>
                                    </tr>
                                </thead>

                                <tbody
                                    class="divide-y divide-black/[0.045] dark:divide-white/[0.06]"
                                >
                                    <tr
                                        v-for="u in group.users"
                                        :key="u.id"
                                        class="transition hover:bg-black/[0.02] dark:hover:bg-white/[0.03]"
                                    >
                                        <td class="px-6 py-4">
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <div
                                                    class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full text-sm font-bold text-white shadow-sm"
                                                    style="background: linear-gradient(135deg, #0072A8 0%, #21B24B 100%)"
                                                >
                                                    <img
                                                        v-if="u.avatar"
                                                        :src="u.avatar"
                                                        class="h-full w-full object-cover"
                                                    />

                                                    <span v-else>
                                                        {{
                                                            u.name
                                                                .charAt(0)
                                                                .toUpperCase()
                                                        }}
                                                    </span>
                                                </div>

                                                <div class="min-w-0">
                                                    <div
                                                        class="truncate font-semibold text-slate-950 dark:text-white"
                                                    >
                                                        {{ u.name }}
                                                        <span
                                                            v-if="
                                                                u.id ===
                                                                currentUser.id
                                                            "
                                                            class="ml-1 text-xs text-slate-400"
                                                            >(Tú)</span
                                                        >
                                                    </div>

                                                    <div
                                                        class="truncate text-xs text-slate-500 dark:text-slate-400"
                                                    >
                                                        {{ u.email }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4">
                                            <span
                                                v-if="
                                                    u.id ===
                                                        currentUser.id ||
                                                    u.role === 'owner'
                                                "
                                                class="inline-flex h-8 items-center rounded-full border border-white/55 bg-white/40 px-3 text-xs font-semibold text-slate-700 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-slate-300"
                                            >
                                                {{
                                                    u.role === 'owner'
                                                        ? 'Owner'
                                                        : u.role === 'admin'
                                                          ? 'Administrador'
                                                          : 'Miembro'
                                                }}
                                            </span>

                                            <select
                                                v-else
                                                :value="u.role"
                                                class="h-8 cursor-pointer rounded-full border-0 bg-white/55 px-3 text-xs font-semibold text-slate-900 outline-none ring-1 ring-slate-200/70 transition hover:bg-white/80 focus:ring-2 focus:ring-[#0072A8] dark:bg-white/[0.06] dark:text-slate-100 dark:ring-white/[0.08] dark:hover:bg-white/[0.09] dark:focus:ring-[#0072A8]"
                                                @change="
                                                    updateRole(
                                                        u.id,
                                                        $event.target.value
                                                    )
                                                "
                                            >
                                                <option
                                                    value="admin"
                                                    class="bg-white text-slate-900 dark:bg-[#1c1c1e] dark:text-white"
                                                >
                                                    Administrador
                                                </option>
                                                <option
                                                    value="member"
                                                    class="bg-white text-slate-900 dark:bg-[#1c1c1e] dark:text-white"
                                                >
                                                    Miembro
                                                </option>
                                            </select>
                                        </td>

                                        <td
                                            class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400"
                                        >
                                            {{ u.created_at }}
                                        </td>

                                        <td class="px-6 py-4 text-right">
                                            <button
                                                v-if="
                                                    u.id !==
                                                        currentUser.id &&
                                                    u.role !== 'owner'
                                                "
                                                class="rounded-xl p-2 text-slate-400 transition hover:bg-rose-500/10 hover:text-rose-500 active:scale-90 dark:hover:bg-rose-400/10 dark:hover:text-rose-300"
                                                title="Remover miembro"
                                                @click="deleteUser(u.id)"
                                            >
                                                <TrashIcon class="h-5 w-5" />
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Estado vacío -->
                    <div
                        v-if="groupedUsers.length === 0"
                        class="rounded-[24px] border border-dashed border-slate-200/80 py-16 text-center dark:border-white/[0.07]"
                    >
                        <BuildingOfficeIcon
                            class="mx-auto h-10 w-10 text-slate-300 dark:text-slate-600"
                        />

                        <p
                            class="mt-4 text-sm font-medium text-slate-700 dark:text-slate-200"
                        >
                            Aún no hay empresas ni miembros
                        </p>

                        <p
                            class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                        >
                            Crea la primera empresa para empezar.
                        </p>
                    </div>
                </template>

                <!-- ============================================
                     Vista NO SUPEROWNER: tabla plana
                ============================================= -->
                <template v-else>
                    <div
                        class="overflow-hidden rounded-[28px] border border-white/65 bg-white/35 shadow-[0_22px_70px_rgba(15,23,42,0.06),inset_0_1px_2px_rgba(255,255,255,0.85),inset_0_-2px_5px_rgba(0,0,0,0.06)] backdrop-blur-xl dark:border-white/[0.08] dark:bg-[#101014]/65 dark:shadow-[0_22px_70px_rgba(0,0,0,0.28),inset_0_1px_2px_rgba(255,255,255,0.16),inset_0_-2px_6px_rgba(0,0,0,0.5)]"
                    >
                        <div
                            class="border-b border-black/[0.055] p-6 dark:border-white/[0.07]"
                        >
                            <h3
                                class="text-lg font-semibold tracking-[-0.02em] text-slate-950 dark:text-white"
                            >
                                Directorio de Usuarios
                            </h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full table-fixed text-left text-sm">
                                <colgroup>
                                    <col class="w-[42%]" />
                                    <col class="w-[22%]" />
                                    <col class="w-[22%]" />
                                    <col class="w-[14%]" />
                                </colgroup>

                                <thead
                                    class="border-b border-black/[0.055] dark:border-white/[0.07]"
                                >
                                    <tr
                                        class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                                    >
                                        <th class="px-6 py-4">Usuario</th>
                                        <th class="px-6 py-4">
                                            Rol Asignado
                                        </th>
                                        <th class="px-6 py-4">
                                            Miembro Desde
                                        </th>
                                        <th class="px-6 py-4 text-right">
                                            Acción
                                        </th>
                                    </tr>
                                </thead>

                                <tbody
                                    class="divide-y divide-black/[0.045] dark:divide-white/[0.06]"
                                >
                                    <tr
                                        v-for="u in users"
                                        :key="u.id"
                                        class="transition hover:bg-black/[0.02] dark:hover:bg-white/[0.03]"
                                    >
                                        <td class="px-6 py-4">
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <div
                                                    class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full text-sm font-bold text-white shadow-sm"
                                                    style="background: linear-gradient(135deg, #0072A8 0%, #21B24B 100%)"
                                                >
                                                    <img
                                                        v-if="u.avatar"
                                                        :src="u.avatar"
                                                        class="h-full w-full object-cover"
                                                    />

                                                    <span v-else>
                                                        {{
                                                            u.name
                                                                .charAt(0)
                                                                .toUpperCase()
                                                        }}
                                                    </span>
                                                </div>

                                                <div class="min-w-0">
                                                    <div
                                                        class="truncate font-semibold text-slate-950 dark:text-white"
                                                    >
                                                        {{ u.name }}
                                                        <span
                                                            v-if="
                                                                u.id ===
                                                                currentUser.id
                                                            "
                                                            class="ml-1 text-xs text-slate-400"
                                                            >(Tú)</span
                                                        >
                                                    </div>

                                                    <div
                                                        class="truncate text-xs text-slate-500 dark:text-slate-400"
                                                    >
                                                        {{ u.email }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4">
                                            <span
                                                class="inline-flex h-8 items-center rounded-full border border-white/55 bg-white/40 px-3 text-xs font-semibold text-slate-700 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-slate-300"
                                            >
                                                {{
                                                    u.role === 'owner'
                                                        ? 'Owner'
                                                        : u.role === 'admin'
                                                          ? 'Administrador'
                                                          : 'Miembro'
                                                }}
                                            </span>
                                        </td>

                                        <td
                                            class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400"
                                        >
                                            {{ u.created_at }}
                                        </td>

                                        <td class="px-6 py-4 text-right">
                                            <button
                                                v-if="
                                                    u.id !==
                                                        currentUser.id &&
                                                    u.role !== 'owner'
                                                "
                                                class="rounded-xl p-2 text-slate-400 transition hover:bg-rose-500/10 hover:text-rose-500 active:scale-90 dark:hover:bg-rose-400/10 dark:hover:text-rose-300"
                                                @click="deleteUser(u.id)"
                                            >
                                                <TrashIcon class="h-5 w-5" />
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
            </div>

            <!-- =====================================================
                 TAB 2: SOLICITUDES
            ====================================================== -->
            <div
                v-if="activeTab === 'requests'"
                class="animate-in fade-in slide-in-from-bottom-2 duration-300 space-y-6"
            >
                <div class="w-full sm:w-80">
                    <SegmentedControl
                        v-model="requestStatusFilter"
                        :options="requestStatusOptions"
                    />
                </div>

                <div
                    v-if="filteredRequests.length > 0"
                    class="grid gap-6 md:grid-cols-2 xl:grid-cols-3"
                >
                    <div
                        v-for="req in filteredRequests"
                        :key="req.uuid"
                        class="flex flex-col justify-between rounded-[24px] border border-white/65 bg-white/35 p-6 shadow-[0_14px_40px_rgba(15,23,42,0.04),inset_0_1px_2px_rgba(255,255,255,0.85),inset_0_-2px_5px_rgba(0,0,0,0.06)] backdrop-blur-xl transition duration-300 hover:-translate-y-1 hover:bg-white/55 dark:border-white/[0.08] dark:bg-[#101014]/65 dark:shadow-[0_14px_40px_rgba(0,0,0,0.25),inset_0_1px_2px_rgba(255,255,255,0.16),inset_0_-2px_6px_rgba(0,0,0,0.5)] dark:hover:bg-[#101014]/80"
                    >
                        <div>
                            <div
                                class="flex items-start justify-between gap-3"
                            >
                                <div class="min-w-0">
                                    <h3
                                        class="truncate text-lg font-semibold tracking-[-0.02em] text-slate-950 dark:text-white"
                                    >
                                        {{ req.name }}
                                    </h3>

                                    <p
                                        class="truncate text-sm text-[#0072A8] dark:text-[#4FC3F7]"
                                    >
                                        {{ req.email }}
                                    </p>
                                </div>

                                <span
                                    v-if="req.status !== 'pending'"
                                    class="shrink-0 rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.12em]"
                                    :class="
                                        req.status === 'approved'
                                            ? 'bg-emerald-500/10 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300'
                                            : 'bg-rose-500/10 text-rose-700 dark:bg-rose-400/10 dark:text-rose-300'
                                    "
                                >
                                    {{
                                        req.status === 'approved'
                                            ? 'Aprobada'
                                            : 'Rechazada'
                                    }}
                                </span>
                            </div>

                            <div
                                class="mt-4 text-sm text-slate-500 dark:text-slate-400"
                            >
                                <p
                                    v-if="req.message"
                                    class="mb-2 italic"
                                >
                                    "{{ req.message }}"
                                </p>

                                <div
                                    v-if="isSuperOwner"
                                    class="mb-1 text-xs"
                                >
                                    <span
                                        class="font-medium text-slate-700 dark:text-slate-300"
                                        >Empresa destino:</span
                                    >
                                    {{ req.company_name }}
                                </div>
                            </div>
                        </div>

                        <div
                            class="mt-6 flex items-center justify-between border-t border-black/[0.055] pt-4 dark:border-white/[0.07]"
                        >
                            <span
                                class="text-xs text-slate-400 dark:text-slate-500"
                            >
                                Solicitado: {{ req.requested_at }}
                            </span>

                            <div
                                v-if="req.status === 'pending'"
                                class="flex items-center gap-2"
                            >
                                <button
                                    class="rounded-full bg-white/55 px-4 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200/70 transition hover:bg-white/80 active:scale-95 dark:bg-white/[0.06] dark:text-slate-300 dark:ring-white/[0.08] dark:hover:bg-white/[0.09]"
                                    @click="rejectRequest(req.uuid)"
                                >
                                    Rechazar
                                </button>

                                <button
                                    class="rounded-full bg-[#0072A8] px-4 py-1.5 text-xs font-semibold text-white shadow-[0_6px_20px_rgba(0,114,168,0.35)] transition hover:bg-[#005E8A] active:scale-95"
                                    @click="approveRequest(req.uuid)"
                                >
                                    Aprobar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="rounded-[24px] border border-dashed border-slate-200/80 py-12 text-center dark:border-white/[0.07]"
                >
                    <p
                        class="text-sm text-slate-500 dark:text-slate-400"
                    >
                        No hay solicitudes registradas en este estado.
                    </p>
                </div>
            </div>
        </div>

        <!-- =====================================================
             MODAL DE INVITACIÓN
        ====================================================== -->
        <div
            v-if="showInviteModal"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-0"
        >
            <div
                class="absolute inset-0 bg-black/55 backdrop-blur-md transition-opacity"
                @click="showInviteModal = false"
            ></div>

            <div
                class="relative w-full max-w-md transform overflow-hidden rounded-[32px] border border-white/65 bg-white/85 p-6 text-left shadow-[0_24px_80px_rgba(15,23,42,0.25),inset_0_1px_2px_rgba(255,255,255,0.9),inset_0_-3px_8px_rgba(0,0,0,0.06)] backdrop-blur-2xl transition-all dark:border-white/[0.08] dark:bg-[#101014]/85 dark:shadow-[0_24px_80px_rgba(0,0,0,0.5),inset_0_1px_2px_rgba(255,255,255,0.16),inset_0_-3px_8px_rgba(0,0,0,0.5)] sm:p-8"
            >
                <div class="mb-6 flex items-start justify-between gap-3">
                    <div>
                        <h3
                            class="text-xl font-semibold tracking-[-0.025em] text-slate-950 dark:text-white"
                        >
                            {{
                                isSuperOwner
                                    ? 'Invitar Usuario o Empresa'
                                    : 'Invitar al Equipo'
                            }}
                        </h3>

                        <p
                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                        >
                            {{
                                isSuperOwner
                                    ? 'Registra una nueva empresa o añade colaboradores a una existente.'
                                    : 'Agrega un nuevo colaborador a tu organización.'
                            }}
                        </p>
                    </div>

                    <button
                        class="rounded-full bg-white/55 p-2 text-slate-500 ring-1 ring-slate-200/70 transition hover:bg-white/80 hover:text-slate-900 dark:bg-white/[0.05] dark:text-slate-400 dark:ring-white/[0.08] dark:hover:bg-white/[0.09] dark:hover:text-white"
                        @click="showInviteModal = false"
                    >
                        <XMarkIcon class="h-4 w-4" />
                    </button>
                </div>

                <div
                    v-if="!isSuperOwner"
                    class="mb-5 flex items-center gap-3 rounded-2xl bg-[#0072A8]/10 p-3.5 text-xs text-[#0072A8] dark:bg-[#0072A8]/20 dark:text-[#4FC3F7]"
                >
                    <BuildingOffice2Icon class="h-5 w-5 shrink-0" />

                    <div>
                        Empresa actual:
                        <strong class="font-semibold">{{
                            userCompany
                        }}</strong>
                    </div>
                </div>

                <form class="space-y-5" @submit.prevent="submitInvite">
                    <template v-if="isSuperOwner">
                        <FormField
                            label="Empresa destino"
                            :icon="BuildingOffice2Icon"
                            required
                        >
                            <SelectField
                                v-model="inviteForm.company_id"
                                :options="companySelectOptions"
                            />
                        </FormField>

                        <FormField
                            v-if="inviteForm.company_id === 'new'"
                            label="Nombre de la nueva empresa"
                            :icon="BuildingOffice2Icon"
                            required
                            :error="inviteForm.errors.new_company_name"
                        >
                            <TextField
                                v-model="inviteForm.new_company_name"
                                placeholder="Ej. Mi Agencia Tech"
                                required
                            />
                        </FormField>
                    </template>

                    <FormField
                        label="Correo electrónico del usuario"
                        :icon="EnvelopeIcon"
                        required
                        :error="inviteForm.errors.email"
                    >
                        <TextField
                            v-model="inviteForm.email"
                            type="email"
                            placeholder="correo@ejemplo.com"
                            required
                        />
                    </FormField>

                    <FormField label="Rol asignado" :icon="UserIcon">
                        <SelectField
                            v-model="inviteForm.role"
                            :options="roleOptions"
                        />
                    </FormField>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <SecondaryButton
                            type="button"
                            @click="showInviteModal = false"
                        >
                            Cancelar
                        </SecondaryButton>

                        <PrimaryButton
                            type="submit"
                            :loading="inviteForm.processing"
                        >
                            {{
                                isSuperOwner &&
                                inviteForm.company_id === 'new'
                                    ? 'Crear Empresa e Invitar'
                                    : 'Enviar Invitación'
                            }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>

        <!-- =====================================================
             MODAL CRUD DE EMPRESAS
        ====================================================== -->
        <div
            v-if="showCompanyModal"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-0"
        >
            <div
                class="absolute inset-0 bg-black/55 backdrop-blur-md transition-opacity"
                @click="showCompanyModal = false"
            ></div>

            <div
                class="relative w-full max-w-md transform overflow-hidden rounded-[32px] border border-white/65 bg-white/85 p-6 text-left shadow-[0_24px_80px_rgba(15,23,42,0.25),inset_0_1px_2px_rgba(255,255,255,0.9),inset_0_-3px_8px_rgba(0,0,0,0.06)] backdrop-blur-2xl transition-all dark:border-white/[0.08] dark:bg-[#101014]/85 dark:shadow-[0_24px_80px_rgba(0,0,0,0.5),inset_0_1px_2px_rgba(255,255,255,0.16),inset_0_-3px_8px_rgba(0,0,0,0.5)] sm:p-8"
            >
                <div class="mb-6 flex items-start justify-between gap-3">
                    <div>
                        <h3
                            class="text-xl font-semibold tracking-[-0.025em] text-slate-950 dark:text-white"
                        >
                            {{
                                isEditingCompany
                                    ? 'Editar Empresa'
                                    : 'Registrar Empresa'
                            }}
                        </h3>

                        <p
                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                        >
                            {{
                                isEditingCompany
                                    ? 'Modifica los datos del entorno B2B.'
                                    : 'Crea un nuevo entorno para un cliente.'
                            }}
                        </p>
                    </div>

                    <button
                        class="rounded-full bg-white/55 p-2 text-slate-500 ring-1 ring-slate-200/70 transition hover:bg-white/80 hover:text-slate-900 dark:bg-white/[0.05] dark:text-slate-400 dark:ring-white/[0.08] dark:hover:bg-white/[0.09] dark:hover:text-white"
                        @click="showCompanyModal = false"
                    >
                        <XMarkIcon class="h-4 w-4" />
                    </button>
                </div>

                <form class="space-y-5" @submit.prevent="submitCompany">
                    <FormField
                        label="Nombre de la empresa"
                        :icon="BuildingOffice2Icon"
                        required
                        :error="companyForm.errors.name"
                    >
                        <TextField
                            v-model="companyForm.name"
                            placeholder="Ej. DominioMatic Corp"
                            required
                        />
                    </FormField>

                    <FormField
                        label="Slug (URL/Subdominio)"
                        :icon="BuildingOffice2Icon"
                        required
                        :error="companyForm.errors.slug"
                    >
                        <TextField
                            v-model="companyForm.slug"
                            placeholder="ej-dominiomatic-corp"
                            required
                        />
                    </FormField>

                    <FormField label="Estado Operativo">
                        <SelectField
                            v-model="companyForm.is_active"
                            :options="[
                                { label: 'Activa', value: 1 },
                                { label: 'Inactiva', value: 0 },
                            ]"
                        />
                    </FormField>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <SecondaryButton
                            type="button"
                            @click="showCompanyModal = false"
                        >
                            Cancelar
                        </SecondaryButton>

                        <PrimaryButton
                            type="submit"
                            :loading="companyForm.processing"
                        >
                            {{
                                isEditingCompany
                                    ? 'Guardar Cambios'
                                    : 'Crear Empresa'
                            }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>