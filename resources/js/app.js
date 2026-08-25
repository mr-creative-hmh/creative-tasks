import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { currentLocale, setLocale, t } from './i18n';

const appName = import.meta.env.VITE_APP_NAME || 'Creative Tasks';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        // Initialize locale and direction from server session / localStorage
        const initialLocale = props.initialPage.props.locale || localStorage.getItem('app_locale') || 'ar';
        setLocale(initialLocale);

        const vueApp = createApp({ render: () => h(App, props) });
        vueApp.use(plugin);
        
        // Global translation helper
        vueApp.config.globalProperties.$t = t;
        vueApp.config.globalProperties.$locale = currentLocale;
        
        vueApp.mount(el);
    },
    progress: {
        color: '#0d9488',
        showSpinner: true,
    },
});
