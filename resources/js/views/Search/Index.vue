<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { useRoute, useRouter, RouterLink } from 'vue-router';
import { Search, FileText, ArrowRight } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Input from '@/Components/Input.vue';
import client from '@/api/client';
import type { Note } from '@/types';

const route = useRoute();
const router = useRouter();

const query = ref((route.query.q as string) || '');
const notes = ref<Note[]>([]);
const loading = ref(false);

const search = async () => {
    if (!query.value.trim()) {
        notes.value = [];
        return;
    }
    loading.value = true;
    try {
        const response = await client.get<{ data: Note[] }>('/search', {
            params: { q: query.value },
        });
        notes.value = response.data.data;
    } catch (error: any) {
        toast.error(error.response?.data?.message || 'Ошибка поиска');
    } finally {
        loading.value = false;
    }
};

watch(query, () => {
    router.replace({ query: query.value ? { q: query.value } : {} });
    search();
});

function snippet(note: Note): string {
    const text = (note.content || '').replace(/[#*`\[\]!>-]/g, ' ');
    const index = text.toLowerCase().indexOf(query.value.toLowerCase());
    if (index === -1) return text.slice(0, 160);
    const start = Math.max(0, index - 60);
    const end = Math.min(text.length, index + query.value.length + 100);
    return (start > 0 ? '…' : '') + text.slice(start, end) + (end < text.length ? '…' : '');
}

onMounted(() => {
    if (query.value) search();
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="mx-auto max-w-3xl space-y-6">
            <h1 class="text-2xl font-semibold text-[var(--text)]">Глобальный поиск</h1>

            <div class="relative">
                <Search class="absolute left-3 top-2.5 h-5 w-5 text-[var(--muted)]" />
                <Input v-model="query" type="text" class="pl-10" placeholder="Поиск по заметкам…" />
            </div>

            <div v-if="loading" class="text-center text-sm text-[var(--muted)]">
                Поиск…
            </div>

            <div v-else-if="!query" class="text-center text-sm text-[var(--muted)]">
                Введите запрос для поиска
            </div>

            <div v-else-if="notes.length === 0" class="text-center text-sm text-[var(--muted)]">
                По запросу «{{ query }}» ничего не найдено
            </div>

            <div v-else class="space-y-3">
                <RouterLink
                    v-for="note in notes"
                    :key="note.id"
                    :to="`/projects/${note.project_id}/notes/${note.id}`"
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
                </RouterLink>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
