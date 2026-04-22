<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

defineOptions({ layout: CustomerLayout });
</script>

<template>
    <div class="text-center">
        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-[var(--color-success)]/10">
            <span class="text-3xl">✓</span>
        </div>

        <h2 class="font-heading text-2xl font-bold text-[var(--color-text)] mb-2">
            Account Linked
        </h2>
        <p class="text-[var(--color-text-secondary)] text-sm">
            You can close this window and return to your application.
        </p>
    </div>
</template>
