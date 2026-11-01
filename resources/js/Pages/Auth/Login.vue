<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/Guest.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import Button from '@/Components/Button.vue';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'));
};
</script>

<template>
    <Head title="Вход" />

    <GuestLayout>
        <div class="space-y-6">
            <div class="text-center">
                <h1 class="text-2xl font-semibold text-[var(--text)]">Вход в Noterdam</h1>
                <p class="mt-2 text-sm text-[var(--muted)]">Введите email и пароль</p>
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="form.email" type="email" required :error="form.errors.email" />
                </div>

                <div>
                    <Label for="password">Пароль</Label>
                    <Input id="password" v-model="form.password" type="password" required :error="form.errors.password" />
                </div>

                <div class="flex items-center gap-2">
                    <input
                        id="remember"
                        v-model="form.remember"
                        type="checkbox"
                        class="h-4 w-4 rounded border-[var(--border)] bg-[var(--surface)] text-emerald-500 focus:ring-emerald-500"
                    />
                    <Label for="remember" class="mb-0">Запомнить меня</Label>
                </div>

                <Button type="submit" class="w-full" :disabled="form.processing">
                    Войти
                </Button>
            </form>

            <p class="text-center text-sm text-[var(--muted)]">
                Нет аккаунта?
                <Link :href="route('register')" class="text-emerald-500 hover:text-emerald-400 hover:underline">
                    Зарегистрироваться
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>
