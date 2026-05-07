<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import AppLogo from '@/Components/AppLogo.vue';

const page = usePage();
const currentUrl = computed(() => page.url);
const isDesktop = ref(true);
const drawerOpen = ref(false);

const navItems = [
    { label: 'Dashboard', href: '/admin' },
    { label: 'Providers', href: '/admin/providers' },
    { label: 'Clients', href: '/admin/clients' },
];

const settingsItems = [
    { label: 'Theme', href: '/admin/settings/theme' },
    { label: 'General', href: '/admin/settings/general' },
];

function isActive(href) {
    if (href === '/admin') return currentUrl.value === '/admin';
    return currentUrl.value.startsWith(href);
}

function itemClass(href) {
    return isActive(href)
        ? 'bg-[var(--color-accent-dim)] text-[var(--color-primary)] font-semibold'
        : 'text-[var(--color-text-secondary)] hover:text-[var(--color-text)] hover:bg-[var(--color-surface-hover)]';
}

const mql = window.matchMedia('(min-width: 1025px)');

function onBreakpointChange(e) {
    isDesktop.value = e.matches;
    if (e.matches) drawerOpen.value = false;
}

function onKeydown(e) {
    if (e.key === 'Escape' && drawerOpen.value) {
        drawerOpen.value = false;
    }
}

onMounted(() => {
    isDesktop.value = mql.matches;
    mql.addEventListener('change', onBreakpointChange);
    document.addEventListener('keydown', onKeydown);
    router.on('navigate', () => {
        drawerOpen.value = false;
    });
});

onUnmounted(() => {
    mql.removeEventListener('change', onBreakpointChange);
    document.removeEventListener('keydown', onKeydown);
});

defineExpose({ drawerOpen });
</script>

<template>
    <!-- Desktop: fixed sidebar -->
    <aside
        v-if="isDesktop"
        data-testid="admin-sidebar"
        class="flex w-[220px] shrink-0 flex-col border-r border-[var(--color-border)] bg-[var(--color-surface)]"
    >
        <div class="flex h-12 items-center px-5 border-b border-[var(--color-border)]">
            <AppLogo />
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <Link
                v-for="item in navItems"
                :key="item.href"
                :href="item.href"
                :data-testid="`nav-${item.label.toLowerCase()}`"
                :class="itemClass(item.href)"
                class="flex items-center gap-2.5 rounded-md px-3 py-[7px] text-[13px] font-medium transition-colors"
            >
                {{ item.label }}
            </Link>

            <div class="pt-4">
                <p class="px-3 pb-2 text-[10px] font-semibold tracking-[0.08em] text-[var(--color-text-muted)] uppercase">Settings</p>
                <Link
                    v-for="item in settingsItems"
                    :key="item.href"
                    :href="item.href"
                    :data-testid="`nav-${item.label.toLowerCase()}`"
                    :class="itemClass(item.href)"
                    class="flex items-center gap-2.5 rounded-md px-3 py-[7px] text-[13px] font-medium transition-colors"
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

    <!-- Mobile/tablet: slide-out drawer -->
    <template v-else>
        <Teleport to="body">
            <Transition name="drawer">
                <div
                    v-if="drawerOpen"
                    data-testid="admin-drawer-overlay"
                    class="fixed inset-0 z-[60] bg-black/50"
                    @click="drawerOpen = false"
                />
            </Transition>

            <Transition name="drawer-panel">
                <aside
                    v-if="drawerOpen"
                    data-testid="admin-drawer"
                    class="fixed top-0 left-0 z-[70] flex h-full w-[260px] flex-col bg-[var(--color-surface)] shadow-xl"
                >
                    <div class="flex items-center justify-between px-5 pt-5 pb-4">
                        <AppLogo />
                        <button
                            data-testid="admin-drawer-close"
                            class="rounded p-1 text-[var(--color-text-muted)] hover:bg-[var(--color-surface-hover)] hover:text-[var(--color-text)]"
                            @click="drawerOpen = false"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                            </svg>
                        </button>
                    </div>

                    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto pb-6">
                        <Link
                            v-for="item in navItems"
                            :key="item.href"
                            :href="item.href"
                            :data-testid="`nav-${item.label.toLowerCase()}`"
                            :class="itemClass(item.href)"
                            class="flex items-center gap-2.5 rounded-md px-3 py-[7px] text-[13px] font-medium transition-colors"
                        >
                            {{ item.label }}
                        </Link>

                        <div class="pt-4">
                            <p class="px-3 pb-2 text-[10px] font-semibold tracking-[0.08em] text-[var(--color-text-muted)] uppercase">Settings</p>
                            <Link
                                v-for="item in settingsItems"
                                :key="item.href"
                                :href="item.href"
                                :data-testid="`nav-${item.label.toLowerCase()}`"
                                :class="itemClass(item.href)"
                                class="flex items-center gap-2.5 rounded-md px-3 py-[7px] text-[13px] font-medium transition-colors"
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
            </Transition>
        </Teleport>
    </template>
</template>

<style scoped>
.drawer-enter-active,
.drawer-leave-active {
    transition: opacity 200ms ease;
}
.drawer-enter-from,
.drawer-leave-to {
    opacity: 0;
}

.drawer-panel-enter-active,
.drawer-panel-leave-active {
    transition: transform 200ms ease;
}
.drawer-panel-enter-from,
.drawer-panel-leave-to {
    transform: translateX(-100%);
}
</style>
