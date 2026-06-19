import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
// Configuración para mantener el tema oscuro o claro según la preferencia del usuario
const savedTheme =
    localStorage.getItem(
        'theme'
    ) || 'system';

const html =
    document.documentElement;

if (savedTheme === 'dark') {

    html.classList.add('dark');

} else if (
    savedTheme === 'light'
) {

    html.classList.remove('dark');

} else {

    if (
        window.matchMedia(
            '(prefers-color-scheme: dark)'
        ).matches
    ) {
        html.classList.add('dark');
    }
}
createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

