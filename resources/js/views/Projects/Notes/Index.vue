<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter, RouterLink } from 'vue-router';
import {
    Search,
    Pin,
    ArrowLeft,
} from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import NoteEditor from '@/Components/NoteEditor.vue';
import client from '@/api/client';
import type { Project, Note, Tag } from '@/types';

const route = useRoute();
const router = useRouter();

const projectId = computed(() => Number(route.params.projectId));
const project = ref<Project | null>(null);
const notes = ref<Note[]>([]);
const tags = ref<Tag[]>([]);
const search = ref('');
const content = ref('# Новая заметка\n\n');
const title = ref('Новая заметка');
const selectedTagIds = ref<number[]>([]);
const processing = ref(false);
const loading = ref(false);

const filteredNotes = computed(() => {
    const term = search.value.toLowerCase();
    return notes.value.filter((n) =>
        n.title.toLowerCase().includes(term) ||
        n.content.toLowerCase().includes(term),
    );
});

const fetchData = async () => {
    loading.value = true;
    try {
        const [projectResponse, notesResponse, tagsResponse] = await Promise.all([
            client.get<{ data: Project }>(`/projects/${projectId.value}`),
            client.get<{ data: Note[] }>(`/projects/${projectId.value}/notes`),
            client.get<{ data: Tag[] }>('/tags'),
        ]);
        project.value = projectResponse.data.data;
        notes.value = notesResponse.data.data;
        tags.value = tagsResponse.data.data;
    } catch (error: any) {
        toast.error(error.response?.data?.message || 'Ошибка загрузки');
    } finally {
        loading.value = false;
    }
};

const submit = async () => {
    processing.value = true;
    try {
        const response = await client.post<{ data: Note }>(`/projects/${projectId.value}/notes`, {
            title: title.value,
            content: content.value,
            tag_ids: selectedTagIds.value,
        });
        toast.success('Заметка создана');
        router.push(`/projects/${projectId.value}/notes/${response.data.data.id}`);
    } catch (error: any) {
        if (error.response?.data?.errors) {
            const messages = Object.values(error.response.data.errors).flat().join('\n');
            toast.error(messages);
        } else {
            toast.error(error.response?.data?.message || 'Ошибка создания заметки');
        }
    } finally {
        processing.value = false;
    }
};

onMounted(fetchData);
</script>

<template>
    <AuthenticatedLayout>
        <div v-if="loading" class="py-12 text-center text-[var(--muted)]">
            Загрузка…
        </div>
        <div v-else-if="!project" class="py-12 text-center text-[var(--muted)]">
            Проект не найден.
        </div>
        <div v-else class="flex h-[calc(100vh-8rem)] flex-col gap-4 lg:flex-row">
            <aside class="flex w-full flex-col overflow-hidden rounded-xl border border-[var(--border)] bg-[var(--surface)] lg:w-80">
                <div class="flex items-center justify-between border-b border-[var(--border)] p-3">
                    <RouterLink :to="`/projects/${project.id}`" class="text-sm text-[var(--muted)] hover:text-[var(--text)]">
                        <ArrowLeft class="inline h-4 w-4" /> Назад
                    </RouterLink>
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
                        <RouterLink
                            v-for="note in filteredNotes"
                            :key="note.id"
                            :to="`/projects/${project.id}/notes/${note.id}`"
                            class="flex items-start gap-2 rounded-lg p-2 transition hover:bg-[var(--surface-elevated)]"
                        >
                            <Pin v-if="note.is_pinned" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-500" />
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-[var(--text)]">{{ note.title }}</p>
                                <p class="text-xs text-[var(--muted)]">{{ new Date(note.updated_at).toLocaleDateString('ru-RU') }}</p>
                            </div>
                        </RouterLink>
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
