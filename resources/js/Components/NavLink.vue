<script setup lang="ts">
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import { cn } from '@/lib/utils';

const props = defineProps<{
    to: string;
    active?: boolean;
}>();

const classes = computed(() =>
    cn(
        'inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition',
        props.active
            ? 'bg-[var(--surface-elevated)] text-[var(--text)]'
            : 'text-[var(--muted)] hover:bg-[var(--surface)] hover:text-[var(--text)]',
    ),
);
</script>

<template>
    <RouterLink :to="to" :class="classes">
        <slot />
    </RouterLink>
</template>
