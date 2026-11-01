<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import {
    Folder,
    Plus,
    Archive,
    MoreVertical,
    Search,
    Clock,
    FileText,
} from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Modal from '@/Components/Modal.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import Button from '@/Components/Button.vue';
import InteractiveCat from '@/Components/InteractiveCat.vue';
import type { Project } from '@/types';

const props = defineProps<{
    projects: Project[];
    filter: 'active' | 'archived';
}>();

const showModal = ref(false);
const form = useForm({
    name: '',
    description: '',
    color: '#34D399',
});

const colors = ['#34D399', '#60A5FA', '#FBBF24', '#F87171', '#A78BFA', '#22D3EE'];

const filteredProjects = computed(() =>
    props.projects.filter((p) =>
        props.filter === 'archived' ? p.is_archived : !p.is_archived,
    ),
);

const submit = () => {
    form.post(route('projects.store'), {
        onSuccess: () => {
            form.reset();
            showModal.value = false;
        },
    });
};

const archive = (project: Project) => {
    useForm({}).post(route('projects.archive', project.id));
};
</script>

<template>
    <Head title="Главная" />

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
                <Link
                    :href="route('dashboard', { filter: 'active' })"
                    :class="[
                        'rounded-lg px-3 py-1.5 text-sm font-medium',
                        filter === 'active' ? 'bg-[var(--surface-elevated)] text-[var(--text)]' : 'text-[var(--muted)] hover:bg-[var(--surface)]',
                    ]"
                >
                    Активные
                </Link>
                <Link
                    :href="route('dashboard', { filter: 'archived' })"
                    :class="[
                        'rounded-lg px-3 py-1.5 text-sm font-medium',
                        filter === 'archived' ? 'bg-[var(--surface-elevated)] text-[var(--text)]' : 'text-[var(--muted)] hover:bg-[var(--surface)]',
                    ]"
                >
                    Архив
                </Link>
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
                <Link
                    v-for="project in filteredProjects"
                    :key="project.id"
                    :href="route('projects.show', project.id)"
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
                </Link>
            </div>
        </div>

        <Modal :show="showModal" max-width="md" @close="showModal = false">
            <form class="space-y-4" @submit.prevent="submit">
                <h2 class="text-lg font-semibold text-[var(--text)]">Новый проект</h2>
                <div>
                    <Label for="project-name">Название</Label>
                    <Input id="project-name" v-model="form.name" required :error="form.errors.name" />
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
                    <Button type="submit" :disabled="form.processing">Создать</Button>
                </div>
            </form>
        </Modal>

        <InteractiveCat />
    </AuthenticatedLayout>
</template>
