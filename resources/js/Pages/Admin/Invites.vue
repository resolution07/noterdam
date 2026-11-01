<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Trash2, Copy, Plus } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';
import type { Invite } from '@/types';

const props = defineProps<{
    invites: Invite[];
}>();

const showModal = ref(false);
const form = useForm({
    email: '',
    expires_at: '',
});

const submit = () => {
    form.post(route('admin.invites.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showModal.value = false;
        },
    });
};

const deleteInvite = (id: number) => {
    if (!confirm('Удалить инвайт?')) return;
    useForm({}).delete(route('admin.invites.destroy', id), { preserveScroll: true });
};

const copyLink = (code: string) => {
    const url = `${window.location.origin}/register/${code}`;
    navigator.clipboard.writeText(url);
};
</script>

<template>
    <Head title="Инвайты" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-3xl space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-[var(--text)]">Инвайты</h1>
                    <p class="text-sm text-[var(--muted)]">Управление приглашениями для регистрации</p>
                </div>
                <Button @click="showModal = true">
                    <Plus class="h-4 w-4" /> Создать
                </Button>
            </div>

            <div class="rounded-xl border border-[var(--border)] bg-[var(--surface)] p-6">
                <div v-if="invites.length === 0" class="text-center text-sm text-[var(--muted)]">
                    Инвайтов пока нет.
                </div>

                <ul v-else class="space-y-2">
                    <li
                        v-for="invite in invites"
                        :key="invite.id"
                        class="flex items-center justify-between rounded-lg border border-[var(--border)] bg-[var(--surface-elevated)] p-4"
                    >
                        <div class="min-w-0">
                            <p class="font-mono text-sm text-[var(--text)]">{{ invite.code }}</p>
                            <p class="text-xs text-[var(--muted)]">
                                {{ invite.email ? `Email: ${invite.email}` : 'Без ограничения email' }}
                                · Создал {{ invite.creator?.name || '—' }}
                                · {{ invite.expires_at ? `истекает ${new Date(invite.expires_at).toLocaleDateString('ru-RU')}` : 'без срока' }}
                                · {{ invite.used_at ? `использован ${new Date(invite.used_at).toLocaleDateString('ru-RU')}` : 'не использован' }}
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            <button
                                type="button"
                                class="rounded p-1.5 text-[var(--muted)] hover:bg-[var(--surface)] hover:text-[var(--text)]"
                                aria-label="Копировать ссылку"
                                @click="copyLink(invite.code)"
                            >
                                <Copy class="h-4 w-4" />
                            </button>
                            <button
                                type="button"
                                class="rounded p-1.5 text-[var(--muted)] hover:bg-rose-500/10 hover:text-rose-500"
                                aria-label="Удалить"
                                @click="deleteInvite(invite.id)"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <Modal :show="showModal" max-width="md" @close="showModal = false">
            <form class="space-y-4" @submit.prevent="submit">
                <h2 class="text-lg font-semibold text-[var(--text)]">Новый инвайт</h2>
                <div>
                    <Label for="invite-email">Email (необязательно)</Label>
                    <Input id="invite-email" v-model="form.email" type="email" :error="form.errors.email" />
                </div>
                <div>
                    <Label for="invite-expires">Истекает (необязательно)</Label>
                    <Input id="invite-expires" v-model="form.expires_at" type="datetime-local" :error="form.errors.expires_at" />
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <Button type="button" variant="secondary" @click="showModal = false">Отмена</Button>
                    <Button type="submit" :disabled="form.processing">Создать</Button>
                </div>
            </form>
        </Modal>
    </AuthenticatedLayout>
</template>
