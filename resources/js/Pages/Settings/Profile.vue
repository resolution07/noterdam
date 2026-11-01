<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import Button from '@/Components/Button.vue';
import NavLink from '@/Components/NavLink.vue';
import type { User } from '@/types';

const page = usePage();
const user = page.props.auth.user as User;

const form = useForm({
    name: user.name,
    email: user.email,
});

const submit = () => {
    form.put(route('settings.profile.update'));
};
</script>

<template>
    <Head title="Профиль" />

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
                    <Label for="name">Имя</Label>
                    <Input id="name" v-model="form.name" required :error="form.errors.name" />
                </div>
                <div>
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="form.email" type="email" required :error="form.errors.email" />
                </div>
                <div class="flex justify-end">
                    <Button type="submit" :disabled="form.processing">Сохранить</Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
