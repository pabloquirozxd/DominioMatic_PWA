<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    computed,
    nextTick,
    onMounted,
    onUnmounted,
    ref,
} from 'vue';
import liquidGL from 'liquid-gl';

defineOptions({
    layout: AuthenticatedLayout,
});

const page = usePage();

const props = defineProps({
    overview: {
        type: Object,
        default: () => ({
            contacts: 0,
            products: 0,
            subscriptions: 0,
        }),
    },
    subscriptionStats: {
        type: Object,
        default: () => ({
            total: 0,
            active: 0,
            expired: 0,
            suspended: 0,
            expiringToday: 0,
            expiring7Days: 0,
            expiring30Days: 0,
            distribution: [],
        }),
    },
    subscriptionTrend: {
        type: Array,
        default: () => [],
    },
    subscriptionValueByCurrency: {
        type: Array,
        default: () => [],
    },
    subscriptionAlerts: {
        type: Array,
        default: () => [],
    },
    topProducts: {
        type: Array,
        default: () => [],
    },
    inventoryStats: {
        type: Object,
        default: () => ({
            units: 0,
            finiteProducts: 0,
            lowStock: 0,
            outOfStock: 0,
            valueByCurrency: [],
        }),
    },
    lowStockProducts: {
        type: Array,
        default: () => [],
    },
});

/* ------------------------------------------------------------------ */
/* Usuario                                                             */
/* ------------------------------------------------------------------ */

const userName = computed(() => {
    return page.props.auth?.user?.name || 'Usuario';
});

/* ------------------------------------------------------------------ */
/* Tenant                                                              */
/* ------------------------------------------------------------------ */

const tenantInfo = computed(() => {
    const user = page.props.auth?.user;

    return (
        user?.company ||
        page.props.auth?.company ||
        page.props.company ||
        page.props.tenant ||
        null
    );
});

const isMainTenant = computed(() => {
    const user = page.props.auth?.user;
    const tenant = tenantInfo.value;

    const slug = String(
        tenant?.slug || user?.tenant?.slug || ''
    )
        .trim()
        .toLowerCase();

    const name = String(
        tenant?.name ||
        user?.company_name ||
        user?.tenant?.name ||
        ''
    )
        .trim()
        .toLowerCase();

    return (
        user?.is_dominiomatic === true ||
        tenant?.is_dominiomatic === true ||
        user?.tenant?.is_dominiomatic === true ||
        slug === 'dominiomatic' ||
        name === 'dominiomatic' ||
        user?.company_id === 1
    );
});

const tenantName = computed(() => {
    return (
        tenantInfo.value?.name ||
        page.props.auth?.user?.company_name ||
        page.props.auth?.user?.tenant?.name ||
        (isMainTenant.value ? 'DominioMatic' : 'Organización')
    );
});

const tenantInitial = computed(() => {
    const name = String(tenantName.value).trim();
    return name.charAt(0).toUpperCase() || 'U';
});

/* ------------------------------------------------------------------ */
/* Saludo                                                              */
/* ------------------------------------------------------------------ */

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Buenos días';
    if (hour < 19) return 'Buenas tardes';
    return 'Buenas noches';
});

/* ------------------------------------------------------------------ */
/* Suscripciones                                                       */
/* ------------------------------------------------------------------ */

const subscriptionStatus = computed(() => {
    return [
        {
            key: 'active',
            label: 'Activas',
            value: Number(props.subscriptionStats.active || 0),
            dot: 'bg-sky-500 dark:bg-sky-400',
        },
        {
            key: 'expired',
            label: 'Vencidas',
            value: Number(props.subscriptionStats.expired || 0),
            dot: 'bg-rose-500 dark:bg-rose-400',
        },
        {
            key: 'suspended',
            label: 'Suspendidas',
            value: Number(props.subscriptionStats.suspended || 0),
            dot: 'bg-slate-500 dark:bg-slate-400',
        },
    ];
});

const activeSubscriptionPercentage = computed(() => {
    const total = Number(props.subscriptionStats.total || 0);
    if (total <= 0) return 0;

    return Math.round(
        (Number(props.subscriptionStats.active || 0) / total) * 100
    );
});

const donutCircumference = 2 * Math.PI * 48;

const activeDash = computed(() => {
    const visible =
        (activeSubscriptionPercentage.value / 100) *
        donutCircumference;

    return `${visible} ${donutCircumference}`;
});

/* ------------------------------------------------------------------ */
/* Atención                                                            */
/* ------------------------------------------------------------------ */

const attentionCount = computed(() => {
    return (
        Number(props.subscriptionStats.expiring30Days || 0) +
        Number(props.inventoryStats.lowStock || 0) +
        Number(props.inventoryStats.outOfStock || 0)
    );
});

const attentionLevel = computed(() => {
    const expired = Number(props.subscriptionStats.expired || 0);
    const outOfStock = Number(props.inventoryStats.outOfStock || 0);

    if (expired > 0 || outOfStock > 0) return 'critical';
    if (attentionCount.value > 0) return 'attention';
    return 'clear';
});

/* ------------------------------------------------------------------ */
/* Trend chart                                                         */
/* ------------------------------------------------------------------ */

const trendChart = {
    width: 760,
    height: 250,
    paddingX: 24,
    paddingTop: 18,
    paddingBottom: 24,
};

const trendValues = computed(() => {
    return props.subscriptionTrend.map((item) =>
        Math.max(0, Number(item.subscriptions || 0))
    );
});

const trendMax = computed(() => {
    const max = Math.max(...trendValues.value, 0);
    return max > 0 ? max : 1;
});

function getTrendX(index) {
    const count = trendValues.value.length;
    const usableWidth = trendChart.width - trendChart.paddingX * 2;

    if (count <= 1) return trendChart.width / 2;

    return trendChart.paddingX + (index / (count - 1)) * usableWidth;
}

function getTrendY(value) {
    const usableHeight =
        trendChart.height -
        trendChart.paddingTop -
        trendChart.paddingBottom;

    return (
        trendChart.paddingTop +
        usableHeight -
        (value / trendMax.value) * usableHeight
    );
}

const trendPoints = computed(() => {
    return trendValues.value
        .map((value, index) => `${getTrendX(index)},${getTrendY(value)}`)
        .join(' ');
});

const trendAreaPoints = computed(() => {
    if (!trendPoints.value) return '';

    const baseline = trendChart.height - trendChart.paddingBottom;

    return [
        `${trendChart.paddingX},${baseline}`,
        trendPoints.value,
        `${trendChart.width - trendChart.paddingX},${baseline}`,
    ].join(' ');
});

const trendCircles = computed(() => {
    return trendValues.value.map((value, index) => ({
        x: getTrendX(index),
        y: getTrendY(value),
    }));
});

const trendGridLines = computed(() => {
    const usableHeight =
        trendChart.height -
        trendChart.paddingTop -
        trendChart.paddingBottom;

    return [
        trendChart.paddingTop,
        trendChart.paddingTop + usableHeight / 2,
        trendChart.paddingTop + usableHeight,
    ];
});

const hasTrendData = computed(() => {
    return trendValues.value.some((value) => value > 0);
});

/* ------------------------------------------------------------------ */
/* Currency                                                            */
/* ------------------------------------------------------------------ */

function formatCurrency(value, currency) {
    const amount = Number(value || 0);

    if (!currency) {
        return amount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    try {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency,
            currencyDisplay: 'code',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(amount);
    } catch {
        return `${currency} ${amount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;
    }
}

function formatNumber(value) {
    return new Intl.NumberFormat('en-US').format(Number(value || 0));
}

/* ------------------------------------------------------------------ */
/* Inventario                                                          */
/* ------------------------------------------------------------------ */

const inventoryHealthPercentage = computed(() => {
    const finite = Number(props.inventoryStats.finiteProducts || 0);
    const low = Number(props.inventoryStats.lowStock || 0);
    const out = Number(props.inventoryStats.outOfStock || 0);

    if (finite <= 0) return 100;

    const critical = Math.min(finite, low + out);
    return Math.round(((finite - critical) / finite) * 100);
});

const inventoryHealthLabel = computed(() => {
    if (inventoryHealthPercentage.value >= 90) return 'Inventario saludable';
    if (inventoryHealthPercentage.value >= 70) return 'Requiere seguimiento';
    return 'Requiere atención';
});

const hasInventoryValue = computed(() => {
    return (
        Array.isArray(props.inventoryStats.valueByCurrency) &&
        props.inventoryStats.valueByCurrency.length > 0
    );
});

/* ------------------------------------------------------------------ */
/* Alertas                                                             */
/* ------------------------------------------------------------------ */

function alertTone(type) {
    if (type === 'overdue') {
        return {
            dot: 'bg-rose-500 dark:bg-rose-400',
            badge:
                'bg-rose-500/10 text-rose-700 dark:bg-rose-400/10 dark:text-rose-300',
            border: 'border-rose-200/70 dark:border-rose-400/10',
        };
    }

    if (type === 'today') {
        return {
            dot: 'bg-amber-500 dark:bg-amber-400',
            badge:
                'bg-amber-500/10 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300',
            border: 'border-amber-200/70 dark:border-amber-400/10',
        };
    }

    return {
        dot: 'bg-sky-500 dark:bg-sky-400',
        badge:
            'bg-sky-500/10 text-sky-700 dark:bg-sky-400/10 dark:text-sky-300',
        border: 'border-slate-200/70 dark:border-white/[0.07]',
    };
}

function topProductValue(product) {
    if (
        !Array.isArray(product?.valuesByCurrency) ||
        !product.valuesByCurrency.length
    ) {
        return null;
    }

    return product.valuesByCurrency[0];
}

/* ------------------------------------------------------------------ */
/* Liquid Glass                                                        */
/* ------------------------------------------------------------------ */

const heroGlassTarget = ref(null);
const attentionGlassTarget = ref(null);

let heroGlass = null;
let attentionGlass = null;

function createGlass(target, options = {}) {
    if (!target) return null;

    try {
        return liquidGL({
            target,
            snapshot: 'body',
            resolution: 1.0,
            refraction: 0.018,
            bevelDepth: 0.045,
            bevelWidth: 0.045,
            aberration: 0.012,
            frost: 0.18,
            shadow: true,
            specular: false,
            reveal: 'none',
            tilt: false,
            magnify: 1.02,
            ...options,
        });
    } catch (error) {
        console.error('[Dashboard LiquidGL]', error);
        return null;
    }
}

function destroyGlass(instance, target) {
    if (!instance && !target) return;

    if (instance) {
        try {
            if (typeof instance.destroy === 'function') {
                instance.destroy();
            } else if (typeof instance.dispose === 'function') {
                instance.dispose();
            }
        } catch (error) {
            console.warn('[Dashboard LiquidGL cleanup]', error);
        }
    }

    if (target) {
        target.querySelectorAll('canvas').forEach((canvas) => canvas.remove());
    }
}

function initPermanentGlass() {
    if (heroGlassTarget.value && !heroGlass) {
        heroGlass = createGlass(heroGlassTarget.value, {
            refraction: 0.018,
            bevelDepth: 0.05,
            bevelWidth: 0.05,
            aberration: 0.012,
            frost: 0.16,
            magnify: 1.02,
        });
    }

    if (attentionGlassTarget.value && !attentionGlass) {
        attentionGlass = createGlass(attentionGlassTarget.value, {
            refraction: 0.016,
            bevelDepth: 0.045,
            bevelWidth: 0.045,
            aberration: 0.01,
            frost: 0.18,
            magnify: 1.02,
        });
    }
}

/* ------------------------------------------------------------------ */
/* Tema                                                                */
/* ------------------------------------------------------------------ */

let themeObserver = null;
let themeRefreshTimer = null;

function refreshGlassForTheme() {
    if (themeRefreshTimer) {
        clearTimeout(themeRefreshTimer);
        themeRefreshTimer = null;
    }

    destroyGlass(heroGlass, heroGlassTarget.value);
    destroyGlass(attentionGlass, attentionGlassTarget.value);

    heroGlass = null;
    attentionGlass = null;

    themeRefreshTimer = setTimeout(() => {
        themeRefreshTimer = null;
        nextTick(() => {
            requestAnimationFrame(() => {
                initPermanentGlass();
            });
        });
    }, 80);
}

/* ------------------------------------------------------------------ */
/* Lifecycle                                                           */
/* ------------------------------------------------------------------ */

onMounted(async () => {
    await nextTick();

    requestAnimationFrame(() => {
        initPermanentGlass();
    });

    themeObserver = new MutationObserver((mutations) => {
        const themeChanged = mutations.some(
            (mutation) =>
                mutation.type === 'attributes' &&
                mutation.attributeName === 'class'
        );

        if (themeChanged) {
            refreshGlassForTheme();
        }
    });

    themeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
    });
});

onUnmounted(() => {
    if (themeObserver) {
        themeObserver.disconnect();
        themeObserver = null;
    }

    if (themeRefreshTimer) {
        clearTimeout(themeRefreshTimer);
        themeRefreshTimer = null;
    }

    destroyGlass(heroGlass, heroGlassTarget.value);
    destroyGlass(attentionGlass, attentionGlassTarget.value);

    heroGlass = null;
    attentionGlass = null;
});

const userRole = computed(() => page.props.auth?.user?.role || 'member');

const roleLabel = computed(() => {
    const map = {
        owner: 'Súper Owner',
        admin: 'Administrador',
        member: 'Miembro',
    };
    return map[userRole.value] || 'Usuario';
});

const roleBadgeClass = computed(() => {
    const map = {
        owner:
            'border-[#0072A8]/25 bg-[#0072A8]/10 text-[#0072A8] dark:border-[#0072A8]/30 dark:bg-[#0072A8]/15 dark:text-[#4FC3F7]',
        admin:
            'border-[#21B24B]/25 bg-[#21B24B]/10 text-[#21B24B] dark:border-[#21B24B]/30 dark:bg-[#21B24B]/15 dark:text-[#4ADE80]',
        member:
            'border-slate-200/70 bg-white/55 text-slate-600 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-slate-300',
    };
    return map[userRole.value] || map.member;
});

const roleBadgeDot = computed(() => {
    const map = {
        owner: 'bg-[#0072A8]',
        admin: 'bg-[#21B24B]',
        member: 'bg-slate-400 dark:bg-slate-500',
    };
    return map[userRole.value] || map.member;
});
</script>

<template>
    <Head title="Dashboard | DominioMatic" />

    <div
        class="min-h-screen overflow-x-hidden bg-[#f5f5f7] text-slate-950 transition-colors dark:bg-[#050507] dark:text-white"
    >
        <!-- AMBIENT BACKGROUND — DominioMatic brand gradient -->
        <div
            id="dashboard-ambient"
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
            <header class="mb-6 flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-[17px] border border-white/80 bg-white/65 shadow-[0_8px_24px_rgba(15,23,42,0.05)] dark:border-white/[0.08] dark:bg-white/[0.05]"
                        >
                            <img
                                v-if="isMainTenant"
                                src="/images/favicon_dominiomatic.png"
                                alt="DominioMatic"
                                class="h-full w-full object-contain p-1"
                            />

                            <span
                                v-else
                                class="text-sm font-semibold tracking-tight text-slate-950 dark:text-white"
                            >
                                {{ tenantInitial }}
                            </span>
                        </div>

                        <div class="min-w-0">
                            <p
                                class="truncate text-sm font-semibold tracking-[-0.02em] text-slate-950 dark:text-white"
                            >
                                {{ tenantName }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="hidden sm:flex">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.14em]"
                        :class="roleBadgeClass"
                    >
                        <span
                            class="h-1.5 w-1.5 rounded-full"
                            :class="roleBadgeDot"
                        ></span>
                        {{ roleLabel }}
                    </span>
                </div>
            </header>

            <!-- HERO + ATTENTION -->
            <section
                class="mb-10 grid gap-5 xl:grid-cols-[minmax(0,1.65fr)_minmax(320px,0.85fr)]"
            >
                <!-- HERO -->
                <div class="relative overflow-hidden rounded-[36px]">
                    <article
                        id="dashboard-hero-glass"
                        ref="heroGlassTarget"
                        class="dashboard-liquid-glass relative h-full rounded-[36px] p-1.5"
                    >
                        <div
                            class="pointer-events-none absolute inset-0 rounded-[34px] border border-white/70 bg-white/30 backdrop-blur-2xl backdrop-saturate-150 shadow-[inset_0_1px_2px_rgba(255,255,255,0.85),inset_0_-3px_8px_rgba(0,0,0,0.05)] dark:border-white/[0.08] dark:bg-[#0f0f13]/70 dark:shadow-[inset_0_1px_2px_rgba(255,255,255,0.14),inset_0_-3px_8px_rgba(0,0,0,0.6)]"
                        ></div>

                        <div
                            class="relative z-[2] overflow-hidden rounded-[29px] px-6 py-8 sm:px-9 sm:py-10"
                        >
                            <div
                                class="pointer-events-none absolute -right-28 -top-28 h-80 w-80 rounded-full bg-cyan-300/[0.055] blur-3xl dark:bg-cyan-300/[0.03]"
                            ></div>

                            <div
                                class="pointer-events-none absolute bottom-[-150px] left-[28%] h-96 w-96 rounded-full bg-violet-300/[0.045] blur-3xl dark:bg-violet-300/[0.025]"
                            ></div>

                            <div class="relative">
                                <div
                                    class="flex flex-wrap items-start justify-between gap-5"
                                >
                                    <div>
                                        <p
                                            class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-400 dark:text-slate-500"
                                        >
                                            {{ greeting }}
                                        </p>

                                        <h1
                                            class="mt-2 text-3xl font-semibold tracking-[-0.045em] text-slate-950 sm:text-4xl dark:text-white"
                                        >
                                            {{ userName }}
                                        </h1>

                                        <p
                                            class="mt-2 max-w-xl text-sm leading-6 text-slate-500 dark:text-slate-400"
                                        >
                                            Una vista clara del estado actual de tu empresa.
                                        </p>
                                    </div>

                                    <Link
                                        href="/subscriptions"
                                        class="inline-flex items-center gap-2 rounded-full border border-white/70 bg-white/48 px-3.5 py-2 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-white/70 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-slate-300 dark:hover:bg-white/[0.07]"
                                    >
                                        Ver suscripciones
                                        <span class="text-slate-400 dark:text-slate-500">→</span>
                                    </Link>
                                </div>

                                <div
                                    class="mt-9 grid gap-8 md:grid-cols-[auto_minmax(0,1fr)] md:items-center"
                                >
                                    <div
                                        class="relative flex h-36 w-36 shrink-0 items-center justify-center sm:h-40 sm:w-40"
                                    >
                                        <svg
                                            class="h-full w-full -rotate-90"
                                            viewBox="0 0 120 120"
                                            aria-hidden="true"
                                        >
                                            <circle
                                                cx="60"
                                                cy="60"
                                                r="48"
                                                fill="none"
                                                class="stroke-slate-200/80 dark:stroke-white/[0.07]"
                                                stroke-width="7"
                                            />

                                            <circle
                                                cx="60"
                                                cy="60"
                                                r="48"
                                                fill="none"
                                                class="stroke-cyan-500 dark:stroke-cyan-400"
                                                stroke-width="7"
                                                stroke-linecap="round"
                                                :stroke-dasharray="activeDash"
                                            />
                                        </svg>

                                        <div
                                            class="absolute inset-0 flex flex-col items-center justify-center"
                                        >
                                            <span
                                                class="text-4xl font-semibold tracking-[-0.055em] text-slate-950 dark:text-white"
                                            >
                                                {{ subscriptionStats.active || 0 }}
                                            </span>

                                            <span
                                                class="mt-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500"
                                            >
                                                activas
                                            </span>
                                        </div>
                                    </div>

                                    <div class="min-w-0">
                                        <div
                                            class="flex flex-wrap items-end justify-between gap-5"
                                        >
                                            <div>
                                                <p
                                                    class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                                >
                                                    Suscripciones registradas
                                                </p>
                                                <div class="mt-1 flex flex-wrap items-baseline gap-x-3 gap-y-1">
                                                    <span
                                                        class="text-5xl font-semibold tracking-[-0.06em] text-slate-950 dark:text-white"
                                                    >
                                                        {{ subscriptionStats.total || 0 }}
                                                    </span>

                                                    <span
                                                        class="text-sm text-slate-400 dark:text-slate-500"
                                                    >
                                                        total
                                                    </span>

                                                    <span
                                                        v-if="Number(subscriptionStats.expired || 0) > 0"
                                                        class="text-sm font-medium text-rose-500 dark:text-rose-400"
                                                    >
                                                        {{ subscriptionStats.expired }} vencidas
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="text-right">
                                                <p
                                                    class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500"
                                                >
                                                    Estado
                                                </p>

                                                <p
                                                    class="mt-1 text-sm font-semibold text-slate-700 dark:text-slate-300"
                                                >
                                                    {{ activeSubscriptionPercentage }}% activas
                                                </p>
                                            </div>
                                        </div>

                                        <div
                                            class="mt-7 grid grid-cols-3 gap-2 sm:gap-3"
                                        >
                                            <div
                                                v-for="status in subscriptionStatus"
                                                :key="status.key"
                                                class="rounded-2xl border border-white/50 bg-white/30 px-3.5 py-3.5 shadow-[inset_0_2px_4px_rgba(15,23,42,0.03)] dark:border-white/[0.06] dark:bg-white/[0.025] dark:shadow-[inset_0_2px_4px_rgba(0,0,0,0.25)]"
                                            >
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="h-1.5 w-1.5 rounded-full"
                                                        :class="status.dot"
                                                    ></span>

                                                    <span
                                                        class="text-[11px] text-slate-500 dark:text-slate-400"
                                                    >
                                                        {{ status.label }}
                                                    </span>
                                                </div>

                                                <p
                                                    class="mt-2 text-lg font-semibold tracking-tight text-slate-900 dark:text-white"
                                                >
                                                    {{ status.value }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- ATTENTION -->
                <div class="relative overflow-hidden rounded-[36px]">
                    <article
                        id="dashboard-attention-glass"
                        ref="attentionGlassTarget"
                        class="dashboard-liquid-glass relative h-full rounded-[36px] p-1.5"
                    >
                        <div
                            class="pointer-events-none absolute inset-0 rounded-[34px] border border-white/70 bg-white/30 backdrop-blur-2xl backdrop-saturate-150 shadow-[inset_0_1px_2px_rgba(255,255,255,0.85),inset_0_-3px_8px_rgba(0,0,0,0.05)] dark:border-white/[0.08] dark:bg-[#0f0f13]/70 dark:shadow-[inset_0_1px_2px_rgba(255,255,255,0.14),inset_0_-3px_8px_rgba(0,0,0,0.6)]"
                        ></div>

                        <div
                            class="relative z-[2] flex h-full min-h-[320px] flex-col overflow-hidden rounded-[29px] px-6 py-7 sm:px-7"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p
                                        class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500"
                                    >
                                        Atención
                                    </p>

                                    <h2
                                        class="mt-2 text-2xl font-semibold tracking-[-0.035em] text-slate-950 dark:text-white"
                                    >
                                        {{
                                            attentionLevel === 'clear'
                                                ? 'Todo en orden'
                                                : 'Qué revisar ahora'
                                        }}
                                    </h2>

                                    <p
                                        class="mt-1 max-w-sm text-sm leading-5 text-slate-500 dark:text-slate-400"
                                    >
                                        Prioridad real, separada por impacto.
                                    </p>
                                </div>

                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-white/70 bg-white/48 text-slate-500 shadow-sm dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-slate-300"
                                >
                                    <svg
                                        class="h-5 w-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M12 3.5 2.75 20h18.5L12 3.5Z"
                                            stroke-linejoin="round"
                                        />
                                        <path
                                            d="M12 9v4.5M12 17.25v.1"
                                            stroke-linecap="round"
                                        />
                                    </svg>
                                </div>
                            </div>

                            <!-- CONTENT -->
                            <div class="mt-8 flex-1">
                                <!-- EMPTY STATE: Todo en orden -->
                                <div
                                    v-if="attentionLevel === 'clear'"
                                    class="flex h-full min-h-[180px] flex-col items-center justify-center text-center"
                                >
                                    <div
                                        class="flex h-14 w-14 items-center justify-center rounded-full border border-emerald-500/20 bg-emerald-500/10 text-emerald-600 dark:border-emerald-400/25 dark:bg-emerald-400/10 dark:text-emerald-300"
                                    >
                                        <svg
                                            class="h-6 w-6"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2.2"
                                            aria-hidden="true"
                                        >
                                            <path
                                                d="M4.5 12.5 10 18l9.5-11"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </div>

                                    <p
                                        class="mt-4 text-sm font-medium text-slate-700 dark:text-slate-200"
                                    >
                                        Sin incidencias por revisar
                                    </p>

                                    <p
                                        class="mt-1 max-w-xs text-xs leading-5 text-slate-400 dark:text-slate-500"
                                    >
                                        Suscripciones e inventario bajo control.
                                    </p>
                                </div>

                                <!-- DETAILED STATE -->
                                <template v-else>
                                    <!-- CRITICAL -->
                                    <div
                                        class="flex items-start justify-between gap-4 border-b border-black/[0.055] pb-5 dark:border-white/[0.07]"
                                    >
                                        <div class="flex items-start gap-3">
                                            <span
                                                class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-rose-500 dark:bg-rose-400"
                                            ></span>

                                            <div>
                                                <p
                                                    class="text-[11px] font-semibold uppercase tracking-[0.14em] text-rose-600 dark:text-rose-300"
                                                >
                                                    Crítico
                                                </p>

                                                <p
                                                    class="mt-1 text-sm text-slate-600 dark:text-slate-300"
                                                >
                                                    {{ Number(subscriptionStats.expired || 0) }} vencidas ·
                                                    {{ Number(inventoryStats.outOfStock || 0) }} agotados
                                                </p>
                                            </div>
                                        </div>

                                        <span
                                            class="text-2xl font-semibold tracking-[-0.04em] text-slate-900 dark:text-white"
                                        >
                                            {{
                                                Number(subscriptionStats.expired || 0) +
                                                Number(inventoryStats.outOfStock || 0)
                                            }}
                                        </span>
                                    </div>

                                    <!-- NEXT -->
                                    <div
                                        class="flex items-start justify-between gap-4 border-b border-black/[0.055] py-5 dark:border-white/[0.07]"
                                    >
                                        <div class="flex items-start gap-3">
                                            <span
                                                class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-amber-500 dark:bg-amber-400"
                                            ></span>

                                            <div>
                                                <p
                                                    class="text-[11px] font-semibold uppercase tracking-[0.14em] text-amber-600 dark:text-amber-300"
                                                >
                                                    Próximo
                                                </p>

                                                <p
                                                    class="mt-1 text-sm text-slate-600 dark:text-slate-300"
                                                >
                                                    {{ Number(subscriptionStats.expiring7Days || 0) }} vencen en 7 días
                                                </p>
                                            </div>
                                        </div>

                                        <span
                                            class="text-2xl font-semibold tracking-[-0.04em] text-slate-900 dark:text-white"
                                        >
                                            {{ Number(subscriptionStats.expiring7Days || 0) }}
                                        </span>
                                    </div>

                                    <!-- OPERATIONAL -->
                                    <Link
                                        href="/products"
                                        class="group flex items-start justify-between gap-4 pt-5"
                                    >
                                        <div class="flex items-start gap-3">
                                            <span
                                                class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-slate-400 dark:bg-slate-500"
                                            ></span>

                                            <div>
                                                <p
                                                    class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400"
                                                >
                                                    Operativo
                                                </p>

                                                <p
                                                    class="mt-1 text-sm text-slate-600 transition group-hover:text-slate-900 dark:text-slate-300 dark:group-hover:text-white"
                                                >
                                                    Revisar inventario completo
                                                </p>
                                            </div>
                                        </div>

                                        <span
                                            class="mt-0.5 text-sm text-slate-400 transition group-hover:translate-x-0.5 dark:text-slate-500"
                                        >
                                            →
                                        </span>
                                    </Link>
                                </template>
                            </div>

                            <Link
                                v-if="attentionLevel !== 'clear'"
                                href="/subscriptions"
                                class="mt-8 inline-flex items-center justify-between rounded-2xl px-3.5 py-3 text-xs font-semibold text-slate-500 transition hover:bg-black/[0.035] hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/[0.045] dark:hover:text-white"
                            >
                                Revisar suscripciones
                                <span>→</span>
                            </Link>
                        </div>
                    </article>
                </div>
            </section>

            <!-- OVERVIEW -->
            <section
                class="mb-9 overflow-hidden rounded-[28px] border border-white/55 bg-white/25 backdrop-blur-md backdrop-saturate-150 shadow-[0_10px_40px_rgba(15,23,42,0.04),inset_0_1px_2px_rgba(255,255,255,0.7),inset_0_-2px_5px_rgba(0,0,0,0.04)] dark:border-white/[0.07] dark:bg-[#101014]/55 dark:shadow-[0_10px_40px_rgba(0,0,0,0.2),inset_0_1px_2px_rgba(255,255,255,0.1),inset_0_-2px_6px_rgba(0,0,0,0.4)]"
            >
                <div
                    class="grid grid-cols-2 divide-x divide-black/[0.055] sm:grid-cols-3 dark:divide-white/[0.06]"
                >
                    <Link
                        href="/contacts"
                        class="group px-4 py-5 transition hover:bg-black/[0.02] dark:hover:bg-white/[0.02] sm:px-6"
                    >
                        <p
                            class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500"
                        >
                            Contactos
                        </p>

                        <div class="mt-2 flex items-baseline gap-2">
                            <span
                                class="text-2xl font-semibold tracking-[-0.045em] text-slate-950 dark:text-white"
                            >
                                {{ overview.contacts || 0 }}
                            </span>

                            <span class="text-xs text-slate-400 dark:text-slate-500">registrados</span>
                        </div>
                    </Link>

                    <Link
                        href="/products"
                        class="group px-4 py-5 transition hover:bg-black/[0.02] dark:hover:bg-white/[0.02] sm:px-6"
                    >
                        <p
                            class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500"
                        >
                            Productos
                        </p>

                        <div class="mt-2 flex items-baseline gap-2">
                            <span
                                class="text-2xl font-semibold tracking-[-0.045em] text-slate-950 dark:text-white"
                            >
                                {{ overview.products || 0 }}
                            </span>

                            <span class="text-xs text-slate-400 dark:text-slate-500">registrados</span>
                        </div>
                    </Link>

                    <Link
                        href="/subscriptions"
                        class="group col-span-2 px-4 py-5 transition hover:bg-black/[0.02] dark:hover:bg-white/[0.02] sm:col-span-1 sm:px-6"
                    >
                        <p
                            class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500"
                        >
                            Vencen hoy
                        </p>

                        <div class="mt-2 flex items-baseline gap-2">
                            <span
                                class="text-2xl font-semibold tracking-[-0.045em]"
                                :class="
                                    Number(subscriptionStats.expiringToday || 0) > 0
                                        ? 'text-amber-600 dark:text-amber-400'
                                        : 'text-slate-950 dark:text-white'
                                "
                            >
                                {{ subscriptionStats.expiringToday || 0 }}
                            </span>

                            <span class="text-xs text-slate-400 dark:text-slate-500">suscripciones</span>
                        </div>
                    </Link>
                </div>
            </section>

            <!-- TREND -->
            <section
                class="mb-9 rounded-[28px] border border-white/55 bg-white/25 p-6 backdrop-blur-md backdrop-saturate-150 shadow-[0_10px_40px_rgba(15,23,42,0.04),inset_0_1px_2px_rgba(255,255,255,0.7),inset_0_-2px_5px_rgba(0,0,0,0.04)] dark:border-white/[0.07] dark:bg-[#101014]/55 dark:shadow-[0_10px_40px_rgba(0,0,0,0.2),inset_0_1px_2px_rgba(255,255,255,0.1),inset_0_-2px_6px_rgba(0,0,0,0.4)] sm:p-7"
            >
                <div class="flex flex-wrap items-end justify-between gap-5">
                    <div>
                        <p
                            class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500"
                        >
                            Evolución
                        </p>

                        <h2
                            class="mt-2 text-2xl font-semibold tracking-[-0.035em] text-slate-950 dark:text-white"
                        >
                            Nuevas suscripciones
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Actividad registrada durante los últimos seis meses.
                        </p>
                    </div>

                    <span class="text-xs font-medium text-slate-400 dark:text-slate-500">
                        6 meses
                    </span>
                </div>

                <div v-if="hasTrendData" class="mt-7">
                    <svg
                        viewBox="0 0 760 250"
                        preserveAspectRatio="xMidYMid meet"
                        class="block h-auto max-h-[200px] w-full"
                        aria-label="Tendencia de nuevas suscripciones"
                    >
                        <defs>
                            <linearGradient
                                id="dashboardTrendGradient"
                                x1="0"
                                x2="0"
                                y1="0"
                                y2="1"
                            >
                                <stop offset="0%" stop-color="rgba(34,211,238,0.14)" />
                                <stop offset="100%" stop-color="rgba(34,211,238,0)" />
                            </linearGradient>
                        </defs>

                        <line
                            v-for="y in trendGridLines"
                            :key="`grid-${y}`"
                            :x1="trendChart.paddingX"
                            :y1="y"
                            :x2="trendChart.width - trendChart.paddingX"
                            :y2="y"
                            class="stroke-slate-200/65 dark:stroke-white/[0.045]"
                            stroke-width="1"
                            vector-effect="non-scaling-stroke"
                        />

                        <polygon
                            :points="trendAreaPoints"
                            fill="url(#dashboardTrendGradient)"
                        />

                        <polyline
                            :points="trendPoints"
                            fill="none"
                            class="stroke-cyan-500 dark:stroke-cyan-400"
                            stroke-width="3"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            vector-effect="non-scaling-stroke"
                        />

                        <circle
                            v-for="(point, index) in trendCircles"
                            :key="`point-${index}`"
                            :cx="point.x"
                            :cy="point.y"
                            r="3.6"
                            class="fill-[#f5f5f7] stroke-cyan-500 dark:fill-[#050507] dark:stroke-cyan-400"
                            stroke-width="2.2"
                            vector-effect="non-scaling-stroke"
                        />
                    </svg>

                    <div
                        class="mt-2 grid gap-1"
                        :style="{
                            gridTemplateColumns: `repeat(${Math.max(
                                1,
                                subscriptionTrend.length
                            )}, minmax(0, 1fr))`,
                        }"
                    >
                        <div
                            v-for="item in subscriptionTrend"
                            :key="`label-${item.month}`"
                            class="min-w-0 text-center"
                        >
                            <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500">
                                {{ item.label }}
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200">
                                {{ item.subscriptions }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="mt-7 flex min-h-[200px] items-center justify-center border border-dashed border-slate-200/80 px-6 text-center dark:border-white/[0.07]"
                >
                    <div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">
                            Todavía no hay suficiente actividad
                        </p>

                        <p class="mt-1 max-w-md text-xs leading-5 text-slate-400 dark:text-slate-500">
                            Cuando existan nuevas suscripciones, la evolución mensual aparecerá aquí.
                        </p>
                    </div>
                </div>
            </section>

            <!-- VALUE + INVENTORY -->
            <section
                class="mb-9 grid gap-8 rounded-[28px] border border-white/55 bg-white/25 p-6 backdrop-blur-md backdrop-saturate-150 shadow-[0_10px_40px_rgba(15,23,42,0.04),inset_0_1px_2px_rgba(255,255,255,0.7),inset_0_-2px_5px_rgba(0,0,0,0.04)] dark:border-white/[0.07] dark:bg-[#101014]/55 dark:shadow-[0_10px_40px_rgba(0,0,0,0.2),inset_0_1px_2px_rgba(255,255,255,0.1),inset_0_-2px_6px_rgba(0,0,0,0.4)] sm:p-7 lg:grid-cols-[1fr_auto_1fr]"
            >
                <!-- VALUE -->
                <div class="min-w-0">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p
                                class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500"
                            >
                                Valor registrado
                            </p>

                            <h2
                                class="mt-2 text-2xl font-semibold tracking-[-0.035em] text-slate-950 dark:text-white"
                            >
                                Suscripciones
                            </h2>
                        </div>
                    </div>

                    <div
                        v-if="subscriptionValueByCurrency.length"
                        class="mt-7 space-y-4"
                    >
                        <div
                            v-for="item in subscriptionValueByCurrency"
                            :key="item.currency"
                            class="flex items-center justify-between gap-4 border-b border-black/[0.055] pb-4 last:border-b-0 last:pb-0 dark:border-white/[0.06]"
                        >
                            <div>
                                <p class="text-xs font-medium text-slate-400 dark:text-slate-500">
                                    {{ item.currency }}
                                </p>

                                <p class="mt-1 text-2xl font-semibold tracking-[-0.04em] text-slate-900 dark:text-white">
                                    {{ formatCurrency(item.total, item.currency) }}
                                </p>
                            </div>

                            <span
                                class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                            >
                                registrado
                            </span>
                        </div>
                    </div>

                    <div
                        v-else
                        class="mt-7 border-y border-dashed border-slate-200/80 py-5 text-sm text-slate-400 dark:border-white/[0.07] dark:text-slate-500"
                    >
                        Todavía no hay valores monetarios registrados.
                    </div>
                </div>

                <div
                    class="hidden w-px bg-black/[0.06] lg:block dark:bg-white/[0.08]"
                ></div>

                <!-- INVENTORY -->
                <div class="min-w-0">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p
                                class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500"
                            >
                                Inventario
                            </p>

                            <h2
                                class="mt-2 text-2xl font-semibold tracking-[-0.035em] text-slate-950 dark:text-white"
                            >
                                Estado general
                            </h2>
                        </div>

                        <Link
                            href="/products"
                            class="text-xs font-semibold text-slate-400 transition hover:text-slate-900 dark:text-slate-500 dark:hover:text-white"
                        >
                            Ver productos →
                        </Link>
                    </div>

                    <div class="mt-7">
                        <div class="flex items-end justify-between gap-5">
                            <div>
                                <p
                                    class="text-4xl font-semibold tracking-[-0.055em] text-slate-950 dark:text-white"
                                >
                                    {{ formatNumber(inventoryStats.units) }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                    unidades físicas finitas
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="text-2xl font-semibold tracking-[-0.04em] text-slate-900 dark:text-white">
                                    {{ inventoryHealthPercentage }}%
                                </p>

                                <p class="mt-1 text-[10px] text-slate-400 dark:text-slate-500">
                                    {{ inventoryHealthLabel }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-5 h-1.5 overflow-hidden rounded-full bg-slate-200/80 dark:bg-white/[0.08]"
                        >
                            <div
                                class="h-full rounded-full bg-slate-900 transition-all duration-700 dark:bg-white"
                                :style="{ width: `${inventoryHealthPercentage}%` }"
                            ></div>
                        </div>

                        <div
                            class="mt-5 flex flex-wrap gap-x-5 gap-y-2 text-xs text-slate-500 dark:text-slate-400"
                        >
                            <span>{{ inventoryStats.lowStock || 0 }} bajo stock</span>
                            <span>{{ inventoryStats.outOfStock || 0 }} agotados</span>
                            <span>{{ inventoryStats.finiteProducts || 0 }} finitos</span>
                        </div>
                    </div>

                    <div
                        v-if="hasInventoryValue"
                        class="mt-7 border-t border-black/[0.055] pt-5 dark:border-white/[0.06]"
                    >
                        <p
                            class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500"
                        >
                            Valor de inventario
                        </p>

                        <div class="mt-3 flex flex-wrap gap-x-6 gap-y-3">
                            <div
                                v-for="item in inventoryStats.valueByCurrency"
                                :key="`inventory-${item.currency}`"
                            >
                                <p
                                    class="text-lg font-semibold tracking-tight text-slate-900 dark:text-white"
                                >
                                    {{ formatCurrency(item.total, item.currency) }}
                                </p>

                                <p class="mt-0.5 text-[10px] text-slate-400 dark:text-slate-500">
                                    {{ item.currency }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- EXPIRATIONS + TOP PRODUCTS -->
            <section
                class="mb-9 grid gap-10 rounded-[28px] border border-white/55 bg-white/25 p-6 backdrop-blur-md backdrop-saturate-150 shadow-[0_10px_40px_rgba(15,23,42,0.04),inset_0_1px_2px_rgba(255,255,255,0.7),inset_0_-2px_5px_rgba(0,0,0,0.04)] dark:border-white/[0.07] dark:bg-[#101014]/55 dark:shadow-[0_10px_40px_rgba(0,0,0,0.2),inset_0_1px_2px_rgba(255,255,255,0.1),inset_0_-2px_6px_rgba(0,0,0,0.4)] sm:p-7 lg:grid-cols-[1.08fr_0.92fr]"
            >
                <!-- EXPIRATIONS -->
                <div class="min-w-0">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p
                                class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500"
                            >
                                Cronología
                            </p>

                            <h2
                                class="mt-2 text-2xl font-semibold tracking-[-0.035em] text-slate-950 dark:text-white"
                            >
                                Vencimientos
                            </h2>

                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                Suscripciones dentro de los próximos 30 días.
                            </p>
                        </div>

                        <Link
                            href="/subscriptions"
                            class="text-xs font-semibold text-slate-400 transition hover:text-slate-900 dark:text-slate-500 dark:hover:text-white"
                        >
                            Ver todo →
                        </Link>
                    </div>

                    <div
                        v-if="subscriptionAlerts.length"
                        class="mt-7 space-y-1"
                    >
                        <div
                            v-for="alert in subscriptionAlerts"
                            :key="alert.id"
                            class="relative flex gap-4 rounded-2xl px-3 py-3 transition hover:bg-black/[0.02] dark:hover:bg-white/[0.03]"
                        >
                            <div
                                class="relative z-[2] mt-1 flex h-3 w-3 shrink-0 items-center justify-center rounded-full bg-white dark:bg-[#050507]"
                            >
                                <span
                                    class="h-2 w-2 rounded-full"
                                    :class="alertTone(alert.type).dot"
                                ></span>
                            </div>

                            <div class="min-w-0 flex-1 pb-1">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p
                                            class="truncate text-sm font-semibold text-slate-900 dark:text-white"
                                        >
                                            {{ alert.title }}
                                        </p>

                                        <p
                                            class="mt-1 truncate text-xs text-slate-400 dark:text-slate-500"
                                        >
                                            {{ alert.subtitle }}
                                        </p>
                                    </div>

                                    <span
                                        class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-semibold"
                                        :class="alertTone(alert.type).badge"
                                    >
                                        {{ alert.label }}
                                    </span>
                                </div>

                                <div
                                    class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-slate-400 dark:text-slate-500"
                                >
                                    <span>{{ alert.date }}</span>

                                    <span v-if="alert.currency">
                                        {{ formatCurrency(alert.total, alert.currency) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="mt-7 border-y border-dashed border-slate-200/80 py-8 text-center dark:border-white/[0.07]"
                    >
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">
                            No hay vencimientos próximos
                        </p>

                        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                            Las suscripciones dentro de los próximos 30 días aparecerán aquí.
                        </p>
                    </div>
                </div>

                <!-- TOP PRODUCTS -->
                <div
                    class="min-w-0 border-t border-black/[0.055] pt-9 dark:border-white/[0.06] lg:border-l lg:border-t-0 lg:pl-10 lg:pt-0"
                >
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p
                                class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500"
                            >
                                Rendimiento
                            </p>

                            <h2
                                class="mt-2 text-2xl font-semibold tracking-[-0.035em] text-slate-950 dark:text-white"
                            >
                                Productos utilizados
                            </h2>

                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                Basado en suscripciones registradas.
                            </p>
                        </div>

                        <Link
                            href="/products"
                            class="text-xs font-semibold text-slate-400 transition hover:text-slate-900 dark:text-slate-500 dark:hover:text-white"
                        >
                            Ver todo →
                        </Link>
                    </div>

                    <div v-if="topProducts.length" class="mt-7 space-y-5">
                        <div
                            v-for="(product, index) in topProducts"
                            :key="product.id"
                            class="group"
                        >
                            <div class="flex items-center gap-3">
                                <span
                                    class="w-6 shrink-0 text-[10px] font-semibold text-slate-400 dark:text-slate-600"
                                >
                                    {{ String(index + 1).padStart(2, '0') }}
                                </span>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-3">
                                        <p
                                            class="truncate text-sm font-medium text-slate-900 dark:text-white"
                                        >
                                            {{ product.name }}
                                        </p>

                                        <span
                                            class="shrink-0 text-xs font-semibold text-slate-500 dark:text-slate-400"
                                        >
                                            {{ product.subscriptions }}
                                        </span>
                                    </div>

                                    <div
                                        class="mt-2 h-1 overflow-hidden rounded-full bg-slate-200/80 dark:bg-white/[0.07]"
                                    >
                                        <div
                                            class="h-full rounded-full bg-slate-900 transition-all duration-500 dark:bg-white"
                                            :style="{
                                                width: `${Math.max(
                                                    8,
                                                    Math.min(
                                                        100,
                                                        (Number(product.subscriptions || 0) /
                                                            Math.max(
                                                                1,
                                                                Number(topProducts[0]?.subscriptions || 0)
                                                            )) * 100
                                                    )
                                                )}%`,
                                            }"
                                        ></div>
                                    </div>
                                </div>

                                <div
                                    v-if="topProductValue(product)"
                                    class="hidden shrink-0 text-right sm:block"
                                >
                                    <p
                                        class="text-[10px] uppercase tracking-[0.1em] text-slate-400 dark:text-slate-500"
                                    >
                                        valor
                                    </p>

                                    <p
                                        class="mt-0.5 text-xs font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        {{
                                            formatCurrency(
                                                topProductValue(product).total,
                                                topProductValue(product).currency
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="mt-7 border-y border-dashed border-slate-200/80 py-8 text-center dark:border-white/[0.07]"
                    >
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">
                            Todavía no hay actividad
                        </p>

                        <p class="mt-1 text-xs leading-5 text-slate-400 dark:text-slate-500">
                            Los productos utilizados aparecerán aquí cuando existan suscripciones.
                        </p>
                    </div>
                </div>
            </section>

            <!-- LOW STOCK -->
            <section
                v-if="lowStockProducts.length"
                class="rounded-[28px] border border-white/55 bg-white/25 p-6 backdrop-blur-md backdrop-saturate-150 shadow-[0_10px_40px_rgba(15,23,42,0.04),inset_0_1px_2px_rgba(255,255,255,0.7),inset_0_-2px_5px_rgba(0,0,0,0.04)] dark:border-white/[0.07] dark:bg-[#101014]/55 dark:shadow-[0_10px_40px_rgba(0,0,0,0.2),inset_0_1px_2px_rgba(255,255,255,0.1),inset_0_-2px_6px_rgba(0,0,0,0.4)] sm:p-7"
            >
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p
                            class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500"
                        >
                            Inventario
                        </p>

                        <h2
                            class="mt-2 text-2xl font-semibold tracking-[-0.035em] text-slate-950 dark:text-white"
                        >
                            Productos que requieren revisión
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{
                                lowStockProducts.length > 3
                                    ? `Mostrando 3 de ${lowStockProducts.length} productos.`
                                    : 'Productos finitos con stock bajo.'
                            }}
                        </p>
                    </div>

                    <Link
                        href="/products"
                        class="text-xs font-semibold text-slate-400 transition hover:text-slate-900 dark:text-slate-500 dark:hover:text-white"
                    >
                        Ver todos →
                    </Link>
                </div>

                <div
                    class="mt-7 overflow-hidden rounded-2xl border border-black/[0.055] dark:border-white/[0.06]"
                >
                    <div
                        class="grid grid-cols-[minmax(0,1fr)_70px_120px] gap-4 border-b border-black/[0.055] py-3 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400 dark:border-white/[0.06] dark:text-slate-500 sm:px-1"
                    >
                        <span>Producto</span>
                        <span>Stock</span>
                        <span class="text-right">Precio</span>
                    </div>

                    <div
                        v-for="item in lowStockProducts.slice(0, 3)"
                        :key="item.id"
                        class="grid grid-cols-[minmax(0,1fr)_70px_120px] gap-4 border-b border-black/[0.04] py-4 last:border-b-0 dark:border-white/[0.05] sm:px-1"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-900 dark:text-white">
                                {{ item.name }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                Stock físico
                            </p>
                        </div>

                        <div class="flex items-center">
                            <span
                                class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="
                                    Number(item.stock) <= 2
                                        ? 'bg-rose-500/10 text-rose-700 dark:bg-rose-400/10 dark:text-rose-300'
                                        : 'bg-amber-500/10 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300'
                                "
                            >
                                {{ item.stock }}
                            </span>
                        </div>

                        <div class="flex items-center justify-end">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                {{ formatCurrency(item.price, item.currency) }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>