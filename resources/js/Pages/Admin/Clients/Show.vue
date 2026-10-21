<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SectionHeader from '@/Components/UI/SectionHeader.vue';
import FormField from '@/Components/UI/FormField.vue';
import StatusPill from '@/Components/UI/StatusPill.vue';
import ConfirmModal from '@/Components/UI/ConfirmModal.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    client: { type: Object, required: true },
});

const form = useForm({
    name: props.client.name,
    enabled: props.client.enabled,
});

const showDeleteModal = ref(false);
const deleting = ref(false);
const copiedField = ref(null);

function save() {
    form.put(`/admin/clients/${props.client.id}`);
}

function confirmDelete() {
    deleting.value = true;
    router.delete(`/admin/clients/${props.client.id}`, {
        onFinish: () => {
            deleting.value = false;
            showDeleteModal.value = false;
        },
    });
}

async function copyToClipboard(value, field) {
    try {
        await navigator.clipboard.writeText(value);
        copiedField.value = field;
        setTimeout(() => { copiedField.value = null; }, 2000);
    } catch {
        // Fallback: no-op in test environments
    }
}
</script>

<template>
    <div>
        <SectionHeader :title="client.name">
            <template #actions>
                <StatusPill
                    :status="client.enabled ? 'success' : 'neutral'"
                    :label="client.enabled ? 'Enabled' : 'Disabled'"
                />
            </template>
        </SectionHeader>

        <!-- Credentials -->
        <div class="mb-8 rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5">
            <h3 class="mb-4 text-[11px] font-semibold tracking-[0.08em] text-[var(--color-text-muted)] uppercase">
                Credentials
            </h3>

            <div class="space-y-4">
                <div class="flex items-center justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <span class="block text-[10px] font-semibold tracking-[0.08em] text-[var(--color-text-muted)] uppercase">
                            Client ID
                        </span>
                        <span class="block truncate font-mono text-[13px] text-[var(--color-text)]">
                            {{ client.client_id }}
                        </span>
                    </div>
                    <button
                        type="button"
                        class="shrink-0 rounded-md border border-[var(--color-border)] px-3 py-[5px] text-[12px] font-medium text-[var(--color-text-secondary)] transition-colors hover:bg-[var(--color-surface-hover)]"
                        @click="copyToClipboard(client.client_id, 'client_id')"
                    >
                        {{ copiedField === 'client_id' ? 'Copied!' : 'Copy' }}
                    </button>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <span class="block text-[10px] font-semibold tracking-[0.08em] text-[var(--color-text-muted)] uppercase">
                            Client Secret
                        </span>
                        <span class="block truncate font-mono text-[13px] text-[var(--color-text)]">
                            {{ client.client_secret }}
                        </span>
                    </div>
                    <button
                        type="button"
                        class="shrink-0 rounded-md border border-[var(--color-border)] px-3 py-[5px] text-[12px] font-medium text-[var(--color-text-secondary)] transition-colors hover:bg-[var(--color-surface-hover)]"
                        @click="copyToClipboard(client.client_secret, 'client_secret')"
                    >
                        {{ copiedField === 'client_secret' ? 'Copied!' : 'Copy' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5">
            <h3 class="mb-4 text-[11px] font-semibold tracking-[0.08em] text-[var(--color-text-muted)] uppercase">
                Settings
            </h3>

            <form @submit.prevent="save" class="space-y-5">
                <FormField label="Name" name="name" required :error="form.errors.name">
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-input-bg)] px-3 py-2 text-sm text-[var(--color-text)] outline-none transition-colors focus:border-[var(--color-primary)] focus:ring-2 focus:ring-[var(--color-primary)]/20"
                    />
                </FormField>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="form.enabled = !form.enabled"
                        :class="[
                            'relative inline-flex h-[18px] w-[32px] items-center rounded-full transition-colors',
                            form.enabled ? 'bg-[var(--color-success)]' : 'bg-[var(--color-border)]',
                        ]"
                        role="switch"
                        :aria-checked="form.enabled"
                    >
                        <span
                            :class="[
                                'inline-block h-3.5 w-3.5 rounded-full bg-white shadow-sm transition-transform',
                                form.enabled ? 'translate-x-[15px]' : 'translate-x-[2px]',
                            ]"
                        />
                    </button>
                    <span class="text-[13px] text-[var(--color-text-secondary)]">
                        {{ form.enabled ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-[var(--color-primary)] px-4 py-[7px] text-[13px] font-semibold text-[var(--color-accent-text)] transition-colors hover:bg-[var(--color-primary-hover)] disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving…' : 'Save Changes' }}
                    </button>
                    <button
                        type="button"
                        class="rounded-lg border border-[var(--color-danger)]/40 px-4 py-[7px] text-[13px] font-semibold text-[var(--color-danger)] transition-colors hover:bg-[var(--color-danger)]/12"
                        @click="showDeleteModal = true"
                    >
                        Delete Client
                    </button>
                </div>
            </form>
        </div>

        <ConfirmModal
            :show="showDeleteModal"
            title="Delete Client"
            :message="`Are you sure you want to delete '${client.name}'? This action cannot be undone.`"
            confirm-label="Delete"
            variant="danger"
            :loading="deleting"
            @confirm="confirmDelete"
            @cancel="showDeleteModal = false"
        />
    </div>
</template>
