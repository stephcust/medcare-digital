import './bootstrap';
import '../css/app.css';

import "bootstrap-icons/font/bootstrap-icons.css";
import "primeicons/primeicons.css";

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';
import Tooltip from 'primevue/tooltip';
import Ripple from 'primevue/ripple';
import BadgeDirective from 'primevue/badgedirective';
import locale from './locale-pt';
import { PresetBluePurpleSilver } from '@/Themes/PresetBluePurpleSilver';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        return app
            .use(plugin)
            .use(ZiggyVue)
            .use(PrimeVue, {
                locale,
                ripple: true,
                theme: {
                    preset: PresetBluePurpleSilver,
                    options: {
                        darkModeSelector: '.my-app-dark',
                        cssLayer: {
                            name: 'primevue',
                            order: 'tailwind-base, primevue, tailwind-utilities'
                        },

                    },
                },
                pt: {
                    button: (o) => {
                        const severity = o?.props?.severity;
                        if (severity === 'tertiary') {
                            const root = `bg-tertiary-400 text-white border-tertiary-400 hover:bg-tertiary-500 active:bg-tertiary-600 hover:border-tertiary-500 active:border-tertiary-600 outline-tertiary-500`
                            return { root };
                        }
                    },
                }
            })
            .use(ToastService)
            .use(ConfirmationService)
            .directive('badge', BadgeDirective)
            .directive('tooltip', Tooltip)
            .directive('ripple', Ripple)
            .mount(el);
    },
    progress: { color: '#34d399', },
});
