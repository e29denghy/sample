import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import { createApp, h } from 'vue';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const element = document.getElementById('app');

if (element) {
    const initialPage = element.dataset.page ? JSON.parse(element.dataset.page) : undefined;

    createInertiaApp({
        page: initialPage,
        resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
        setup({ el, App, props, plugin }) {
            createApp({ render: () => h(App, props) })
                .use(plugin)
                .component('Head', Head)
                .component('Link', Link)
                .mount(el);
        },
    });
}
