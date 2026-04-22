<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SectionHeader from '@/Components/UI/SectionHeader.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    providers: { type: Array, default: () => [] },
});

function toggleProvider(provider) {
    const form = useForm({ enabled: !provider.enabled });
    form.put(`/admin/providers/${provider.id}`);
}

function saveSettings(provider) {
    const form = useForm({
        settings: provider.settings.map(s => ({ id: s.id, value: s.value })),
    });
    form.put(`/admin/providers/${provider.id}`);
}
</script>

<template>
    <div>
        <SectionHeader title="Social Providers" />

        <div class="space-y-4">
            <div
                v-for="provider in providers"
                :key="provider.id"
                class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5"
            >
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-heading text-lg font-semibold text-[var(--color-text)]">
                            {{ provider.name }}
                        </h3>
                        <p class="text-xs font-mono text-[var(--color-text-muted)]">
                            {{ provider.code }}
                        </p>
                    </div>
                    <button
                        @click="toggleProvider(provider)"
                        :class="[
                            'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                            provider.enabled ? 'bg-[var(--color-success)]' : 'bg-[var(--color-border)]'
                        ]"
                    >
                        <span
                            :class="[
                                'inline-block h-4 w-4 rounded-full bg-white transition-transform',
                                provider.enabled ? 'translate-x-6' : 'translate-x-1'
                            ]"
                        />
                    </button>
                </div>

                <div v-if="provider.settings.length" class="space-y-3 border-t border-[var(--color-border)] pt-4">
                    <div v-for="setting in provider.settings" :key="setting.id">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)] mb-1.5">
                            {{ setting.name }}
                        </label>
                        <input
                            v-model="setting.value"
                            :type="setting.hidden ? 'password' : 'text'"
                            :placeholder="setting.hidden ? '••••••••' : ''"
                            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-input-bg)] px-3 py-2 text-sm text-[var(--color-text)] outline-none focus:border-[var(--color-primary)] focus:ring-2 focus:ring-[var(--color-primary)]/20"
                        />
                    </div>

                    <button
                        @click="saveSettings(provider)"
                        class="rounded-lg bg-[var(--color-primary)] px-4 py-2 text-sm font-semibold text-[var(--color-accent-text)] hover:bg-[var(--color-primary-hover)] transition-colors"
                    >
                        Save Settings
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
