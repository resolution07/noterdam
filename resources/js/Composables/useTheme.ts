import { ref, watch, onMounted } from 'vue';

type Theme = 'dark' | 'light' | 'system';

const STORAGE_KEY = 'noterdam-theme';

function resolve(theme: Theme): 'dark' | 'light' {
    if (theme !== 'system') return theme;
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

export function useTheme() {
    const theme = ref<Theme>('dark');

    const apply = (value: Theme) => {
        const resolved = resolve(value);
        const html = document.documentElement;
        if (resolved === 'dark') {
            html.classList.add('dark');
            html.classList.remove('light');
        } else {
            html.classList.add('light');
            html.classList.remove('dark');
        }
        html.style.colorScheme = resolved;
    };

    const set = (value: Theme) => {
        theme.value = value;
        localStorage.setItem(STORAGE_KEY, value);
        apply(value);
    };

    onMounted(() => {
        const stored = localStorage.getItem(STORAGE_KEY) as Theme | null;
        theme.value = stored || 'dark';
        apply(theme.value);

        window
            .matchMedia('(prefers-color-scheme: dark)')
            .addEventListener('change', () => apply(theme.value));
    });

    watch(theme, apply);

    return { theme, set };
}
