<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { UserPlus, X } from 'lucide-vue-next';
import Modal from './Modal.vue';
import Input from './Input.vue';
import Label from './Label.vue';
import Button from './Button.vue';
import type { Project, ProjectMember, User } from '@/types';

const props = defineProps<{
    show: boolean;
    project: Project;
    members: ProjectMember[];
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const form = useForm({
    email: '',
    role: 'viewer' as 'editor' | 'viewer',
});

const roleLabels: Record<string, string> = {
    owner: 'Владелец',
    editor: 'Редактор',
    viewer: 'Наблюдатель',
};

const submit = () => {
    form.post(route('projects.members.store', props.project.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};

const updateRole = (userId: number, role: string) => {
    useForm({ role }).put(route('projects.members.update', [props.project.id, userId]), {
        preserveScroll: true,
    });
};

const remove = (userId: number) => {
    if (!confirm('Удалить участника из проекта?')) return;
    useForm({}).delete(route('projects.members.destroy', [props.project.id, userId]), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Modal :show="show" max-width="lg" @close="emit('close')">
        <div class="space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-[var(--text)]">Участники проекта</h2>
                <p class="text-sm text-[var(--muted)]">Пригласите пользователя по email.</p>
            </div>

            <form class="flex items-end gap-2" @submit.prevent="submit">
                <div class="flex-1">
                    <Label for="share-email">Email</Label>
                    <Input id="share-email" v-model="form.email" type="email" :error="form.errors.email" />
                </div>
                <div>
                    <Label for="share-role">Роль</Label>
                    <select
                        id="share-role"
                        v-model="form.role"
                        class="input h-10"
                    >
                        <option value="editor">Редактор</option>
                        <option value="viewer">Наблюдатель</option>
                    </select>
                </div>
                <Button type="submit" :disabled="form.processing">
                    <UserPlus class="h-4 w-4" /> Добавить
                </Button>
            </form>

            <ul class="max-h-64 space-y-2 overflow-auto">
                <li
                    v-for="member in members"
                    :key="member.id"
                    class="flex items-center justify-between rounded-lg border border-[var(--border)] bg-[var(--surface)] p-3"
                >
                    <div>
                        <p class="text-sm font-medium text-[var(--text)]">{{ member.name }}</p>
                        <p class="text-xs text-[var(--muted)]">{{ member.email }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-[var(--surface-elevated)] px-2 py-1 text-xs text-[var(--muted)]">
                            {{ roleLabels[member.pivot.role] }}
                        </span>
                        <select
                            v-if="member.pivot.role !== 'owner'"
                            :value="member.pivot.role"
                            class="rounded-lg border border-[var(--border)] bg-[var(--surface-elevated)] px-2 py-1 text-xs text-[var(--text)]"
                            @change="updateRole(member.id, ($event.target as HTMLSelectElement).value)"
                        >
                            <option value="editor">Редактор</option>
                            <option value="viewer">Наблюдатель</option>
                        </select>
                        <button
                            v-if="member.pivot.role !== 'owner'"
                            type="button"
                            class="rounded p-1.5 text-[var(--muted)] hover:bg-rose-500/10 hover:text-rose-500"
                            aria-label="Удалить"
                            @click="remove(member.id)"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </li>
            </ul>
        </div>
    </Modal>
</template>
