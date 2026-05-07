import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { applyAccentHue } from './useAccentHue';

const VALID_MODES = ['light', 'dark', 'system'];

function resolveMode(mode) {
    if (mode === 'system') {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    return mode;
}

export function useTheme() {
    const page = usePage();
    const sharedTheme = page.props.theme || {};

    const colorMode = ref(sharedTheme.color_mode || localStorage.getItem('themeMode') || 'dark');
    const effectiveMode = ref(resolveMode(colorMode.value));
    const accentHue = ref(sharedTheme.accent_hue ?? 55);

    function applyTheme() {
        const el = document.documentElement;
        effectiveMode.value = resolveMode(colorMode.value);
        el.setAttribute('data-theme', 'dispatch');
        el.setAttribute('data-mode', effectiveMode.value);
        applyAccentHue(accentHue.value, effectiveMode.value);
    }

    function setMode(newMode) {
        if (VALID_MODES.includes(newMode)) {
            colorMode.value = newMode;
            localStorage.setItem('themeMode', newMode);
            applyTheme();
        }
    }

    function toggleMode() {
        setMode(effectiveMode.value === 'dark' ? 'light' : 'dark');
    }

    function setAccentHue(hue) {
        accentHue.value = hue;
        applyTheme();
    }

    watch([colorMode, accentHue], () => applyTheme(), { immediate: true });

    if (colorMode.value === 'system') {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => applyTheme());
    }

    return {
        colorMode,
        effectiveMode,
        accentHue,
        setMode,
        toggleMode,
        setAccentHue,
    };
}
