<script setup lang="ts">
import { cn } from '@/lib/utils';

defineProps<{
    for?: string;
    class?: string;
}>();
</script>

<template>
    <label
        :for="$props.for"
        :class="cn('mb-1.5 block text-xs font-medium text-[var(--muted)]', $props.class)"
    >
        <slot />
    </label>
</template>
