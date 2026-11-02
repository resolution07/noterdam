<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute, RouterLink } from 'vue-router';
import { Clock, FileText } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Button from '@/Components/Button.vue';
import client from '@/api/client';
import type { Project } from '@/types';

const route = useRoute();

const projects = ref<Project[]>([]);
const filter = computed(() => {
    const f = route.query.filter;
    return f === 'archived' ? 'archived' : 'active';
});
const loading = ref(false);

const fetchProjects = async () => {
    loading.value = true;
    try {
        const response = await client.get<{ data: Project[] }>('/projects', {
            params: { filter: filter.value },
        });
        projects.value = response.data.data;
    } finally {
        loading.value = false;
    }
};

watch(filter, fetchProjects, { immediate: true });
onMounted(() => {
    if (projects.value.length === 0) fetchProjects();
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-[var(--text)]">Все проекты</h1>
                <RouterLink to="/">
                    <Button variant="secondary">Назад</Button>
                </RouterLink>
            </div>

            <div class="flex items-center gap-2">
                <RouterLink
                    to="/projects?filter=active"
                    :class="[
                        'rounded-lg px-3 py-1.5 text-sm font-medium',
                        filter === 'active' ? 'bg-[var(--surface-elevated)] text-[var(--text)]' : 'text-[var(--muted)] hover:bg-[var(--surface)]',
                    ]"
                >
                    Активные
                </RouterLink>
                <RouterLink
                    to="/projects?filter=archived"
                    :class="[
                        'rounded-lg px-3 py-1.5 text-sm font-medium',
                        filter === 'archived' ? 'bg-[var(--surface-elevated)] text-[var(--text)]' : 'text-[var(--muted)] hover:bg-[var(--surface)]',
                    ]"
                >
                    Архив
                </RouterLink>
            </div>

            <div v-if="projects.length === 0" class="text-center text-[var(--muted)]">
                Проекты не найдены.
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <RouterLink
                    v-for="project in projects"
                    :key="project.id"
                    :to="`/projects/${project.id}`"
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
                </RouterLink>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
