<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    Plus,
    Search,
    Pin,
    FileText,
    ArrowLeft,
} from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Button from '@/Components/Button.vue';
import NoteEditor from '@/Components/NoteEditor.vue';
import type { Project, Note, Tag } from '@/types';

const props = defineProps<{
    project: Project;
    notes: Note[];
    tags: Tag[];
    q: string;
}>();

const search = ref(props.q || '');
const content = ref('# Новая заметка\n\n');
const title = ref('Новая заметка');
const selectedTagIds = ref<number[]>([]);

const form = useForm({
    title: '',
    content: '',
    tag_ids: [] as number[],
});

const filteredNotes = computed(() => {
    const term = search.value.toLowerCase();
    return props.notes.filter((n) =>
        n.title.toLowerCase().includes(term) ||
        n.content.toLowerCase().includes(term),
    );
});

const submit = () => {
    form.title = title.value;
    form.content = content.value;
    form.tag_ids = selectedTagIds.value;
    form.post(route('projects.notes.store', props.project.id));
};
</script>

<template>
    <Head title="Новая заметка" />

    <AuthenticatedLayout>
        <div class="flex h-[calc(100vh-8rem)] flex-col gap-4 lg:flex-row">
            <aside class="flex w-full flex-col overflow-hidden rounded-xl border border-[var(--border)] bg-[var(--surface)] lg:w-80">
                <div class="flex items-center justify-between border-b border-[var(--border)] p-3">
                    <Link :href="route('projects.show', project.id)" class="text-sm text-[var(--muted)] hover:text-[var(--text)]">
                        <ArrowLeft class="inline h-4 w-4" /> Назад
                    </Link>
                    <h2 class="text-sm font-medium text-[var(--text)]">Заметки</h2>
                </div>
                <div class="p-3">
                    <div class="relative">
                        <Search class="absolute left-2.5 top-2 h-4 w-4 text-[var(--muted)]" />
                        <input v-model="search" type="text" placeholder="Поиск…" class="input pl-9" />
                    </div>
                </div>
                <div class="flex-1 overflow-auto p-3 pt-0">
                    <div v-if="filteredNotes.length === 0" class="py-6 text-center text-sm text-[var(--muted)]">
                        Нет заметок
                    </div>
                    <div v-else class="space-y-1">
                        <Link
                            v-for="note in filteredNotes"
                            :key="note.id"
                            :href="route('projects.notes.show', [project.id, note.id])"
                            class="flex items-start gap-2 rounded-lg p-2 transition hover:bg-[var(--surface-elevated)]"
                        >
                            <Pin v-if="note.is_pinned" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-500" />
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-[var(--text)]">{{ note.title }}</p>
                                <p class="text-xs text-[var(--muted)]">{{ new Date(note.updated_at).toLocaleDateString('ru-RU') }}</p>
                            </div>
                        </Link>
                    </div>
                </div>
            </aside>

            <main class="flex flex-1 flex-col overflow-hidden rounded-xl border border-[var(--border)] bg-[var(--surface)]">
                <NoteEditor
                    v-model="content"
                    v-model:title="title"
                    save-status="unsaved"
                    @save="submit"
                    @autosave="submit"
                />
                <div class="border-t border-[var(--border)] p-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs text-[var(--muted)]">Теги:</span>
                        <button
                            v-for="tag in tags"
                            :key="tag.id"
                            type="button"
                            :class="[
                                'rounded-full px-2.5 py-1 text-xs transition',
                                selectedTagIds.includes(tag.id)
                                    ? 'bg-emerald-500/20 text-emerald-500'
                                    : 'bg-[var(--surface-elevated)] text-[var(--muted)] hover:text-[var(--text)]',
                            ]"
                            @click="
                                selectedTagIds.includes(tag.id)
                                    ? selectedTagIds.splice(selectedTagIds.indexOf(tag.id), 1)
                                    : selectedTagIds.push(tag.id)
                            "
                        >
                            {{ tag.name }}
                        </button>
                    </div>
                </div>
            </main>
        </div>
    </AuthenticatedLayout>
</template>
