<script setup>
import {
    computed,
    nextTick,
    onMounted,
    onUnmounted,
    ref,
} from 'vue';

import { router } from '@inertiajs/vue3';
import liquidGL from 'liquid-gl';

const props = defineProps({
    align: {
        type: String,
        default: 'right',
    },

    width: {
        type: String,
        default: '48',
    },

    contentClasses: {
        type: String,
        default: '',
    },
});

const open = ref(false);
const liquidTarget = ref(null);

let glassEffect = null;
let initialized = false;
let removeInertiaListener = null;

const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

const widthClass = computed(() => {
    return {
        '48': 'w-48',
        '56': 'w-56',
    }[props.width.toString()] || 'w-48';
});

const alignmentClasses = computed(() => {
    if (props.align === 'left') {
        return 'ltr:origin-top-left rtl:origin-top-right start-0';
    }

    if (props.align === 'right') {
        return 'ltr:origin-top-right rtl:origin-top-left end-0';
    }

    return 'origin-top';
});

const initLiquidGlass = async () => {
    if (initialized || !liquidTarget.value) {
        return;
    }

    await nextTick();

    if (!liquidTarget.value) {
        return;
    }

    glassEffect = liquidGL({
        target: '.dominiomatic-liquid-glass',
        snapshot: 'body',

        resolution: 1.0,

        /*
         * Valor elevado únicamente para comprobar
         * que refraction está siendo aplicado.
         */
        refraction: 0.018,

        bevelDepth: 0.045,
        bevelWidth: 0.045,

        aberration: 0.015,
        frost: 0.2,

        shadow: true,
        specular: false,

        reveal: 'none',

        tilt: false,
        magnify: 1.03,

        on: {
            init(instance) {
                glassEffect = instance;
                initialized = true;

                /*
                 * Exponemos temporalmente la instancia
                 * para poder comprobarla desde DevTools.
                 */
                window.__liquidGlass = instance;

                console.log(
                    '[LiquidGL] instancia inicializada'
                );

                console.log(
                    '[LiquidGL] refraction:',
                    instance.options?.refraction
                );

                console.log(
                    '[LiquidGL] opciones:',
                    instance.options
                );
            },
        },
    });
};

const handleInertiaNavigate = () => {
    nextTick(() => {
        requestAnimationFrame(() => {
            window.dispatchEvent(new Event('resize'));
        });
    });
};

const handleVisibilityChange = () => {
    if (document.visibilityState !== 'visible') {
        return;
    }

    window.dispatchEvent(new Event('resize'));
};

onMounted(() => {
    document.addEventListener(
        'keydown',
        closeOnEscape
    );

    document.addEventListener(
        'visibilitychange',
        handleVisibilityChange
    );

    removeInertiaListener = router.on(
        'navigate',
        handleInertiaNavigate
    );

    nextTick(() => {
        initLiquidGlass();
    });
});

onUnmounted(() => {
    document.removeEventListener(
        'keydown',
        closeOnEscape
    );

    document.removeEventListener(
        'visibilitychange',
        handleVisibilityChange
    );

    if (removeInertiaListener) {
        removeInertiaListener();
        removeInertiaListener = null;
    }

    glassEffect = null;

    if (window.__liquidGlass) {
        delete window.__liquidGlass;
    }
});
</script>

<template>
    <div class="relative">
        <div @click="open = !open">
            <slot name="trigger" />
        </div>

        <div
            v-show="open"
            class="fixed inset-0 z-40"
            @click="open = false"
        ></div>

        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 scale-95 -translate-y-1"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 -translate-y-1"
        >
            <div
                v-show="open"
                ref="liquidTarget"
                class="dominiomatic-liquid-glass
                       absolute
                       z-50
                       mt-2
                       rounded-[28px]
                       p-1.5"
                :class="[widthClass, alignmentClasses]"
                style="display: none; clip-path: inset(0 round 26px);"
                @click="open = false"
            >
                <!-- Capa de tinte visual -->
                <div
                    class="liquid-glass-tint
                           pointer-events-none
                           absolute
                           inset-0
                           z-0
                           rounded-[26px]
                           border
                           border-white/60
                           bg-white/50
                           backdrop-blur-md
                           shadow-[inset_0_1px_2px_rgba(255,255,255,0.85),inset_0_-2px_5px_rgba(0,0,0,0.12)]
                           dark:border-white/20
                           dark:bg-[#101013]/80
                           dark:backdrop-blur-xl
                           dark:shadow-[inset_0_1px_2px_rgba(255,255,255,0.25),inset_0_-2px_6px_rgba(0,0,0,0.70)]"
                    aria-hidden="true"
                ></div>

                <!-- Contenido UI -->
                <div
                    class="relative z-[3] pointer-events-auto rounded-[22px]"
                    :class="contentClasses"
                >
                    <slot name="content" />
                </div>
            </div>
        </Transition>
    </div>
</template>