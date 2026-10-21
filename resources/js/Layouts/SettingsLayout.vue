<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SettingsNav from '@/Components/Admin/SettingsNav.vue';
</script>

<template>
    <AdminLayout>
        <div class="-mx-10 -mt-8 -mb-16 flex min-h-full">
            <div class="border-r border-[var(--color-border)] px-6 pt-8">
                <SettingsNav />
            </div>
            <div class="flex-1 px-10 pt-8 pb-16">
                <slot />
            </div>
        </div>
    </AdminLayout>
</template>
