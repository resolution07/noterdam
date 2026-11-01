<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Key, Trash2, Copy } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import Button from '@/Components/Button.vue';
import NavLink from '@/Components/NavLink.vue';
import type { Token } from '@/types';

const props = defineProps<{
    tokens: Token[];
}>();

const form = useForm({
    name: '',
});

const justCreated = ref('');

const submit = () => {
    form.post(route('settings.tokens.store'), {
        preserveScroll: true,
        onSuccess: () => {
            justCreated.value = '';
            form.reset();
        },
    });
};

const deleteToken = (id: number) => {
    if (!confirm('Удалить токен?')) return;
    useForm({}).delete(route('settings.tokens.destroy', id), { preserveScroll: true });
};
</script>

<template>
    <Head title="Токены" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-2xl space-y-6">
            <h1 class="text-2xl font-semibold text-[var(--text)]">Настройки</h1>

            <div class="flex gap-2 border-b border-[var(--border)] pb-1">
                <NavLink :href="route('settings.profile.edit')" :active="route().current('settings.profile.edit')">
                    Профиль
                </NavLink>
                <NavLink :href="route('settings.password.edit')" :active="route().current('settings.password.edit')">
                    Пароль
                </NavLink>
                <NavLink :href="route('settings.tokens.index')" :active="route().current('settings.tokens.*')">
                    Токены
                </NavLink>
            </div>

            <form class="flex items-end gap-2 rounded-xl border border-[var(--border)] bg-[var(--surface)] p-6" @submit.prevent="submit">
                <div class="flex-1">
                    <Label for="token-name">Название токена</Label>
                    <Input id="token-name" v-model="form.name" required :error="form.errors.name" />
                </div>
                <Button type="submit" :disabled="form.processing">
                    <Key class="h-4 w-4" /> Создать
                </Button>
            </form>

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
