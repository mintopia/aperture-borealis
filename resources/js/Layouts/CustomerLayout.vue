<script setup>
import { usePage, Head } from '@inertiajs/vue3';
import { useTheme } from '@/Composables/useTheme';
import AppLogo from '@/Components/AppLogo.vue';

const page = usePage();
const { effectiveMode } = useTheme();

const theme = page.props.theme || {};
const legal = page.props.legal || {};
</script>

<template>
    <div data-testid="customer-layout" class="flex min-h-screen flex-col items-center justify-center bg-[var(--color-bg)] px-4 py-8">
        <Head :title="$page.props.title ?? ''" />

        <div class="w-full max-w-[420px]">
            <div class="mb-8 flex justify-center">
                <AppLogo class="text-xl" />
            </div>

            <slot />
        </div>

        <footer v-if="legal.terms_url || legal.privacy_url" class="mt-auto pt-8">
            <div class="flex gap-4 text-[11px] text-[var(--color-text-muted)]">
                <a v-if="legal.terms_url" :href="legal.terms_url" target="_blank" class="hover:text-[var(--color-text-secondary)]">
                    Terms of Service
                </a>
                <a v-if="legal.privacy_url" :href="legal.privacy_url" target="_blank" class="hover:text-[var(--color-text-secondary)]">
                    Privacy Policy
                </a>
            </div>
        </footer>

        <component v-if="theme.custom_css" :is="'style'" v-text="theme.custom_css" />
    </div>
</template>
