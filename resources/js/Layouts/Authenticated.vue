<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
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
import { useFlash } from '@/Composables/useFlash';
import { Toaster } from 'vue-sonner';
import type { User } from '@/types';

useFlash();

const page = usePage();
const user = computed(() => page.props.auth.user as User | null);
const mobileOpen = ref(false);

const initials = computed(() => {
    return user.value?.name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();
});
</script>

<template>
    <div class="min-h-screen bg-[var(--background)] text-[var(--text)]">
        <Toaster position="top-right" rich-colors />

        <header class="sticky top-0 z-30 border-b border-[var(--border)] bg-[var(--surface)]/80 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4">
                    <Link :href="route('dashboard')">
                        <ApplicationLogo />
                    </Link>
                    <nav class="hidden items-center gap-1 md:flex">
                        <NavLink :href="route('projects.index')" :active="route().current('projects.*')">
                            Проекты
                        </NavLink>
                        <NavLink :href="route('search')" :active="route().current('search')">
                            <Search class="h-4 w-4" /> Поиск
                        </NavLink>
                    </nav>
                </div>

                <div class="flex items-center gap-2">
                    <ThemeToggle />
                    <Link
                        :href="route('search')"
                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-[var(--border)] text-[var(--muted)] hover:bg-[var(--surface)] hover:text-[var(--text)] md:hidden"
                        aria-label="Поиск"
                    >
                        <Search class="h-4 w-4" />
                    </Link>
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
                            <Link
                                :href="route('settings.profile.edit')"
                                class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-[var(--text)] hover:bg-[var(--surface)]"
                                @click="close"
                            >
                                <Settings class="h-4 w-4" /> Настройки
                            </Link>
                            <Link
                                v-if="user?.is_admin"
                                :href="route('admin.invites.index')"
                                class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-[var(--text)] hover:bg-[var(--surface)]"
                                @click="close"
                            >
                                <Users class="h-4 w-4" /> Инвайты
                            </Link>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-rose-500 hover:bg-[var(--surface)]"
                                @click="close"
                            >
                                <LogOut class="h-4 w-4" /> Выйти
                            </Link>
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
                    <NavLink :href="route('dashboard')" :active="route().current('dashboard')">Главная</NavLink>
                    <NavLink :href="route('projects.index')" :active="route().current('projects.*')">Проекты</NavLink>
                    <NavLink :href="route('search')" :active="route().current('search')">Поиск</NavLink>
                    <NavLink :href="route('settings.profile.edit')" :active="route().current('settings.*')">
                        Настройки
                    </NavLink>
                    <NavLink
                        v-if="user?.is_admin"
                        :href="route('admin.invites.index')"
                        :active="route().current('admin.*')"
                    >
                        Инвайты
                    </NavLink>
                </nav>
            </aside>
        </Transition>
    </div>
</template>
