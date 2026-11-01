<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import Button from '@/Components/Button.vue';
import NavLink from '@/Components/NavLink.vue';

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(route('settings.password.update'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Безопасность" />

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

            <form class="space-y-4 rounded-xl border border-[var(--border)] bg-[var(--surface)] p-6" @submit.prevent="submit">
                <div>
                    <Label for="current_password">Текущий пароль</Label>
                    <Input id="current_password" v-model="form.current_password" type="password" :error="form.errors.current_password" />
                </div>
                <div>
                    <Label for="password">Новый пароль</Label>
                    <Input id="password" v-model="form.password" type="password" :error="form.errors.password" />
                </div>
                <div>
                    <Label for="password_confirmation">Подтверждение пароля</Label>
                    <Input id="password_confirmation" v-model="form.password_confirmation" type="password" :error="form.errors.password_confirmation" />
                </div>
                <div class="flex justify-end">
                    <Button type="submit" :disabled="form.processing">Обновить пароль</Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
