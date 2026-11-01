<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Search,
    Pin,
    Plus,
    Upload,
    FileText,
} from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Button from '@/Components/Button.vue';
import NoteEditor from '@/Components/NoteEditor.vue';
import AttachmentList from '@/Components/AttachmentList.vue';
import type { Project, Note, Tag } from '@/types';

const props = defineProps<{
    project: Project;
    notes: Note[];
    note: Note;
    tags: Tag[];
}>();

const search = ref('');
const content = ref(props.note.content);
const title = ref(props.note.title);
const selectedTagIds = ref<number[]>((props.note.tags || []).map((t) => t.id));
const saveStatus = ref<'saved' | 'saving' | 'unsaved'>('saved');
const fileInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    title: '',
    content: '',
    tag_ids: [] as number[],
});

const uploadForm = useForm({
    file: null as File | null,
});

const filteredNotes = computed(() => {
    const term = search.value.toLowerCase();
    return props.notes.filter((n) =>
        n.title.toLowerCase().includes(term) ||
        n.content.toLowerCase().includes(term),
    );
});

const submit = () => {
    saveStatus.value = 'saving';
    form.title = title.value;
    form.content = content.value;
    form.tag_ids = selectedTagIds.value;
    form.put(route('projects.notes.update', [props.project.id, props.note.id]), {
        onSuccess: () => {
            saveStatus.value = 'saved';
        },
        onError: () => {
            saveStatus.value = 'unsaved';
        },
    });
};

const autosave = () => submit();

const deleteAttachment = (id: number) => {
    if (!confirm('Удалить вложение?')) return;
    useForm({}).delete(route('attachments.destroy', id), { preserveScroll: true });
};

const upload = (file: File) => {
    uploadForm.file = file;
    uploadForm.post(route('attachments.store', [props.project.id, props.note.id]), {
        preserveScroll: true,
        onSuccess: () => {
            uploadForm.reset();
            if (fileInput.value) fileInput.value.value = '';
        },
    });
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
    for (const item of items) {
        if (item.type.startsWith('image/')) {
            const file = item.getAsFile();
            if (file) upload(file);
        }
    }
};
</script>

<template>
    <Head :title="`Редактирование · ${note.title}`" />

    <AuthenticatedLayout>
        <div class="flex h-[calc(100vh-8rem)] flex-col gap-4 lg:flex-row">
            <aside class="flex w-full flex-col overflow-hidden rounded-xl border border-[var(--border)] bg-[var(--surface)] lg:w-80">
                <div class="flex items-center justify-between border-b border-[var(--border)] p-3">
                    <Link :href="route('projects.show', project.id)" class="text-sm text-[var(--muted)] hover:text-[var(--text)]">
                        <ArrowLeft class="inline h-4 w-4" /> {{ project.name }}
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
                            v-for="n in filteredNotes"
                            :key="n.id"
                            :href="route('projects.notes.show', [project.id, n.id])"
                            class="flex items-start gap-2 rounded-lg p-2 transition hover:bg-[var(--surface-elevated)]"
                            :class="note.id === n.id ? 'bg-[var(--surface-elevated)]' : ''"
                        >
                            <Pin v-if="n.is_pinned" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-500" />
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-[var(--text)]">{{ n.title }}</p>
                                <p class="text-xs text-[var(--muted)]">{{ new Date(n.updated_at).toLocaleDateString('ru-RU') }}</p>
                            </div>
                        </Link>
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
