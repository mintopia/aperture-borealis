<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SectionHeader from '@/Components/UI/SectionHeader.vue';
import StatCard from '@/Components/UI/StatCard.vue';
import DataTable from '@/Components/UI/DataTable.vue';

defineOptions({ layout: AdminLayout });

defineProps({
    stats: { type: Object, default: () => ({}) },
    recentAuths: { type: Array, default: () => [] },
});

const columns = [
    { key: 'nickname', label: 'User' },
    { key: 'email', label: 'Email' },
    { key: 'provider', label: 'Provider' },
    { key: 'client', label: 'Client' },
    { key: 'created_at', label: 'Time' },
];
</script>

<template>
    <div>
        <SectionHeader title="Dashboard" />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <StatCard label="Total Clients" :value="stats.total_clients ?? 0" />
            <StatCard label="Active Clients" :value="stats.active_clients ?? 0" color="success" />
            <StatCard label="Total Auths" :value="stats.total_auths ?? 0" color="primary" />
            <StatCard label="Pending Codes" :value="stats.pending_codes ?? 0" color="warning" />
        </div>

        <SectionHeader title="Recent Authentications" />

        <DataTable
            :columns="columns"
            :rows="recentAuths"
            empty-message="No authentications yet."
        >
            <template #row="{ row }">
                <td class="text-[var(--color-text)]">{{ row.nickname }}</td>
                <td class="text-[var(--color-text-secondary)]">{{ row.email }}</td>
                <td class="text-[var(--color-text-secondary)]">{{ row.provider }}</td>
                <td class="text-[var(--color-text-secondary)]">{{ row.client }}</td>
                <td class="font-mono text-[var(--color-text-muted)]">{{ row.created_at }}</td>
            </template>
        </DataTable>
    </div>
</template>
