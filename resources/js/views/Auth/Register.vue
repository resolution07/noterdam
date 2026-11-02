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
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});
const errors = reactive<Record<string, string>>({});
const processing = ref(false);

const submit = async () => {
    processing.value = true;
    Object.keys(errors).forEach((key) => delete errors[key]);

    const success = await auth.register(
        form.name,
        form.email,
        form.password,
        form.password_confirmation,
    );
    if (!success) {
        processing.value = false;
    }
};
</script>

<template>
    <GuestLayout>
        <div class="space-y-6">
            <div class="text-center">
                <h1 class="text-2xl font-semibold text-[var(--text)]">Регистрация</h1>
                <p class="mt-2 text-sm text-[var(--muted)]">Создайте аккаунт в Noterdam</p>
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <Label for="name">Имя</Label>
                    <Input id="name" v-model="form.name" required :error="errors.name" />
                </div>

                <div>
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="form.email" type="email" required :error="errors.email" />
                </div>

                <div>
                    <Label for="password">Пароль</Label>
                    <Input id="password" v-model="form.password" type="password" required :error="errors.password" />
                </div>

                <div>
                    <Label for="password_confirmation">Подтверждение пароля</Label>
                    <Input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        :error="errors.password_confirmation"
                    />
                </div>

                <Button type="submit" class="w-full" :disabled="processing">
                    Зарегистрироваться
                </Button>
            </form>

            <p class="text-center text-sm text-[var(--muted)]">
                Уже есть аккаунт?
                <RouterLink to="/login" class="text-emerald-500 hover:text-emerald-400 hover:underline">
                    Войти
                </RouterLink>
            </p>
        </div>
    </GuestLayout>
</template>
