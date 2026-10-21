<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SectionHeader from '@/Components/UI/SectionHeader.vue';
import FormField from '@/Components/UI/FormField.vue';

defineOptions({ layout: AdminLayout });

const form = useForm({
    name: '',
    enabled: true,
});

function submit() {
    form.post('/admin/clients');
}
</script>

<template>
    <div>
        <SectionHeader title="New Client" />

        <div class="max-w-lg rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5">
            <form @submit.prevent="submit" class="space-y-5">
                <FormField label="Name" name="name" required :error="form.errors.name">
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        placeholder="My Application"
                        class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-input-bg)] px-3 py-2 text-sm text-[var(--color-text)] placeholder-[var(--color-text-muted)] outline-none transition-colors focus:border-[var(--color-primary)] focus:ring-2 focus:ring-[var(--color-primary)]/20"
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

                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-[var(--color-primary)] px-4 py-[7px] text-[13px] font-semibold text-[var(--color-accent-text)] transition-colors hover:bg-[var(--color-primary-hover)] disabled:opacity-50"
                    >
                        {{ form.processing ? 'Creating…' : 'Create Client' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
