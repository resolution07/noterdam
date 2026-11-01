import '../css/app.css';

import { createApp, h, type DefineComponent } from 'vue';
import { createInertiaApp, Link, Head } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue, route } from '../../vendor/tightenco/ziggy/dist/index.esm.js';
import { Toaster } from 'vue-sonner';

(window as unknown as { route: typeof route }).route = route;

const appName = import.meta.env.VITE_APP_NAME || 'Noterdam';

createInertiaApp({
    title: (title) => `${title} · ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .component('InertiaLink', Link)
            .component('InertiaHead', Head)
            .component('Toaster', Toaster)
            .mount(el);
    },
    progress: {
        color: '#34D399',
    },
});
