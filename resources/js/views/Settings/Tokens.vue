<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue';
import { Key, Trash2, Copy } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import Button from '@/Components/Button.vue';
import NavLink from '@/Components/NavLink.vue';
import client from '@/api/client';
import type { Token } from '@/types';

const tokens = ref<Token[]>([]);
const form = reactive({ name: '' });
const errors = reactive<Record<string, string>>({});
const processing = ref(false);
const justCreated = ref('');

const fetchTokens = async () => {
    try {
        const response = await client.get<{ data: Token[] }>('/user/tokens');
        tokens.value = response.data.data;
    } catch (error: any) {
        toast.error(error.response?.data?.message || 'Ошибка загрузки токенов');
    }
};

const submit = async () => {
    processing.value = true;
    Object.keys(errors).forEach((key) => delete errors[key]);
    try {
        const response = await client.post<{ token: string; data: Token }>('/user/tokens', form);
        tokens.value.unshift(response.data.data);
        justCreated.value = response.data.token;
        form.name = '';
        toast.success('Токен создан');
    } catch (error: any) {
        if (error.response?.data?.errors) {
            Object.assign(errors, error.response.data.errors);
        } else {
            toast.error(error.response?.data?.message || 'Ошибка создания токена');
        }
    } finally {
        processing.value = false;
    }
};

const deleteToken = async (id: number) => {
    if (!confirm('Удалить токен?')) return;
    try {
        await client.delete(`/user/tokens/${id}`);
        tokens.value = tokens.value.filter((t) => t.id !== id);
        toast.success('Токен удалён');
    } catch (error: any) {
        toast.error(error.response?.data?.message || 'Ошибка удаления');
    }
};

const copyToken = () => {
    if (!justCreated.value) return;
    navigator.clipboard.writeText(justCreated.value);
    toast.success('Токен скопирован');
};

onMounted(fetchTokens);
</script>

<template>
    <AuthenticatedLayout>
        <div class="mx-auto max-w-2xl space-y-6">
            <h1 class="text-2xl font-semibold text-[var(--text)]">Настройки</h1>

            <div class="flex gap-2 border-b border-[var(--border)] pb-1">
                <NavLink to="/settings/profile" :active="false">
                    Профиль
                </NavLink>
                <NavLink to="/settings/password" :active="false">
                    Пароль
                </NavLink>
                <NavLink to="/settings/tokens" :active="true">
                    Токены
                </NavLink>
            </div>

            <form class="flex items-end gap-2 rounded-xl border border-[var(--border)] bg-[var(--surface)] p-6" @submit.prevent="submit">
                <div class="flex-1">
                    <Label for="token-name">Название токена</Label>
                    <Input id="token-name" v-model="form.name" required :error="errors.name" />
                </div>
                <Button type="submit" :disabled="processing">
                    <Key class="h-4 w-4" /> Создать
                </Button>
            </form>

            <div v-if="justCreated" class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4">
                <p class="text-sm text-[var(--text)]">Сохраните токен — он больше не будет показан:</p>
                <div class="mt-2 flex items-center gap-2">
                    <code class="flex-1 break-all rounded bg-[var(--surface)] p-2 text-xs">{{ justCreated }}</code>
                    <button type="button" class="rounded p-2 hover:bg-[var(--surface)]" @click="copyToken">
                        <Copy class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <div class="rounded-xl border border-[var(--border)] bg-[var(--surface)] p-6">
                <h2 class="mb-4 text-sm font-medium text-[var(--text)]">Ваши токены</h2>

                <div v-if="tokens.length === 0" class="text-sm text-[var(--muted)]">
                    Токенов пока нет.
                </div>

                <ul v-else class="space-y-2">
                    <li
                        v-for="token in tokens"
                        :key="token.id"
                        class="flex items-center justify-between rounded-lg border border-[var(--border)] bg-[var(--surface-elevated)] p-3"
                    >
                        <div>
                            <p class="text-sm font-medium text-[var(--text)]">{{ token.name }}</p>
                            <p class="text-xs text-[var(--muted)]">
                                {{ token.last_used_at ? `Использован ${new Date(token.last_used_at).toLocaleDateString('ru-RU')}` : 'Не использовался' }}
                            </p>
                        </div>
                        <button
                            type="button"
                            class="rounded p-1.5 text-[var(--muted)] hover:bg-rose-500/10 hover:text-rose-500"
                            aria-label="Удалить"
                            @click="deleteToken(token.id)"
                        >
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
