<script setup>
import { usePage, Head } from '@inertiajs/vue3';
import { useTheme } from '@/Composables/useTheme';
import Sidebar from '@/Components/Admin/Sidebar.vue';

const page = usePage();
useTheme();
</script>

<template>
    <div data-testid="admin-layout" class="flex min-h-screen bg-[var(--color-bg)]">
        <Head :title="$page.props.title ?? ''" />

        <Sidebar />

        <div class="flex min-w-0 flex-1 flex-col">
            <header
                data-testid="admin-header"
                class="sticky top-0 z-50 flex h-12 items-center justify-between border-b border-[var(--color-border)] bg-[var(--color-surface)] px-6"
            >
                <div />
                <div class="flex items-center gap-4">
                    <div v-if="page.props.auth.user" class="text-[13px] text-[var(--color-text-secondary)]">
                        {{ page.props.auth.user.nickname }}
                    </div>
                </div>
            </header>

            <div v-if="page.props.flash?.success || page.props.flash?.error" class="px-10 pt-4">
                <div v-if="page.props.flash.success" class="rounded-md border border-[var(--color-success)]/20 bg-[var(--color-success)]/10 px-4 py-3 text-[13px] text-[var(--color-success)]">
                    {{ page.props.flash.success }}
                </div>
                <div v-if="page.props.flash.error" class="rounded-md border border-[var(--color-danger)]/20 bg-[var(--color-danger)]/10 px-4 py-3 text-[13px] text-[var(--color-danger)]">
                    {{ page.props.flash.error }}
                </div>
            </div>

            <main class="mx-auto w-full max-w-[1400px] flex-1 px-10 pt-8 pb-16">
                <slot />
            </main>
        </div>
    </div>
</template>
