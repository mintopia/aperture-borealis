<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SectionHeader from '@/Components/UI/SectionHeader.vue';
import DataTable from '@/Components/UI/DataTable.vue';
import StatusPill from '@/Components/UI/StatusPill.vue';

defineOptions({ layout: AdminLayout });

defineProps({
    clients: { type: Array, default: () => [] },
});

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'client_id', label: 'Client ID' },
    { key: 'status', label: 'Status' },
    { key: 'created_at', label: 'Created' },
    { key: 'actions', label: 'Actions', srOnly: true },
];
</script>

<template>
    <div>
        <SectionHeader title="Clients">
            <template #actions>
                <Link
                    href="/admin/clients/create"
                    class="rounded-lg bg-[var(--color-primary)] px-4 py-[7px] text-[13px] font-semibold text-[var(--color-accent-text)] transition-colors hover:bg-[var(--color-primary-hover)]"
                >
                    New Client
                </Link>
            </template>
        </SectionHeader>

        <DataTable
            :columns="columns"
            :rows="clients"
            clickable
            :row-href="(row) => `/admin/clients/${row.id}`"
            :row-aria-label="(row) => `View client ${row.name}`"
            empty-message="No clients yet."
        >
            <template #row="{ row }">
                <td class="text-[var(--color-text)] font-medium">{{ row.name }}</td>
                <td class="font-mono text-[var(--color-text-secondary)]">{{ row.client_id.substring(0, 8) }}…</td>
                <td>
                    <StatusPill
                        :status="row.enabled ? 'success' : 'neutral'"
                        :label="row.enabled ? 'Enabled' : 'Disabled'"
                    />
                </td>
                <td class="font-mono text-[var(--color-text-muted)]">{{ row.created_at }}</td>
                <td class="text-right">
                    <Link
                        :href="`/admin/clients/${row.id}`"
                        class="text-[13px] font-medium text-[var(--color-primary)] hover:underline"
                        @click.stop
                    >
                        View
                    </Link>
                </td>
            </template>
        </DataTable>
    </div>
</template>
