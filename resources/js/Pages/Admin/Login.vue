<script setup>
import { useForm } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import ProviderButton from '@/Components/Customer/ProviderButton.vue';
import AppLogo from '@/Components/AppLogo.vue';

defineOptions({ layout: CustomerLayout });

const props = defineProps({
    providers: { type: Array, default: () => [] },
});

const form = useForm({
    email: '',
    password: '',
});

function submitLogin() {
    form.post('/admin/login');
}
</script>

<template>
    <div class="w-full max-w-[400px] mx-auto">
        <!-- Brand -->
        <div class="flex items-center justify-center mb-1">
            <AppLogo class="text-2xl" />
        </div>
        <p class="text-center text-[13px] text-[var(--color-text-muted)] mb-10">
            Admin Dashboard
        </p>

        <!-- Social providers -->
        <div v-if="providers.length" class="space-y-3 mb-4">
            <ProviderButton
                v-for="provider in providers"
                :key="provider.code"
                :provider="provider"
                :href="`/admin/login/${provider.code}/redirect`"
            />
        </div>

        <!-- Divider -->
        <div v-if="providers.length" class="flex items-center gap-3 my-4">
            <div class="flex-1 h-px bg-[var(--color-border)]"></div>
            <span class="text-xs text-[var(--color-text-muted)]">or</span>
            <div class="flex-1 h-px bg-[var(--color-border)]"></div>
        </div>

        <!-- Email/Password form -->
        <form @submit.prevent="submitLogin" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)] mb-1.5">
                    Email
                </label>
                <input
                    v-model="form.email"
                    type="email"
                    class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-input-bg)] px-3 py-2.5 text-sm text-[var(--color-text)] placeholder-[var(--color-text-muted)] outline-none transition-colors focus:border-[var(--color-primary)] focus:ring-2 focus:ring-[var(--color-primary)]/20"
                    placeholder="admin@example.com"
                />
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)] mb-1.5">
                    Password
                </label>
                <input
                    v-model="form.password"
                    type="password"
                    class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-input-bg)] px-3 py-2.5 text-sm text-[var(--color-text)] placeholder-[var(--color-text-muted)] outline-none transition-colors focus:border-[var(--color-primary)] focus:ring-2 focus:ring-[var(--color-primary)]/20"
                    placeholder="••••••••"
                />
            </div>

            <div v-if="form.errors.email" class="text-sm text-[var(--color-danger)]">
                {{ form.errors.email }}
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-lg bg-[var(--color-primary)] px-4 py-2.5 text-[15px] font-semibold text-[var(--color-accent-text)] transition-colors hover:bg-[var(--color-primary-hover)] disabled:opacity-50"
            >
                <span v-if="form.processing">Signing in…</span>
                <span v-else>Sign In</span>
            </button>
        </form>

        <!-- Passkey divider + button -->
        <div class="flex items-center gap-3 my-4">
            <div class="flex-1 h-px bg-[var(--color-border)]"></div>
            <span class="text-xs text-[var(--color-text-muted)]">or</span>
            <div class="flex-1 h-px bg-[var(--color-border)]"></div>
        </div>

        <button
            type="button"
            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] px-4 py-2.5 text-[15px] font-semibold text-[var(--color-text)] transition-colors hover:bg-[var(--color-surface-hover)]"
        >
            🔑 Sign in with Passkey
        </button>
    </div>
</template>
