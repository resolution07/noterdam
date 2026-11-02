<script setup lang="ts">
import { ref, computed } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import {
    Search,
    Settings,
    LogOut,
    Menu,
    X,
    Users,
} from 'lucide-vue-next';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import NavLink from '@/Components/NavLink.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import Dropdown from '@/Components/Dropdown.vue';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const route = useRoute();
const user = computed(() => auth.user);
const mobileOpen = ref(false);

const initials = computed(() => {
    return user.value?.name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();
});

const logout = () => {
    auth.logout();
};
</script>

<template>
    <div class="min-h-screen bg-[var(--background)] text-[var(--text)]">
        <header class="sticky top-0 z-30 border-b border-[var(--border)] bg-[var(--surface)]/80 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4">
                    <RouterLink to="/">
                        <ApplicationLogo />
                    </RouterLink>
                    <nav class="hidden items-center gap-1 md:flex">
                        <NavLink to="/projects" :active="route.path.startsWith('/projects')">
                            Проекты
                        </NavLink>
                        <NavLink to="/search" :active="route.path === '/search'">
                            <Search class="h-4 w-4" /> Поиск
                        </NavLink>
                    </nav>
                </div>

                <div class="flex items-center gap-2">
                    <ThemeToggle />
                    <RouterLink
                        to="/search"
                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-[var(--border)] text-[var(--muted)] hover:bg-[var(--surface)] hover:text-[var(--text)] md:hidden"
                        aria-label="Поиск"
                    >
                        <Search class="h-4 w-4" />
                    </RouterLink>
                    <Dropdown align="right">
                        <template #trigger>
                            <button
                                type="button"
                                class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/10 text-sm font-medium text-emerald-500"
                            >
                                {{ initials }}
                            </button>
                        </template>
                        <template #default="{ close }">
                            <div class="border-b border-[var(--border)] px-4 py-2">
                                <p class="text-sm font-medium text-[var(--text)]">{{ user?.name }}</p>
                                <p class="text-xs text-[var(--muted)]">{{ user?.email }}</p>
                            </div>
                            <RouterLink
                                to="/settings/profile"
                                class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-[var(--text)] hover:bg-[var(--surface)]"
                                @click="close"
                            >
                                <Settings class="h-4 w-4" /> Настройки
                            </RouterLink>
                            <RouterLink
                                v-if="user?.is_admin"
                                to="/admin/invites"
                                class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-[var(--text)] hover:bg-[var(--surface)]"
                                @click="close"
                            >
                                <Users class="h-4 w-4" /> Инвайты
                            </RouterLink>
                            <button
                                type="button"
                                class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-rose-500 hover:bg-[var(--surface)]"
                                @click="logout(); close();"
                            >
                                <LogOut class="h-4 w-4" /> Выйти
                            </button>
                        </template>
                    </Dropdown>
                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-[var(--border)] text-[var(--muted)] hover:bg-[var(--surface)] hover:text-[var(--text)] md:hidden"
                        aria-label="Меню"
                        @click="mobileOpen = true"
                    >
                        <Menu class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <slot />
        </main>

        <Transition
            enter-active-class="transition-opacity duration-150"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileOpen"
                class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm md:hidden"
                @click="mobileOpen = false"
            />
        </Transition>

        <Transition
            enter-active-class="transition-transform duration-200 ease-out"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
        >
            <aside
                v-if="mobileOpen"
                class="fixed right-0 top-0 z-50 h-full w-72 border-l border-[var(--border)] bg-[var(--surface)] p-4 shadow-xl md:hidden"
            >
                <div class="mb-4 flex items-center justify-between">
                    <span class="text-sm font-medium text-[var(--text)]">Меню</span>
                    <button
                        type="button"
                        class="rounded p-1 text-[var(--muted)] hover:bg-[var(--surface-elevated)]"
                        @click="mobileOpen = false"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>
                <nav class="space-y-1">
                    <NavLink to="/" :active="route.path === '/'">Главная</NavLink>
                    <NavLink to="/projects" :active="route.path.startsWith('/projects')">Проекты</NavLink>
                    <NavLink to="/search" :active="route.path === '/search'">Поиск</NavLink>
                    <NavLink to="/settings/profile" :active="route.path.startsWith('/settings')">Настройки</NavLink>
                    <NavLink
                        v-if="user?.is_admin"
                        to="/admin/invites"
                        :active="route.path.startsWith('/admin')"
                    >
                        Инвайты
                    </NavLink>
                </nav>
            </aside>
        </Transition>
    </div>
</template>
