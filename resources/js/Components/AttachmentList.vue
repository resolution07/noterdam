<script setup lang="ts">
import { computed } from 'vue';
import { FileText, Download, Trash2, Image as ImageIcon } from 'lucide-vue-next';
import type { Attachment } from '@/types';

const props = defineProps<{
    attachments: Attachment[];
    canDelete?: boolean;
}>();

const emit = defineEmits<{
    (e: 'delete', id: number): void;
}>();

const totalSize = computed(() =>
    props.attachments.reduce((sum, a) => sum + (a.size || 0), 0),
);

function formatBytes(bytes: number): string {
    if (bytes === 0) return '0 Б';
    const k = 1024;
    const sizes = ['Б', 'КБ', 'МБ', 'ГБ'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

function isImage(mime: string): boolean {
    return mime.startsWith('image/');
}
</script>

<template>
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-medium text-[var(--text)]">
                Вложения ({{ attachments.length }}) · {{ formatBytes(totalSize) }}
            </h3>
        </div>

        <div v-if="attachments.length === 0" class="text-sm text-[var(--muted)]">
            Нет вложений.
        </div>

        <ul v-else class="space-y-2">
            <li
                v-for="attachment in attachments"
                :key="attachment.id"
                class="flex items-center justify-between rounded-lg border border-[var(--border)] bg-[var(--surface)] p-3"
            >
                <div class="flex items-center gap-3 overflow-hidden">
                    <component
                        :is="isImage(attachment.mime_type) ? ImageIcon : FileText"
                        class="h-5 w-5 shrink-0 text-[var(--muted)]"
                    />
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-[var(--text)]">
                            {{ attachment.original_name }}
                        </p>
                        <p class="text-xs text-[var(--muted)]">
                            {{ formatBytes(attachment.size) }} · {{ attachment.uploader?.name || '—' }}
                        </p>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-1">
                    <a
                        :href="route('attachments.download', attachment.id)"
                        class="rounded p-1.5 text-[var(--muted)] hover:bg-[var(--surface-elevated)] hover:text-[var(--text)]"
                        aria-label="Скачать"
                    >
                        <Download class="h-4 w-4" />
                    </a>
                    <button
                        v-if="canDelete"
                        type="button"
                        class="rounded p-1.5 text-[var(--muted)] hover:bg-rose-500/10 hover:text-rose-500"
                        aria-label="Удалить"
                        @click="emit('delete', attachment.id)"
                    >
                        <Trash2 class="h-4 w-4" />
                    </button>
                </div>
            </li>
        </ul>
    </div>
</template>
