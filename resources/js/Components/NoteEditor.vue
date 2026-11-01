<script setup lang="ts">
import {
    ref,
    computed,
    watch,
    onMounted,
    onUnmounted,
} from 'vue';
import {
    Bold,
    Italic,
    Strikethrough,
    Code,
    Heading1,
    Heading2,
    Heading3,
    List,
    ListOrdered,
    Quote,
    Link,
    Image,
    Table,
    Save,
    Eye,
    PenLine,
} from 'lucide-vue-next';
import MarkdownPreview from './MarkdownPreview.vue';
import { MarkdownService } from '@/Services/MarkdownService';

const props = defineProps<{
    modelValue: string;
    title?: string;
    saveStatus?: 'saved' | 'saving' | 'unsaved';
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
    (e: 'update:title', value: string): void;
    (e: 'save'): void;
    (e: 'autosave'): void;
    (e: 'paste', event: ClipboardEvent): void;
}>();

const content = computed({
    get: () => props.modelValue,
    set: (v) => emit('update:modelValue', v),
});

const title = computed({
    get: () => props.title || '',
    set: (v) => emit('update:title', v),
});

const textarea = ref<HTMLTextAreaElement | null>(null);
const mobileTab = ref<'edit' | 'preview'>('edit');
const statusText = computed(() => {
    switch (props.saveStatus) {
        case 'saving':
            return 'Сохранение…';
        case 'saved':
            return 'Сохранено';
        default:
            return 'Есть несохранённые изменения';
    }
});

const previewHtml = computed(() => MarkdownService.render(content.value));

const insert = (before: string, after: string = '') => {
    const el = textarea.value;
    if (!el) return;
    const start = el.selectionStart;
    const end = el.selectionEnd;
    const selected = content.value.slice(start, end);
    const replacement = before + selected + after;
    content.value = content.value.slice(0, start) + replacement + content.value.slice(end);
    requestAnimationFrame(() => {
        el.focus();
        const caret = start + before.length + selected.length;
        el.setSelectionRange(caret, caret);
    });
};

const wrapLine = (prefix: string) => {
    const el = textarea.value;
    if (!el) return;
    const start = el.selectionStart;
    const lineStart = content.value.lastIndexOf('\n', start - 1) + 1;
    content.value =
        content.value.slice(0, lineStart) +
        prefix +
        content.value.slice(lineStart);
    requestAnimationFrame(() => {
        el.focus();
        el.setSelectionRange(lineStart + prefix.length, lineStart + prefix.length);
    });
};

const toolbar = [
    { icon: Bold, title: 'Жирный', action: () => insert('**', '**'), hotkey: 'Ctrl+B' },
    { icon: Italic, title: 'Курсив', action: () => insert('_', '_'), hotkey: 'Ctrl+I' },
    { icon: Strikethrough, title: 'Зачёркнутый', action: () => insert('~~', '~~'), hotkey: 'Ctrl+S' },
    { icon: Code, title: 'Код', action: () => insert('`', '`') },
    { icon: Code, title: 'Блок кода', action: () => insert('```\n', '\n```') },
    { icon: Heading1, title: 'Заголовок 1', action: () => wrapLine('# ') },
    { icon: Heading2, title: 'Заголовок 2', action: () => wrapLine('## ') },
    { icon: Heading3, title: 'Заголовок 3', action: () => wrapLine('### ') },
    { icon: List, title: 'Список', action: () => wrapLine('- ') },
    { icon: ListOrdered, title: 'Нумерованный список', action: () => wrapLine('1. ') },
    { icon: Quote, title: 'Цитата', action: () => wrapLine('> ') },
    { icon: Link, title: 'Ссылка', action: () => insert('[', '](url)'), hotkey: 'Ctrl+K' },
    { icon: Image, title: 'Изображение', action: () => insert('![', '](url)') },
    { icon: Table, title: 'Таблица', action: () => insert('| Колонка 1 | Колонка 2 |\n| --- | --- |\n| ', ' |  |') },
];

const onKeydown = (e: KeyboardEvent) => {
    const mod = e.ctrlKey || e.metaKey;
    if (mod && e.key.toLowerCase() === 'b') {
        e.preventDefault();
        toolbar[0].action();
    } else if (mod && e.key.toLowerCase() === 'i') {
        e.preventDefault();
        toolbar[1].action();
    } else if (mod && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        toolbar[11].action();
    } else if (mod && e.key.toLowerCase() === 's') {
        e.preventDefault();
        toolbar[2].action();
    } else if (e.shiftKey && e.key === 'Enter') {
        e.preventDefault();
        emit('save');
    }
};

let autosaveTimer: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    autosaveTimer = setInterval(() => {
        if (props.saveStatus === 'unsaved') emit('autosave');
    }, 30000);
});

onUnmounted(() => {
    if (autosaveTimer) clearInterval(autosaveTimer);
});

watch(content, () => {
    if (mobileTab.value === 'preview') mobileTab.value = 'edit';
});
</script>

<template>
    <div class="flex h-full flex-col">
        <div class="flex items-center gap-2 border-b border-[var(--border)] p-2">
            <button
                v-for="item in toolbar"
                :key="item.title"
                type="button"
                :title="item.title + (item.hotkey ? ` (${item.hotkey})` : '')"
                class="rounded p-1.5 text-[var(--muted)] transition hover:bg-[var(--surface-elevated)] hover:text-[var(--text)]"
                @click="item.action"
            >
                <component :is="item.icon" class="h-4 w-4" />
            </button>
            <div class="ml-auto flex items-center gap-3">
                <span class="text-xs text-[var(--muted)]">{{ statusText }}</span>
                <button type="button" class="btn-primary gap-2" @click="emit('save')">
                    <Save class="h-4 w-4" /> Сохранить
                </button>
            </div>
        </div>

        <input
            v-model="title"
            type="text"
            placeholder="Заголовок заметки"
            class="border-b border-[var(--border)] bg-transparent px-4 py-3 text-xl font-semibold text-[var(--text)] placeholder:text-[var(--muted)] focus:outline-none"
        />

        <div class="flex flex-1 overflow-hidden">
            <div
                :class="[
                    'flex flex-1 flex-col border-r border-[var(--border)]',
                    mobileTab === 'preview' ? 'hidden lg:flex' : 'flex',
                ]"
            >
                <textarea
                    ref="textarea"
                    v-model="content"
                    class="flex-1 resize-none bg-transparent p-4 font-mono text-sm leading-relaxed text-[var(--text)] placeholder:text-[var(--muted)] focus:outline-none"
                    placeholder="Начните писать в Markdown…"
                    @keydown="onKeydown"
                    @paste="emit('paste', $event)"
                />
            </div>

            <div
                :class="[
                    'flex-1 overflow-auto bg-[var(--background)] p-6',
                    mobileTab === 'edit' ? 'hidden lg:block' : 'block',
                ]"
            >
                <MarkdownPreview :html="previewHtml" />
            </div>
        </div>

        <div class="flex items-center justify-between border-t border-[var(--border)] p-2 lg:hidden">
            <button
                type="button"
                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium"
                :class="mobileTab === 'edit' ? 'bg-[var(--surface-elevated)] text-[var(--text)]' : 'text-[var(--muted)]'"
                @click="mobileTab = 'edit'"
            >
                <PenLine class="h-4 w-4" /> Редактор
            </button>
            <button
                type="button"
                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium"
                :class="mobileTab === 'preview' ? 'bg-[var(--surface-elevated)] text-[var(--text)]' : 'text-[var(--muted)]'"
                @click="mobileTab = 'preview'"
            >
                <Eye class="h-4 w-4" /> Просмотр
            </button>
        </div>
    </div>
</template>
