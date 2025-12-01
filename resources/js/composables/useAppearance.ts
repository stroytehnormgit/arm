import { onMounted, ref } from 'vue';

type Appearance = 'light' | 'dark' | 'green' | 'system';

export function updateTheme(value: Appearance) {
    if (typeof window === 'undefined') {
        return;
    }

    document.documentElement.classList.remove('dark', 'green-theme', 'gradient-theme');

    if (value === 'system') {
        document.documentElement.classList.add('gradient-theme');
        
        const customBackground = localStorage.getItem('custom-background');
        const presetBackground = localStorage.getItem('preset-background');
        
        if (customBackground) {
            applyCustomBackground(customBackground);
        } else if (presetBackground) {
            applyCustomBackground(presetBackground);
        }
    } else if (value === 'green') {
        document.documentElement.classList.add('green-theme');
    } else {
        document.documentElement.classList.toggle('dark', value === 'dark');
    }
}

let currentBackground: string | null = null;

export function applyCustomBackground(backgroundUrl: string) {
    if (typeof document === 'undefined') return;
    
    currentBackground = backgroundUrl;
    
    const html = document.documentElement;
    const body = document.body;
    const root = document.documentElement;
    
    html.setAttribute('data-custom-background', 'true');
    
    if (backgroundUrl.startsWith('#')) {
        html.style.setProperty('background', backgroundUrl, 'important');
        html.style.setProperty('background-image', 'none', 'important');
        body.style.setProperty('background', backgroundUrl, 'important');
        body.style.setProperty('background-image', 'none', 'important');
        root.style.setProperty('--custom-background', backgroundUrl);
    } else {
        const bgValue = `url(${backgroundUrl})`;
        html.style.setProperty('background-image', bgValue, 'important');
        html.style.setProperty('background-size', 'cover', 'important');
        html.style.setProperty('background-position', 'center', 'important');
        html.style.setProperty('background-repeat', 'no-repeat', 'important');
        html.style.setProperty('background-attachment', 'fixed', 'important');
        body.style.setProperty('background-image', bgValue, 'important');
        body.style.setProperty('background-size', 'cover', 'important');
        body.style.setProperty('background-position', 'center', 'important');
        body.style.setProperty('background-repeat', 'no-repeat', 'important');
        body.style.setProperty('background-attachment', 'fixed', 'important');
        root.style.setProperty('--custom-background', bgValue);
    }
    
    const applyToContentArea = () => {
        const bgElements = document.querySelectorAll('.bg-background, [data-slot="sidebar-inset"], .app-content, main[data-slot="sidebar-inset"]');
        bgElements.forEach((el) => {
            const element = el as HTMLElement;
            
            if (backgroundUrl.startsWith('#')) {
                element.style.setProperty('background', backgroundUrl, 'important');
                element.style.setProperty('background-image', 'none', 'important');
            } else {
                const bgValue = `url(${backgroundUrl})`;
                element.style.setProperty('background-image', bgValue, 'important');
                element.style.setProperty('background-size', 'cover', 'important');
                element.style.setProperty('background-position', 'center', 'important');
                element.style.setProperty('background-repeat', 'no-repeat', 'important');
                element.style.setProperty('background-attachment', 'fixed', 'important');
                element.style.setProperty('background-color', 'transparent', 'important');
                element.style.setProperty('position', 'relative', 'important');
            }
        });
        
        const sidebarElements = document.querySelectorAll('[data-sidebar="sidebar"]');
        sidebarElements.forEach((el) => {
            const element = el as HTMLElement;
            element.style.setProperty('background-color', 'rgba(255, 253, 253, 0.2)', 'important');
        });
    };
    
    applyToContentArea();
}

export function reapplyBackground() {
    if (currentBackground) {
        applyCustomBackground(currentBackground);
    } else {
        const presetBackground = localStorage.getItem('preset-background');
        
        if (presetBackground) {
            currentBackground = presetBackground;
            applyCustomBackground(presetBackground);
        }
    }
}

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;

    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const mediaQuery = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.matchMedia('(prefers-color-scheme: dark)');
};

const getStoredAppearance = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return localStorage.getItem('appearance') as Appearance | null;
};

const handleSystemThemeChange = () => {
    const currentAppearance = getStoredAppearance();

    updateTheme(currentAppearance || 'system');
};

export function initializeTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    const savedAppearance = getStoredAppearance();
    updateTheme(savedAppearance || 'system');

    if (!savedAppearance || savedAppearance === 'system') {
        const presetBackground = localStorage.getItem('preset-background');
        
        if (presetBackground) {
            currentBackground = presetBackground;
            document.documentElement.setAttribute('data-custom-background', 'true');
            setTimeout(() => {
                applyCustomBackground(presetBackground);
            }, 50);
        }
    }

    mediaQuery()?.addEventListener('change', handleSystemThemeChange);
    
    if (typeof window !== 'undefined') {
        setInterval(() => {
            if (currentBackground || localStorage.getItem('preset-background')) {
                reapplyBackground();
            }
        }, 1000);
    }
}

const appearance = ref<Appearance>('system');

export function useAppearance() {
    onMounted(() => {
        const savedAppearance = localStorage.getItem(
            'appearance',
        ) as Appearance | null;

        if (savedAppearance) {
            appearance.value = savedAppearance;
        }
    });

    function updateAppearance(value: Appearance) {
        appearance.value = value;

        localStorage.setItem('appearance', value);

        setCookie('appearance', value);

        updateTheme(value);
    }

    return {
        appearance,
        updateAppearance,
    };
}
