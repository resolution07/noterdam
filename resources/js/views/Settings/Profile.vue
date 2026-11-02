<script setup lang="ts">
import { reactive, ref, onMounted } from 'vue';
import { toast } from 'vue-sonner';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import Button from '@/Components/Button.vue';
import NavLink from '@/Components/NavLink.vue';
import client from '@/api/client';
import { useAuthStore } from '@/stores/auth';
import type { User } from '@/types';

const auth = useAuthStore();

const form = reactive({
    name: '',
    email: '',
});
const errors = reactive<Record<string, string>>({});
const processing = ref(false);
const loading = ref(false);

const fetchUser = async () => {
    loading.value = true;
    try {
        const response = await client.get<{ data: User }>('/user/profile');
        form.name = response.data.data.name;
        form.email = response.data.data.email;
    } catch (error: any) {
        toast.error(error.response?.data?.message || 'Ошибка загрузки профиля');
    } finally {
        loading.value = false;
    }
};

const submit = async () => {
    processing.value = true;
    Object.keys(errors).forEach((key) => delete errors[key]);
    try {
        const response = await client.put<{ data: User }>('/user/profile', form);
        if (auth.user) {
            auth.user.name = response.data.data.name;
            auth.user.email = response.data.data.email;
        }
        toast.success('Профиль обновлён');
    } catch (error: any) {
        if (error.response?.data?.errors) {
            Object.assign(errors, error.response.data.errors);
        } else {
            toast.error(error.response?.data?.message || 'Ошибка обновления профиля');
        }
    } finally {
        processing.value = false;
    }
};

onMounted(fetchUser);
</script>

<template>
    <AuthenticatedLayout>
        <div class="mx-auto max-w-2xl space-y-6">
            <h1 class="text-2xl font-semibold text-[var(--text)]">Настройки</h1>

            <div class="flex gap-2 border-b border-[var(--border)] pb-1">
                <NavLink to="/settings/profile" :active="true">
                    Профиль
                </NavLink>
                <NavLink to="/settings/password" :active="false">
                    Пароль
                </NavLink>
                <NavLink to="/settings/tokens" :active="false">
                    Токены
                </NavLink>
            </div>

            <div v-if="loading" class="text-sm text-[var(--muted)]">Загрузка…</div>
            <form v-else class="space-y-4 rounded-xl border border-[var(--border)] bg-[var(--surface)] p-6" @submit.prevent="submit">
                <div>
                    <Label for="name">Имя</Label>
                    <Input id="name" v-model="form.name" required :error="errors.name" />
                </div>
                <div>
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="form.email" type="email" required :error="errors.email" />
                </div>
                <div class="flex justify-end">
                    <Button type="submit" :disabled="processing">Сохранить</Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
