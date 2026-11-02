<script setup lang="ts">
import { reactive, ref } from 'vue';
import { RouterLink } from 'vue-router';
import GuestLayout from '@/Layouts/Guest.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import Button from '@/Components/Button.vue';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();

const form = reactive({
    email: '',
    password: '',
    remember: false,
});
const errors = reactive<Record<string, string>>({});
const processing = ref(false);

const submit = async () => {
    processing.value = true;
    Object.keys(errors).forEach((key) => delete errors[key]);

    const success = await auth.login(form.email, form.password);
    if (!success) {
        processing.value = false;
    }
};
</script>

<template>
    <GuestLayout>
        <div class="space-y-6">
            <div class="text-center">
                <h1 class="text-2xl font-semibold text-[var(--text)]">Вход в Noterdam</h1>
                <p class="mt-2 text-sm text-[var(--muted)]">Введите email и пароль</p>
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="form.email" type="email" required :error="errors.email" />
                </div>

                <div>
                    <Label for="password">Пароль</Label>
                    <Input id="password" v-model="form.password" type="password" required :error="errors.password" />
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

                <Button type="submit" class="w-full" :disabled="processing">
                    Войти
                </Button>
            </form>

            <p class="text-center text-sm text-[var(--muted)]">
                Нет аккаунта?
                <RouterLink to="/register" class="text-emerald-500 hover:text-emerald-400 hover:underline">
                    Зарегистрироваться
                </RouterLink>
            </p>
        </div>
    </GuestLayout>
</template>
