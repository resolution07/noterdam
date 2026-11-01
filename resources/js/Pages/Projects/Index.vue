<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Folder, Clock, FileText, Plus } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Button from '@/Components/Button.vue';
import type { Project } from '@/types';

const props = defineProps<{
    projects: Project[];
    filter: 'active' | 'archived';
}>();
</script>

<template>
    <Head title="Проекты" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-[var(--text)]">Все проекты</h1>
                <Link :href="route('dashboard')">
                    <Button variant="secondary">Назад</Button>
                </Link>
            </div>

            <div class="flex items-center gap-2">
                <Link
                    :href="route('projects.index', { filter: 'active' })"
                    :class="[
                        'rounded-lg px-3 py-1.5 text-sm font-medium',
                        filter === 'active' ? 'bg-[var(--surface-elevated)] text-[var(--text)]' : 'text-[var(--muted)] hover:bg-[var(--surface)]',
                    ]"
                >
                    Активные
                </Link>
                <Link
                    :href="route('projects.index', { filter: 'archived' })"
                    :class="[
                        'rounded-lg px-3 py-1.5 text-sm font-medium',
                        filter === 'archived' ? 'bg-[var(--surface-elevated)] text-[var(--text)]' : 'text-[var(--muted)] hover:bg-[var(--surface)]',
                    ]"
                >
                    Архив
                </Link>
            </div>

            <div v-if="projects.length === 0" class="text-center text-[var(--muted)]">
                Проекты не найдены.
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="project in projects"
                    :key="project.id"
                    :href="route('projects.show', project.id)"
                    class="rounded-xl border border-[var(--border)] bg-[var(--surface)] p-5 transition hover:border-emerald-500/30"
                >
                    <div
                        class="mb-3 h-3 w-3 rounded-full"
                        :style="{ backgroundColor: project.color || '#34D399' }"
                    />
                    <h3 class="text-lg font-medium text-[var(--text)]">{{ project.name }}</h3>
                    <p class="mt-1 text-sm text-[var(--muted)]">{{ project.description || 'Без описания' }}</p>
                    <div class="mt-4 flex items-center gap-3 text-xs text-[var(--muted)]">
                        <span class="flex items-center gap-1"><FileText class="h-3.5 w-3.5" /> {{ project.notes_count || 0 }}</span>
                        <span class="flex items-center gap-1"><Clock class="h-3.5 w-3.5" /> {{ new Date(project.updated_at).toLocaleDateString('ru-RU') }}</span>
                    </div>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
