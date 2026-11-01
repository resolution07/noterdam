<script setup lang="ts">
import { computed } from 'vue';
import DOMPurify from 'dompurify';

const props = defineProps<{
    html: string;
}>();

const safeHtml = computed(() => DOMPurify.sanitize(props.html));
</script>

<template>
    <div
        class="prose dark:prose-invert max-w-none prose-pre:bg-[var(--surface-elevated)] prose-pre:rounded-lg prose-pre:p-4 prose-code:rounded prose-code:bg-[var(--surface-elevated)] prose-code:px-1 prose-code:py-0.5 prose-img:rounded-lg"
        v-html="safeHtml"
    />
</template>
