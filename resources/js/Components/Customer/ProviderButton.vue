<script setup>
defineProps({
    provider: { type: Object, required: true },
    href: { type: String, required: true },
    loading: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
});

const brandColors = {
    discord: { bg: 'bg-[#5865F2]', hover: 'hover:bg-[#4752C4]' },
    google: { bg: 'bg-[#4285F4]', hover: 'hover:bg-[#3367D6]' },
    steam: { bg: 'bg-[#171A21]', hover: 'hover:bg-[#2A475E]' },
    twitch: { bg: 'bg-[#9146FF]', hover: 'hover:bg-[#772CE8]' },
    authentik: { bg: 'bg-[#FD4B2D]', hover: 'hover:bg-[#E04328]' },
    laravelpassport: { bg: 'bg-[#FF2D20]', hover: 'hover:bg-[#E0261B]' },
};
</script>

<template>
    <a
        :href="href"
        :class="[
            'flex w-full items-center justify-center gap-3 rounded-lg px-6 py-3.5 text-[15px] font-semibold text-white transition-colors',
            brandColors[provider.code]?.bg ?? 'bg-[var(--color-primary)]',
            brandColors[provider.code]?.hover ?? 'hover:bg-[var(--color-primary-hover)]',
            (loading || disabled) ? 'pointer-events-none opacity-50' : '',
        ]"
        data-testid="provider-button"
    >
        <span v-if="loading">Connecting…</span>
        <span v-else>{{ provider.name }}</span>
    </a>
</template>
