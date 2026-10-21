<script setup>
import { ref, nextTick, onMounted } from 'vue';

const props = defineProps({
    length: { type: Number, default: 4 },
    error: { type: String, default: '' },
});

const emit = defineEmits(['complete']);

const inputs = ref([]);
const values = ref(Array(props.length).fill(''));

onMounted(() => {
    inputs.value[0]?.focus();
});

function onInput(index, event) {
    const val = event.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    values.value[index] = val.charAt(0) || '';
    event.target.value = values.value[index];

    if (values.value[index] && index < props.length - 1) {
        nextTick(() => inputs.value[index + 1]?.focus());
    }

    const code = values.value.join('');
    if (code.length === props.length) {
        emit('complete', code);
    }
}

function onKeydown(index, event) {
    if (event.key === 'Backspace' && !values.value[index] && index > 0) {
        values.value[index - 1] = '';
        nextTick(() => inputs.value[index - 1]?.focus());
    }
}

function onPaste(event) {
    event.preventDefault();
    const pasted = (event.clipboardData?.getData('text') || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
    for (let i = 0; i < props.length && i < pasted.length; i++) {
        values.value[i] = pasted[i];
    }
    const focusIndex = Math.min(pasted.length, props.length - 1);
    nextTick(() => inputs.value[focusIndex]?.focus());

    const code = values.value.join('');
    if (code.length === props.length) {
        emit('complete', code);
    }
}
</script>

<template>
    <div data-testid="code-input">
        <div class="flex justify-center gap-3">
            <input
                v-for="(_, index) in values"
                :key="index"
                :ref="(el) => (inputs[index] = el)"
                type="text"
                maxlength="1"
                inputmode="text"
                autocomplete="off"
                :value="values[index]"
                :class="[
                    'h-16 w-14 rounded-lg border-2 bg-[var(--color-input-bg)] text-center font-mono text-2xl font-bold text-[var(--color-text)] outline-none transition-colors',
                    error
                        ? 'border-[var(--color-danger)]'
                        : 'border-[var(--color-border)] focus:border-[var(--color-primary)]',
                ]"
                @input="onInput(index, $event)"
                @keydown="onKeydown(index, $event)"
                @paste="onPaste"
            />
        </div>
        <p v-if="error" class="mt-3 text-center text-sm text-[var(--color-danger)]">
            {{ error }}
        </p>
    </div>
</template>
