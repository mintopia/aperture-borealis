<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const items = [
    { label: 'Theme', href: '/admin/settings/theme' },
    { label: 'General', href: '/admin/settings/general' },
];

function isActive(href) {
    return page.url === href;
}
</script>

<template>
    <nav data-testid="settings-nav" class="w-[180px] flex-shrink-0 space-y-1">
        <Link
            v-for="item in items"
            :key="item.href"
            :href="item.href"
            :class="[
                'block rounded-md px-3 py-[7px] text-[13px] font-medium transition-colors',
                isActive(item.href)
                    ? 'bg-[var(--color-accent-dim)] text-[var(--color-primary)]'
                    : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-surface-hover)] hover:text-[var(--color-text)]',
            ]"
        >
            {{ item.label }}
        </Link>
    </nav>
</template>
