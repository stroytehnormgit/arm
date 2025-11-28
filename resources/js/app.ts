import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme, reapplyBackground } from './composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'АРМ';

if (typeof window !== 'undefined') {
    router.on('navigate', () => {
        setTimeout(() => {
            reapplyBackground();
        }, 150);
    });
    
    router.on('success', () => {
        setTimeout(() => {
            reapplyBackground();
        }, 200);
    });
    
    router.on('finish', () => {
        setTimeout(() => {
            reapplyBackground();
        }, 250);
    });
}

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        app.use(plugin);
        
        if (typeof window !== 'undefined' && typeof MutationObserver !== 'undefined') {
            let timeoutId: ReturnType<typeof setTimeout> | null = null;
            
            const observer = new MutationObserver(() => {
                if (timeoutId) {
                    clearTimeout(timeoutId);
                }
                timeoutId = setTimeout(() => {
                    reapplyBackground();
                }, 200);
            });
            
            setTimeout(() => {
                if (document.body) {
                    observer.observe(document.body, {
                        childList: true,
                        subtree: true,
                    });
                }
            }, 500);
        }
        
        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

initializeTheme();

if (typeof window !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            reapplyBackground();
        }, 300);
    });
    
    window.addEventListener('popstate', () => {
        setTimeout(() => {
            reapplyBackground();
        }, 200);
    });
}
