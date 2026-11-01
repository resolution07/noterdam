<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/Guest.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import Button from '@/Components/Button.vue';

const props = defineProps<{
    invite: string;
}>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    invite: props.invite,
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
                <p class="mt-2 text-sm text-[var(--muted)]">Только по приглашению</p>
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <input v-model="form.invite" type="hidden" />

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
        </div>
    </GuestLayout>
</template>
