<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';

const props = defineProps<{
    href: string;
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
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
