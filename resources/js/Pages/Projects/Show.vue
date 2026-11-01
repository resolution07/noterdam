<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    Plus,
    Search,
    Pin,
    FileText,
    Settings,
    Share2,
    ArrowLeft,
} from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Button from '@/Components/Button.vue';
import ShareModal from '@/Components/ShareModal.vue';
import { ref, computed } from 'vue';
import type { Project, ProjectMember, Note, Tag } from '@/types';

const props = defineProps<{
    project: Project;
    notes: Note[];
    currentNote: Note | null;
    tags: Tag[];
}>();

const showShare = ref(false);
const search = ref('');

const members = computed(() => props.project.members as ProjectMember[] || []);

const filteredNotes = computed(() => {
    const term = search.value.toLowerCase();
    return props.notes.filter((n) =>
        n.title.toLowerCase().includes(term) ||
        n.content.toLowerCase().includes(term),
    );
});

const archive = () => {
    useForm({}).post(route('projects.archive', props.project.id));
};

const deleteProject = () => {
    if (!confirm('Удалить проект? Все заметки будут удалены.')) return;
    useForm({}).delete(route('projects.destroy', props.project.id));
};
</script>

<template>
    <Head :title="project.name" />

    <AuthenticatedLayout>
        <div class="flex h-[calc(100vh-8rem)] flex-col gap-4 lg:flex-row">
            <aside class="flex w-full flex-col gap-4 lg:w-80">
                <div class="rounded-xl border border-[var(--border)] bg-[var(--surface)] p-4">
                    <Link :href="route('dashboard')" class="mb-3 inline-flex items-center gap-1 text-xs text-[var(--muted)] hover:text-[var(--text)]">
                        <ArrowLeft class="h-3.5 w-3.5" /> Назад
                    </Link>
                    <div class="flex items-start justify-between">
                        <div>
                            <div
                                class="mb-2 h-3 w-3 rounded-full"
                                :style="{ backgroundColor: project.color || '#34D399' }"
                            />
                            <h1 class="text-lg font-semibold text-[var(--text)]">{{ project.name }}</h1>
                            <p class="text-sm text-[var(--muted)]">{{ project.description || 'Без описания' }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <Button variant="secondary" size="sm" @click="showShare = true">
                            <Share2 class="h-4 w-4" /> Поделиться
                        </Button>
                        <Button variant="ghost" size="sm" @click="archive">
                            {{ project.is_archived ? 'Разархивировать' : 'В архив' }}
                        </Button>
                        <Button variant="ghost" size="sm" @click="deleteProject">
                            Удалить
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
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Поиск по заметкам…"
                                class="input pl-9"
                            />
                        </div>
                    </div>
                    <div class="flex-1 overflow-auto p-3 pt-0">
                        <div v-if="filteredNotes.length === 0" class="py-8 text-center text-sm text-[var(--muted)]">
                            <FileText class="mx-auto mb-2 h-8 w-8" />
                            Заметок пока нет
                        </div>
                        <div v-else class="space-y-1">
                            <Link
                                v-for="note in filteredNotes"
                                :key="note.id"
                                :href="route('projects.notes.show', [project.id, note.id])"
                                class="flex items-start gap-2 rounded-lg p-2 transition hover:bg-[var(--surface-elevated)]"
                                :class="currentNote?.id === note.id ? 'bg-[var(--surface-elevated)]' : ''"
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

            <main class="flex flex-1 items-center justify-center rounded-xl border border-dashed border-[var(--border)] bg-[var(--surface)] p-8 text-center">
                <div>
                    <FileText class="mx-auto mb-4 h-12 w-12 text-[var(--muted)]" />
                    <h2 class="text-lg font-medium text-[var(--text)]">Выберите заметку</h2>
                    <p class="mt-1 text-sm text-[var(--muted)]">Или создайте первую заметку в проекте</p>
                    <Link :href="route('projects.notes.index', project.id)" class="mt-4 inline-block">
                        <Button><Plus class="h-4 w-4" /> Создать заметку</Button>
                    </Link>
                </div>
            </main>
        </div>

        <ShareModal :show="showShare" :project="project" :members="members" @close="showShare = false" />
    </AuthenticatedLayout>
</template>
