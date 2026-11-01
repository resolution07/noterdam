<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    modelValue?: string | number;
    type?: string;
    class?: string;
    error?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const classes = computed(() =>
    cn(
        'w-full rounded-lg border border-[var(--border)] bg-[var(--surface)] px-3 py-2 text-sm text-[var(--text)] placeholder:text-[var(--muted)] focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 disabled:opacity-50',
        props.error && 'border-rose-500 focus:border-rose-500 focus:ring-rose-500',
        props.class,
    ),
);
</script>

<template>
    <div class="w-full">
        <input
            :type="type || 'text'"
            :value="modelValue"
            :class="classes"
            @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        />
        <p v-if="error" class="mt-1 text-xs text-rose-500">{{ error }}</p>
    </div>
</template>
