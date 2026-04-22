<script setup>
import { useForm } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import SectionHeader from '@/Components/UI/SectionHeader.vue';
import FormField from '@/Components/UI/FormField.vue';
import { ACCENT_PRESETS, applyAccentHue } from '@/Composables/useAccentHue';

defineOptions({ layout: SettingsLayout });

const props = defineProps({
    settings: { type: Object, required: true },
});

const form = useForm({
    accent_hue: String(props.settings.accent_hue),
    color_mode: props.settings.color_mode,
    site_title: props.settings.site_title ?? '',
    custom_css: props.settings.custom_css ?? '',
});

const colorModes = [
    { value: 'light', label: 'Light' },
    { value: 'dark', label: 'Dark' },
    { value: 'system', label: 'System' },
];

function selectHue(hue) {
    form.accent_hue = String(hue);
    const mode = document.documentElement.getAttribute('data-mode') || 'dark';
    applyAccentHue(hue, mode);
}

function submit() {
    form.put('/admin/settings/theme');
}
</script>

<template>
    <div>
        <SectionHeader title="Theme" />

        <div class="max-w-2xl rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Accent Hue -->
                <FormField label="Accent Colour" name="accent_hue" required :error="form.errors.accent_hue">
                    <div class="flex flex-wrap gap-3">
                        <button
                            v-for="preset in ACCENT_PRESETS"
                            :key="preset.hue"
                            type="button"
                            @click="selectHue(preset.hue)"
                            :title="preset.name"
                            :class="[
                                'group relative h-9 w-9 rounded-full transition-all',
                                String(preset.hue) === form.accent_hue
                                    ? 'ring-2 ring-[var(--color-primary)] ring-offset-2 ring-offset-[var(--color-surface)] scale-110'
                                    : 'hover:scale-105',
                            ]"
                            :style="{
                                backgroundColor: `oklch(${preset.l}% ${preset.c} ${preset.hue})`,
                            }"
                        >
                            <span class="sr-only">{{ preset.name }}</span>
                            <svg
                                v-if="String(preset.hue) === form.accent_hue"
                                class="absolute inset-0 m-auto h-4 w-4 text-white drop-shadow"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="3"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>
                    <p class="mt-1.5 text-xs text-[var(--color-text-muted)]">
                        Selected: {{ ACCENT_PRESETS.find(p => String(p.hue) === form.accent_hue)?.name ?? `Hue ${form.accent_hue}` }}
                    </p>
                </FormField>

                <!-- Color Mode -->
                <FormField label="Default Colour Mode" name="color_mode" required :error="form.errors.color_mode">
                    <div class="inline-flex rounded-lg border border-[var(--color-border)] overflow-hidden">
                        <button
                            v-for="mode in colorModes"
                            :key="mode.value"
                            type="button"
                            @click="form.color_mode = mode.value"
                            :class="[
                                'px-4 py-[7px] text-[13px] font-medium transition-colors',
                                form.color_mode === mode.value
                                    ? 'bg-[var(--color-primary)] text-[var(--color-accent-text)]'
                                    : 'bg-[var(--color-input-bg)] text-[var(--color-text-secondary)] hover:bg-[var(--color-surface-hover)]',
                            ]"
                        >
                            {{ mode.label }}
                        </button>
                    </div>
                </FormField>

                <!-- Site Title -->
                <FormField label="Site Title" name="site_title" :error="form.errors.site_title">
                    <input
                        id="site_title"
                        v-model="form.site_title"
                        type="text"
                        placeholder="My LAN Party"
                        class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-input-bg)] px-3 py-2 text-sm text-[var(--color-text)] placeholder-[var(--color-text-muted)] outline-none transition-colors focus:border-[var(--color-primary)] focus:ring-2 focus:ring-[var(--color-primary)]/20"
                    />
                </FormField>

                <!-- Custom CSS -->
                <FormField label="Custom CSS" name="custom_css" :error="form.errors.custom_css">
                    <textarea
                        id="custom_css"
                        v-model="form.custom_css"
                        rows="6"
                        placeholder="/* Add custom styles here */"
                        class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-input-bg)] px-3 py-2 font-mono text-xs text-[var(--color-text)] placeholder-[var(--color-text-muted)] outline-none transition-colors focus:border-[var(--color-primary)] focus:ring-2 focus:ring-[var(--color-primary)]/20"
                    />
                    <p class="text-xs text-[var(--color-text-muted)]">
                        Injected into every page. Use with caution.
                    </p>
                </FormField>

                <!-- Submit -->
                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-[var(--color-primary)] px-4 py-[7px] text-[13px] font-semibold text-[var(--color-accent-text)] transition-colors hover:bg-[var(--color-primary-hover)] disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving…' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
