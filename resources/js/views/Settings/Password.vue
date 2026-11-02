<script setup lang="ts">
import { reactive, ref } from 'vue';
import { toast } from 'vue-sonner';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import Button from '@/Components/Button.vue';
import NavLink from '@/Components/NavLink.vue';
import client from '@/api/client';

const form = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
});
const errors = reactive<Record<string, string>>({});
const processing = ref(false);

const submit = async () => {
    processing.value = true;
    Object.keys(errors).forEach((key) => delete errors[key]);
    try {
        await client.put('/user/password', form);
        form.current_password = '';
        form.password = '';
        form.password_confirmation = '';
        toast.success('Пароль обновлён');
    } catch (error: any) {
        if (error.response?.data?.errors) {
            Object.assign(errors, error.response.data.errors);
        } else {
            toast.error(error.response?.data?.message || 'Ошибка обновления пароля');
        }
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="mx-auto max-w-2xl space-y-6">
            <h1 class="text-2xl font-semibold text-[var(--text)]">Настройки</h1>

            <div class="flex gap-2 border-b border-[var(--border)] pb-1">
                <NavLink to="/settings/profile" :active="false">
                    Профиль
                </NavLink>
                <NavLink to="/settings/password" :active="true">
                    Пароль
                </NavLink>
                <NavLink to="/settings/tokens" :active="false">
                    Токены
                </NavLink>
            </div>

            <form class="space-y-4 rounded-xl border border-[var(--border)] bg-[var(--surface)] p-6" @submit.prevent="submit">
                <div>
                    <Label for="current_password">Текущий пароль</Label>
                    <Input id="current_password" v-model="form.current_password" type="password" :error="errors.current_password" />
                </div>
                <div>
                    <Label for="password">Новый пароль</Label>
                    <Input id="password" v-model="form.password" type="password" :error="errors.password" />
                </div>
                <div>
                    <Label for="password_confirmation">Подтверждение пароля</Label>
                    <Input id="password_confirmation" v-model="form.password_confirmation" type="password" :error="errors.password_confirmation" />
                </div>
                <div class="flex justify-end">
                    <Button type="submit" :disabled="processing">Обновить пароль</Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
