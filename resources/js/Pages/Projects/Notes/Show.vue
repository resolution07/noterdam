<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Edit,
    Trash2,
    Pin,
    Search,
    Share2,
    FileText,
    Plus,
} from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Button from '@/Components/Button.vue';
import MarkdownPreview from '@/Components/MarkdownPreview.vue';
import AttachmentList from '@/Components/AttachmentList.vue';
import ShareModal from '@/Components/ShareModal.vue';
import type { Project, ProjectMember, Note, Tag } from '@/types';

const props = defineProps<{
    project: Project;
    notes: Note[];
    currentNote: Note;
    tags: Tag[];
}>();

const page = usePage();
const showShare = ref(false);
const search = ref('');

const members = computed(() => props.project.members as ProjectMember[] || []);
const currentTags = computed(() => props.currentNote.tags || []);
const userRole = computed(() => {
    const userId = page.props.auth.user?.id;
    if (props.project.owner_id === userId) return 'owner';
    return members.value.find((m) => m.id === userId)?.pivot.role || null;
});
const canEdit = computed(() => userRole.value === 'owner' || userRole.value === 'editor');

const filteredNotes = computed(() => {
    const term = search.value.toLowerCase();
    return props.notes.filter((n) =>
        n.title.toLowerCase().includes(term) ||
        n.content.toLowerCase().includes(term),
    );
});

const deleteNote = () => {
    if (!confirm('Удалить заметку?')) return;
    useForm({}).delete(route('projects.notes.destroy', [props.project.id, props.currentNote.id]));
};

const deleteAttachment = (id: number) => {
    if (!confirm('Удалить вложение?')) return;
    useForm({}).delete(route('attachments.destroy', id), { preserveScroll: true });
};
</script>

<template>
    <Head :title="currentNote.title" />

    <AuthenticatedLayout>
        <div class="flex h-[calc(100vh-8rem)] flex-col gap-4 lg:flex-row">
            <aside class="flex w-full flex-col gap-4 lg:w-80">
                <div class="rounded-xl border border-[var(--border)] bg-[var(--surface)] p-4">
                    <Link :href="route('projects.show', project.id)" class="mb-2 inline-flex items-center gap-1 text-xs text-[var(--muted)] hover:text-[var(--text)]">
                        <ArrowLeft class="h-3.5 w-3.5" /> {{ project.name }}
                    </Link>
                    <h1 class="text-lg font-semibold text-[var(--text)]">{{ project.name }}</h1>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <Button variant="secondary" size="sm" @click="showShare = true">
                            <Share2 class="h-4 w-4" /> Поделиться
                        </Button>
                    </div>
                </div>

                <div class="flex flex-1 flex-col overflow-hidden rounded-xl border border-[var(--border)] bg-[var(--surface)]">
                    <div class="flex items-center justify-between border-b border-[var(--border)] p-3">
                        <h2 class="text-sm font-medium text-[var(--text)]">Заметки</h2>
                        <Link :href="route('projects.notes.index', project.id)">
                            <Button variant="ghost" size="icon" aria-label="Новая заметка">
                                <Plus class="h-4 w-4" />
                            </Button>
                        </Link>
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
                                :class="currentNote.id === note.id ? 'bg-[var(--surface-elevated)]' : ''"
                            >
                                <Pin v-if="note.is_pinned" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-500" />
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-[var(--text)]">{{ note.title }}</p>
                                    <p class="text-xs text-[var(--muted)]">{{ new Date(note.updated_at).toLocaleDateString('ru-RU') }}</p>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="flex flex-1 flex-col overflow-hidden rounded-xl border border-[var(--border)] bg-[var(--surface)]">
                <div class="flex items-center justify-between border-b border-[var(--border)] px-5 py-3">
                    <div class="flex items-center gap-2">
                        <Pin v-if="currentNote.is_pinned" class="h-4 w-4 text-emerald-500" />
                        <h1 class="text-lg font-semibold text-[var(--text)]">{{ currentNote.title }}</h1>
                    </div>
                    <div class="flex items-center gap-2">
                        <Link
                            v-if="canEdit"
                            :href="route('projects.notes.edit', [project.id, currentNote.id])"
                        >
                            <Button variant="secondary" size="sm">
                                <Edit class="h-4 w-4" /> Редактировать
                            </Button>
                        </Link>
                        <Button
                            v-if="canEdit"
                            variant="ghost"
                            size="sm"
                            class="text-rose-500 hover:bg-rose-500/10"
                            @click="deleteNote"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <div class="flex-1 overflow-auto p-6">
                    <div class="mb-4 flex flex-wrap gap-2">
                        <span
                            v-for="tag in currentTags"
                            :key="tag.id"
                            class="rounded-full px-2.5 py-1 text-xs"
                            :style="{ backgroundColor: tag.color ? tag.color + '20' : '#34D39920', color: tag.color || '#34D399' }"
                        >
                            {{ tag.name }}
                        </span>
                    </div>

                    <MarkdownPreview :html="currentNote.content_html || ''" />

                    <div class="mt-8 border-t border-[var(--border)] pt-6">
                        <AttachmentList
                            :attachments="currentNote.attachments || []"
                            :can-delete="canEdit"
                            @delete="deleteAttachment"
                        />
                    </div>
                </div>
            </main>
        </div>

        <ShareModal :show="showShare" :project="project" :members="members" @close="showShare = false" />
    </AuthenticatedLayout>
</template>
