<script setup lang="ts">
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { useRoute, useRouter, RouterLink } from 'vue-router';
import {
    Folder,
    Plus,
    Archive,
    Clock,
    FileText,
} from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Modal from '@/Components/Modal.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import Button from '@/Components/Button.vue';
import InteractiveCat from '@/Components/InteractiveCat.vue';
import client from '@/api/client';
import type { Project } from '@/types';

const route = useRoute();
const router = useRouter();

const projects = ref<Project[]>([]);
const filter = computed(() => {
    const f = route.query.filter;
    return f === 'archived' ? 'archived' : 'active';
});

const showModal = ref(false);
const form = reactive({
    name: '',
    description: '',
    color: '#34D399',
});
const formErrors = reactive<Record<string, string>>({});
const processing = ref(false);
const loading = ref(false);

const colors = ['#34D399', '#60A5FA', '#FBBF24', '#F87171', '#A78BFA', '#22D3EE'];

const filteredProjects = computed(() =>
    projects.value.filter((p) =>
        filter.value === 'archived' ? p.is_archived : !p.is_archived,
    ),
);

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

const setFilter = (value: 'active' | 'archived') => {
    router.push({ query: { filter: value } });
};

const submit = async () => {
    processing.value = true;
    Object.keys(formErrors).forEach((key) => delete formErrors[key]);
    try {
        const response = await client.post<{ data: Project }>('/projects', form);
        projects.value.unshift(response.data.data);
        form.name = '';
        form.description = '';
        form.color = '#34D399';
        showModal.value = false;
        toast.success('Проект создан');
    } catch (error: any) {
        if (error.response?.data?.errors) {
            Object.assign(formErrors, error.response.data.errors);
        } else {
            toast.error(error.response?.data?.message || 'Ошибка создания проекта');
        }
    } finally {
        processing.value = false;
    }
};

const archive = async (project: Project) => {
    try {
        await client.post(`/projects/${project.id}/archive`);
        project.is_archived = !project.is_archived;
        toast.success(project.is_archived ? 'Проект архивирован' : 'Проект разархивирован');
    } catch (error: any) {
        toast.error(error.response?.data?.message || 'Ошибка архивации');
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
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-[var(--text)]">Проекты</h1>
                    <p class="text-sm text-[var(--muted)]">Ваши проекты и участия</p>
                </div>
                <Button @click="showModal = true">
                    <Plus class="h-4 w-4" /> Новый проект
                </Button>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    :class="[
                        'rounded-lg px-3 py-1.5 text-sm font-medium',
                        filter === 'active' ? 'bg-[var(--surface-elevated)] text-[var(--text)]' : 'text-[var(--muted)] hover:bg-[var(--surface)]',
                    ]"
                    @click="setFilter('active')"
                >
                    Активные
                </button>
                <button
                    type="button"
                    :class="[
                        'rounded-lg px-3 py-1.5 text-sm font-medium',
                        filter === 'archived' ? 'bg-[var(--surface-elevated)] text-[var(--text)]' : 'text-[var(--muted)] hover:bg-[var(--surface)]',
                    ]"
                    @click="setFilter('archived')"
                >
                    Архив
                </button>
            </div>

            <div
                v-if="filteredProjects.length === 0"
                class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-[var(--border)] bg-[var(--surface)] py-16 text-center"
            >
                <Folder class="h-12 w-12 text-[var(--muted)]" />
                <p class="mt-4 text-[var(--text)]">Проектов пока нет</p>
                <p class="text-sm text-[var(--muted)]">Создайте первый проект, чтобы начать</p>
            </div>

            <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <RouterLink
                    v-for="project in filteredProjects"
                    :key="project.id"
                    :to="`/projects/${project.id}`"
                    class="group relative rounded-xl border border-[var(--border)] bg-[var(--surface)] p-5 transition hover:border-emerald-500/30 hover:shadow-lg"
                >
                    <div class="mb-3 flex items-start justify-between">
                        <div
                            class="h-3 w-3 rounded-full"
                            :style="{ backgroundColor: project.color || '#34D399' }"
                        />
                        <button
                            v-if="!project.is_archived"
                            type="button"
                            class="rounded p-1 text-[var(--muted)] opacity-0 transition hover:bg-[var(--surface-elevated)] group-hover:opacity-100"
                            @click.prevent="archive(project)"
                        >
                            <Archive class="h-4 w-4" />
                        </button>
                    </div>
                    <h3 class="text-lg font-medium text-[var(--text)]">{{ project.name }}</h3>
                    <p class="mt-1 line-clamp-2 text-sm text-[var(--muted)]">
                        {{ project.description || 'Без описания' }}
                    </p>
                    <div class="mt-4 flex items-center gap-3 text-xs text-[var(--muted)]">
                        <span class="flex items-center gap-1">
                            <FileText class="h-3.5 w-3.5" /> {{ project.notes_count || 0 }}
                        </span>
                        <span class="flex items-center gap-1">
                            <Clock class="h-3.5 w-3.5" /> {{ new Date(project.updated_at).toLocaleDateString('ru-RU') }}
                        </span>
                    </div>
                </RouterLink>
            </div>
        </div>

        <Modal :show="showModal" max-width="md" @close="showModal = false">
            <form class="space-y-4" @submit.prevent="submit">
                <h2 class="text-lg font-semibold text-[var(--text)]">Новый проект</h2>
                <div>
                    <Label for="project-name">Название</Label>
                    <Input id="project-name" v-model="form.name" required :error="formErrors.name" />
                </div>
                <div>
                    <Label for="project-description">Описание</Label>
                    <textarea
                        id="project-description"
                        v-model="form.description"
                        rows="3"
                        class="input resize-none"
                    />
                </div>
                <div>
                    <Label>Цвет</Label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="color in colors"
                            :key="color"
                            type="button"
                            :style="{ backgroundColor: color }"
                            :class="[
                                'h-8 w-8 rounded-full ring-2 ring-offset-2 ring-offset-[var(--surface)]',
                                form.color === color ? 'ring-emerald-500' : 'ring-transparent',
                            ]"
                            @click="form.color = color"
                        />
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <Button type="button" variant="secondary" @click="showModal = false">Отмена</Button>
                    <Button type="submit" :disabled="processing">Создать</Button>
                </div>
            </form>
        </Modal>

        <InteractiveCat />
    </AuthenticatedLayout>
</template>
