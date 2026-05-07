<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import ProviderButton from '@/Components/Customer/ProviderButton.vue';

defineOptions({ layout: CustomerLayout });

defineProps({
    providers: { type: Array, default: () => [] },
});
</script>

<template>
    <div class="text-center">
        <h2 class="font-heading text-2xl font-bold text-[var(--color-text)] mb-2">
            Sign In
        </h2>
        <p class="text-[var(--color-text-secondary)] text-sm mb-8">
            Choose an account to continue
        </p>

        <div class="space-y-3">
            <ProviderButton
                v-for="provider in providers"
                :key="provider.code"
                :provider="provider"
                :href="`/auth/${provider.code}/redirect`"
            />
        </div>

        <div v-if="!providers.length" class="py-8 text-[var(--color-text-muted)] text-sm">
            No providers are currently configured.
        </div>
    </div>
</template>
