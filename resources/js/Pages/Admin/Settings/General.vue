<script setup>
import { useForm } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import SectionHeader from '@/Components/UI/SectionHeader.vue';
import FormField from '@/Components/UI/FormField.vue';

defineOptions({ layout: SettingsLayout });

const props = defineProps({
    settings: { type: Object, required: true },
});

const form = useForm({
    terms_url: props.settings.terms_url ?? '',
    privacy_url: props.settings.privacy_url ?? '',
    device_code_expiry: String(props.settings.device_code_expiry),
});

function submit() {
    form.put('/admin/settings/general');
}
</script>

<template>
    <div>
        <SectionHeader title="General" />

        <div class="max-w-2xl rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Terms URL -->
                <FormField label="Terms of Service URL" name="terms_url" :error="form.errors.terms_url">
                    <input
                        id="terms_url"
                        v-model="form.terms_url"
                        type="url"
                        placeholder="https://example.com/terms"
                        class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-input-bg)] px-3 py-2 text-sm text-[var(--color-text)] placeholder-[var(--color-text-muted)] outline-none transition-colors focus:border-[var(--color-primary)] focus:ring-2 focus:ring-[var(--color-primary)]/20"
                    />
                </FormField>

                <!-- Privacy URL -->
                <FormField label="Privacy Policy URL" name="privacy_url" :error="form.errors.privacy_url">
                    <input
                        id="privacy_url"
                        v-model="form.privacy_url"
                        type="url"
                        placeholder="https://example.com/privacy"
                        class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-input-bg)] px-3 py-2 text-sm text-[var(--color-text)] placeholder-[var(--color-text-muted)] outline-none transition-colors focus:border-[var(--color-primary)] focus:ring-2 focus:ring-[var(--color-primary)]/20"
                    />
                </FormField>

                <!-- Device Code Expiry -->
                <FormField label="Device Code Expiry" name="device_code_expiry" required :error="form.errors.device_code_expiry">
                    <div class="flex items-center gap-3">
                        <input
                            id="device_code_expiry"
                            v-model="form.device_code_expiry"
                            type="number"
                            min="60"
                            max="3600"
                            step="1"
                            class="w-32 rounded-lg border border-[var(--color-border)] bg-[var(--color-input-bg)] px-3 py-2 text-sm text-[var(--color-text)] outline-none transition-colors focus:border-[var(--color-primary)] focus:ring-2 focus:ring-[var(--color-primary)]/20"
                        />
                        <span class="text-[13px] text-[var(--color-text-muted)]">seconds</span>
                    </div>
                    <p class="text-xs text-[var(--color-text-muted)]">
                        How long a device code remains valid before it expires. Default is 300 seconds (5 minutes). Range: 60–3600.
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
