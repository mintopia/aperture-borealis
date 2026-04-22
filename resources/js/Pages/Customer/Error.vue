<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

defineOptions({ layout: CustomerLayout });

defineProps({
    reason: { type: String, default: 'unknown' },
});

const messages = {
    expired: 'The code has expired. Please request a new one.',
    session: 'Your session was lost. Please start over.',
    invalid: 'The code is no longer valid.',
    auth_failed: 'Authentication with the provider failed. Please try again.',
    unknown: 'Something went wrong. Please try again.',
};
</script>

<template>
    <div class="text-center">
        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-[var(--color-danger)]/10">
            <span class="text-3xl">✕</span>
        </div>

        <h2 class="font-heading text-2xl font-bold text-[var(--color-text)] mb-2">
            Something Went Wrong
        </h2>
        <p class="text-[var(--color-text-secondary)] text-sm mb-8">
            {{ messages[reason] || messages.unknown }}
        </p>

        <a
            href="/auth"
            class="inline-block rounded-lg bg-[var(--color-primary)] px-6 py-3 text-[15px] font-semibold text-[var(--color-accent-text)] transition-colors hover:bg-[var(--color-primary-hover)]"
        >
            Try Again
        </a>
    </div>
</template>
