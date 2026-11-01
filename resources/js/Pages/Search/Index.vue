<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, FileText, ArrowRight } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Input from '@/Components/Input.vue';
import type { Note } from '@/types';

const props = defineProps<{
    q: string;
    notes: Note[];
}>();

const query = ref(props.q || '');

watch(query, (value) => {
    router.get(
        route('search'),
        { q: value },
        { preserveState: true, preserveScroll: true },
    );
});

function snippet(note: Note): string {
    const text = (note.content || '').replace(/[#*`\[\]!>-]/g, ' ');
    const index = text.toLowerCase().indexOf(query.value.toLowerCase());
    if (index === -1) return text.slice(0, 160);
    const start = Math.max(0, index - 60);
    const end = Math.min(text.length, index + query.value.length + 100);
    return (start > 0 ? '…' : '') + text.slice(start, end) + (end < text.length ? '…' : '');
}
</script>

<template>
    <Head title="Поиск" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-3xl space-y-6">
            <h1 class="text-2xl font-semibold text-[var(--text)]">Глобальный поиск</h1>

            <div class="relative">
                <Search class="absolute left-3 top-2.5 h-5 w-5 text-[var(--muted)]" />
                <Input v-model="query" type="text" class="pl-10" placeholder="Поиск по заметкам…" />
            </div>

            <div v-if="!query" class="text-center text-sm text-[var(--muted)]">
                Введите запрос для поиска
            </div>

            <div v-else-if="notes.length === 0" class="text-center text-sm text-[var(--muted)]">
                По запросу «{{ query }}» ничего не найдено
            </div>

            <div v-else class="space-y-3">
                <Link
                    v-for="note in notes"
                    :key="note.id"
                    :href="route('projects.notes.show', [note.project_id, note.id])"
                    class="group block rounded-xl border border-[var(--border)] bg-[var(--surface)] p-5 transition hover:border-emerald-500/30"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <FileText class="h-4 w-4 text-[var(--muted)]" />
                            <span class="font-medium text-[var(--text)]">{{ note.title }}</span>
                        </div>
                        <ArrowRight class="h-4 w-4 text-[var(--muted)] opacity-0 transition group-hover:opacity-100" />
                    </div>
                    <p class="mt-2 text-sm text-[var(--muted)]">{{ snippet(note) }}</p>
                    <p class="mt-2 text-xs text-[var(--muted)]">
                        Проект #{{ note.project_id }} · {{ note.author?.name || '—' }} · {{ new Date(note.updated_at).toLocaleDateString('ru-RU') }}
                    </p>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
