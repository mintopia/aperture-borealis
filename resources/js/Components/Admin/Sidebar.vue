<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const navItems = [
    { label: 'Dashboard', href: '/admin', icon: '⌂' },
    { label: 'Providers', href: '/admin/providers', icon: '⚡' },
    { label: 'Clients', href: '/admin/clients', icon: '🔑' },
];

const settingsItems = [
    { label: 'Theme', href: '/admin/settings/theme' },
    { label: 'General', href: '/admin/settings/general' },
];

function isActive(href) {
    const currentPath = page.url;
    if (href === '/admin') return currentPath === '/admin';
    return currentPath.startsWith(href);
}
</script>

<template>
    <aside data-testid="admin-sidebar" class="flex w-[220px] flex-shrink-0 flex-col border-r border-[var(--color-border)] bg-[var(--color-surface)]">
        <div class="flex h-12 items-center px-5 border-b border-[var(--color-border)]">
            <span class="font-heading text-[15px] font-bold text-[var(--color-text)]">Borealis</span>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1">
            <Link
                v-for="item in navItems"
                :key="item.href"
                :href="item.href"
                :class="[
                    'flex items-center gap-2.5 rounded-md px-3 py-[7px] text-[13px] font-medium transition-colors',
                    isActive(item.href)
                        ? 'bg-[var(--color-accent-dim)] text-[var(--color-primary)]'
                        : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-surface-hover)] hover:text-[var(--color-text)]',
                ]"
            >
                <span class="text-sm">{{ item.icon }}</span>
                {{ item.label }}
            </Link>

            <div class="pt-4">
                <p class="px-3 pb-2 text-[10px] font-semibold tracking-[0.08em] text-[var(--color-text-muted)] uppercase">Settings</p>
                <Link
                    v-for="item in settingsItems"
                    :key="item.href"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-2.5 rounded-md px-3 py-[7px] text-[13px] font-medium transition-colors',
                        isActive(item.href)
                            ? 'bg-[var(--color-accent-dim)] text-[var(--color-primary)]'
                            : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-surface-hover)] hover:text-[var(--color-text)]',
                    ]"
                >
                    {{ item.label }}
                </Link>
            </div>
        </nav>

        <div class="border-t border-[var(--color-border)] px-3 py-3">
            <Link
                href="/admin/logout"
                method="post"
                as="button"
                class="flex w-full items-center gap-2 rounded-md px-3 py-[7px] text-[13px] font-medium text-[var(--color-text-secondary)] transition-colors hover:bg-[var(--color-surface-hover)] hover:text-[var(--color-text)]"
            >
                Logout
            </Link>
        </div>
    </aside>
</template>
