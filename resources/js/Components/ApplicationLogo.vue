<script setup lang="ts">
import { Triangle } from 'lucide-vue-next';

defineProps<{
    class?: string;
}>();
</script>

<template>
    <div :class="['inline-flex items-center gap-2', $props.class]">
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500 text-white">
            <Triangle class="h-5 w-5 fill-current" />
        </div>
        <span class="text-lg font-semibold tracking-tight text-[var(--text)]">Noterdam</span>
    </div>
</template>
