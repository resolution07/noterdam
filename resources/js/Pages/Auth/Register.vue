<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/Guest.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import Button from '@/Components/Button.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'));
};
</script>

<template>
    <Head title="Регистрация" />

    <GuestLayout>
        <div class="space-y-6">
            <div class="text-center">
                <h1 class="text-2xl font-semibold text-[var(--text)]">Регистрация</h1>
                <p class="mt-2 text-sm text-[var(--muted)]">Создайте аккаунт в Noterdam</p>
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <Label for="name">Имя</Label>
                    <Input id="name" v-model="form.name" required :error="form.errors.name" />
                </div>

                <div>
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="form.email" type="email" required :error="form.errors.email" />
                </div>

                <div>
                    <Label for="password">Пароль</Label>
                    <Input id="password" v-model="form.password" type="password" required :error="form.errors.password" />
                </div>

                <div>
                    <Label for="password_confirmation">Подтверждение пароля</Label>
                    <Input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        :error="form.errors.password_confirmation"
                    />
                </div>

                <Button type="submit" class="w-full" :disabled="form.processing">
                    Зарегистрироваться
                </Button>
            </form>

            <p class="text-center text-sm text-[var(--muted)]">
                Уже есть аккаунт?
                <Link :href="route('login')" class="text-emerald-500 hover:text-emerald-400 hover:underline">
                    Войти
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>
