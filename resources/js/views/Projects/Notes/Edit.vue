<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRoute, RouterLink } from 'vue-router';
import {
    ArrowLeft,
    Search,
    Pin,
    Upload,
} from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import NoteEditor from '@/Components/NoteEditor.vue';
import AttachmentList from '@/Components/AttachmentList.vue';
import client from '@/api/client';
import type { Project, Note, Tag, Attachment } from '@/types';

const route = useRoute();

const projectId = computed(() => Number(route.params.projectId));
const noteId = computed(() => Number(route.params.noteId));

const project = ref<Project | null>(null);
const notes = ref<Note[]>([]);
const note = ref<Note | null>(null);
const tags = ref<Tag[]>([]);
const search = ref('');
const content = ref('');
const title = ref('');
const selectedTagIds = ref<number[]>([]);
const saveStatus = ref<'saved' | 'saving' | 'unsaved'>('saved');
const fileInput = ref<HTMLInputElement | null>(null);
const uploading = ref(false);
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
        const [projectResponse, notesResponse, noteResponse, tagsResponse] = await Promise.all([
            client.get<{ data: Project }>(`/projects/${projectId.value}`),
            client.get<{ data: Note[] }>(`/projects/${projectId.value}/notes`),
            client.get<{ data: Note }>(`/notes/${noteId.value}`),
            client.get<{ data: Tag[] }>('/tags'),
        ]);
        project.value = projectResponse.data.data;
        notes.value = notesResponse.data.data;
        note.value = noteResponse.data.data;
        tags.value = tagsResponse.data.data;
        content.value = note.value.content;
        title.value = note.value.title;
        selectedTagIds.value = (note.value.tags || []).map((t) => t.id);
    } catch (error: any) {
        toast.error(error.response?.data?.message || 'Ошибка загрузки');
    } finally {
        loading.value = false;
    }
};

const submit = async () => {
    if (!note.value) return;
    saveStatus.value = 'saving';
    try {
        const response = await client.put<{ data: Note }>(`/notes/${note.value.id}`, {
            title: title.value,
            content: content.value,
            tag_ids: selectedTagIds.value,
        });
        note.value = response.data.data;
        content.value = note.value.content;
        title.value = note.value.title;
        saveStatus.value = 'saved';
        toast.success('Сохранено');
    } catch (error: any) {
        saveStatus.value = 'unsaved';
        toast.error(error.response?.data?.message || 'Ошибка сохранения');
    }
};

const autosave = () => submit();

const deleteAttachment = async (id: number) => {
    if (!confirm('Удалить вложение?')) return;
    try {
        await client.delete(`/attachments/${id}`);
        if (note.value) {
            note.value.attachments = note.value.attachments?.filter((a) => a.id !== id);
        }
        toast.success('Вложение удалено');
    } catch (error: any) {
        toast.error(error.response?.data?.message || 'Ошибка удаления');
    }
};

const upload = async (file: File) => {
    if (!note.value) return;
    uploading.value = true;
    const data = new FormData();
    data.append('file', file);
    try {
        const response = await client.post<{ data: Attachment }>(`/projects/${projectId.value}/notes/${note.value.id}/attachments`, data, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        if (note.value) {
            note.value.attachments = note.value.attachments || [];
            note.value.attachments.push(response.data.data);
        }
        toast.success('Файл загружен');
        if (fileInput.value) fileInput.value.value = '';
    } catch (error: any) {
        toast.error(error.response?.data?.message || 'Ошибка загрузки файла');
    } finally {
        uploading.value = false;
    }
};

const onFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files?.length) upload(target.files[0]);
};

const onDrop = (e: DragEvent) => {
    e.preventDefault();
    if (e.dataTransfer?.files.length) upload(e.dataTransfer.files[0]);
};

const onPaste = (e: ClipboardEvent) => {
    const items = e.clipboardData?.items;
    if (!items) return;
    for (const item of Array.from(items)) {
        if (item.type.startsWith('image/')) {
            const file = item.getAsFile();
            if (file) upload(file);
        }
    }
};

onMounted(fetchData);
</script>

<template>
    <AuthenticatedLayout>
        <div v-if="loading" class="py-12 text-center text-[var(--muted)]">
            Загрузка…
        </div>
        <div v-else-if="!project || !note" class="py-12 text-center text-[var(--muted)]">
            Заметка не найдена.
        </div>
        <div v-else class="flex h-[calc(100vh-8rem)] flex-col gap-4 lg:flex-row">
            <aside class="flex w-full flex-col overflow-hidden rounded-xl border border-[var(--border)] bg-[var(--surface)] lg:w-80">
                <div class="flex items-center justify-between border-b border-[var(--border)] p-3">
                    <RouterLink :to="`/projects/${project.id}`" class="text-sm text-[var(--muted)] hover:text-[var(--text)]">
                        <ArrowLeft class="inline h-4 w-4" /> {{ project.name }}
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
                            v-for="n in filteredNotes"
                            :key="n.id"
                            :to="`/projects/${project.id}/notes/${n.id}`"
                            class="flex items-start gap-2 rounded-lg p-2 transition hover:bg-[var(--surface-elevated)]"
                            :class="note.id === n.id ? 'bg-[var(--surface-elevated)]' : ''"
                        >
                            <Pin v-if="n.is_pinned" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-500" />
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-[var(--text)]">{{ n.title }}</p>
                                <p class="text-xs text-[var(--muted)]">{{ new Date(n.updated_at).toLocaleDateString('ru-RU') }}</p>
                            </div>
                        </RouterLink>
                    </div>
                </div>
            </aside>

            <main class="flex flex-1 flex-col overflow-hidden rounded-xl border border-[var(--border)] bg-[var(--surface)]">
                <NoteEditor
                    v-model="content"
                    v-model:title="title"
                    :save-status="saveStatus"
                    @save="submit"
                    @autosave="autosave"
                    @paste="onPaste"
                />

                <div class="border-t border-[var(--border)] p-4">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
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

                    <div
                        class="rounded-xl border border-dashed border-[var(--border)] bg-[var(--background)] p-4"
                        @dragover.prevent
                        @drop="onDrop"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-sm text-[var(--muted)]">
                                <Upload class="h-4 w-4" />
                                Перетащите файлы или
                                <button
                                    type="button"
                                    class="text-emerald-500 hover:underline"
                                    @click="fileInput?.click()"
                                >
                                    выберите
                                </button>
                            </div>
                            <input ref="fileInput" type="file" class="hidden" @change="onFileChange" />
                        </div>

                        <div class="mt-4">
                            <AttachmentList
                                :attachments="note.attachments || []"
                                can-delete
                                @delete="deleteAttachment"
                            />
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </AuthenticatedLayout>
</template>
