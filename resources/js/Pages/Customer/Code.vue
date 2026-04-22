<script setup>
import { useForm } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import CodeInput from '@/Components/Customer/CodeInput.vue';

defineOptions({ layout: CustomerLayout });

const form = useForm({ code: '' });

function onCodeComplete(code) {
    form.code = code;
    form.post('/auth');
}
</script>

<template>
    <div class="text-center">
        <h2 class="font-heading text-2xl font-bold text-[var(--color-text)] mb-2">
            Link Your Account
        </h2>
        <p class="text-[var(--color-text-secondary)] text-sm mb-8">
            Enter the code shown on your screen
        </p>

        <CodeInput
            :error="form.errors.code"
            @complete="onCodeComplete"
        />

        <div v-if="form.processing" class="mt-6 text-[var(--color-text-muted)] text-sm">
            Verifying…
        </div>
    </div>
</template>
