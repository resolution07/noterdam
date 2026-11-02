<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { Trash2, Copy, Plus } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';
import client from '@/api/client';
import type { Invite } from '@/types';

const invites = ref<Invite[]>([]);
const showModal = ref(false);
const form = reactive({
    email: '',
    expires_at: '',
});
const errors = reactive<Record<string, string>>({});
const processing = ref(false);

const fetchInvites = async () => {
    try {
        const response = await client.get<{ data: Invite[] }>('/admin/invites');
        invites.value = response.data.data;
    } catch (error: any) {
        toast.error(error.response?.data?.message || 'Ошибка загрузки инвайтов');
    }
};

const submit = async () => {
    processing.value = true;
    Object.keys(errors).forEach((key) => delete errors[key]);
    try {
        const response = await client.post<{ data: Invite }>('/admin/invites', form);
        invites.value.unshift(response.data.data);
        form.email = '';
        form.expires_at = '';
        showModal.value = false;
        toast.success('Инвайт создан');
    } catch (error: any) {
        if (error.response?.data?.errors) {
            Object.assign(errors, error.response.data.errors);
        } else {
            toast.error(error.response?.data?.message || 'Ошибка создания инвайта');
        }
    } finally {
        processing.value = false;
    }
};

const deleteInvite = async (id: number) => {
    if (!confirm('Удалить инвайт?')) return;
    try {
        await client.delete(`/admin/invites/${id}`);
        invites.value = invites.value.filter((i) => i.id !== id);
        toast.success('Инвайт удалён');
    } catch (error: any) {
        toast.error(error.response?.data?.message || 'Ошибка удаления');
    }
};

const copyLink = (code: string) => {
    const url = `${window.location.origin}/register?code=${encodeURIComponent(code)}`;
    navigator.clipboard.writeText(url);
    toast.success('Ссылка скопирована');
};

onMounted(fetchInvites);
</script>

<template>
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
                    <Input id="invite-email" v-model="form.email" type="email" :error="errors.email" />
                </div>
                <div>
                    <Label for="invite-expires">Истекает (необязательно)</Label>
                    <Input id="invite-expires" v-model="form.expires_at" type="datetime-local" :error="errors.expires_at" />
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <Button type="button" variant="secondary" @click="showModal = false">Отмена</Button>
                    <Button type="submit" :disabled="processing">Создать</Button>
                </div>
            </form>
        </Modal>
    </AuthenticatedLayout>
</template>
