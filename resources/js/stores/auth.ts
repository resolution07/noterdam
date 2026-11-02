import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import { toast } from 'vue-sonner';
import router from '@/router';
import client, { setToken, removeToken, TOKEN_KEY } from '@/api/client';
import type { User } from '@/types';

export const useAuthStore = defineStore('auth', () => {
    const token = ref<string | null>(localStorage.getItem(TOKEN_KEY));
    const user = ref<User | null>(null);
    const isAuthenticated = computed(() => !!token.value && !!user.value);

    function setAuth(newToken: string, newUser: User): void {
        token.value = newToken;
        user.value = newUser;
        setToken(newToken);
    }

    function clearAuth(): void {
        token.value = null;
        user.value = null;
        removeToken();
    }

    async function fetchUser(): Promise<User | null> {
        if (!token.value) return null;
        try {
            const response = await client.get<{ data: User }>('/user');
            user.value = response.data.data;
            return user.value;
        } catch {
            clearAuth();
            return null;
        }
    }

    async function login(email: string, password: string): Promise<boolean> {
        try {
            const response = await client.post<{ token: string; user: User }>('/login', {
                email,
                password,
            });
            setAuth(response.data.token, response.data.user);
            await router.push('/');
            return true;
        } catch (error: any) {
            const message = error.response?.data?.message || 'Ошибка входа';
            toast.error(message);
            return false;
        }
    }

    async function register(name: string, email: string, password: string, passwordConfirmation: string): Promise<boolean> {
        try {
            const response = await client.post<{ token: string; user: User }>('/register', {
                name,
                email,
                password,
                password_confirmation: passwordConfirmation,
            });
            setAuth(response.data.token, response.data.user);
            await router.push('/');
            return true;
        } catch (error: any) {
            const message = error.response?.data?.message || 'Ошибка регистрации';
            toast.error(message);
            return false;
        }
    }

    async function logout(): Promise<void> {
        try {
            await client.post('/logout');
        } finally {
            clearAuth();
            await router.push('/login');
        }
    }

    return {
        token,
        user,
        isAuthenticated,
        setAuth,
        clearAuth,
        fetchUser,
        login,
        register,
        logout,
    };
});
