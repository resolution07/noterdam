<script setup lang="ts">
import { Sun, Moon, Monitor } from 'lucide-vue-next';
import { useTheme } from '@/Composables/useTheme';
import Dropdown from './Dropdown.vue';

const { theme, set } = useTheme();
</script>

<template>
    <Dropdown align="right">
        <template #trigger>
            <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[var(--border)] text-[var(--muted)] transition hover:bg-[var(--surface)] hover:text-[var(--text)]"
                aria-label="Тема"
            >
                <Sun v-if="theme === 'light'" class="h-4 w-4" />
                <Moon v-else-if="theme === 'dark'" class="h-4 w-4" />
                <Monitor v-else class="h-4 w-4" />
            </button>
        </template>
        <template #default="{ close }">
            <button
                class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-[var(--text)] hover:bg-[var(--surface)]"
                :class="theme === 'light' && 'bg-[var(--surface)]'"
                @click="set('light'); close()"
            >
                <Sun class="h-4 w-4" /> Светлая
            </button>
            <button
                class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-[var(--text)] hover:bg-[var(--surface)]"
                :class="theme === 'dark' && 'bg-[var(--surface)]'"
                @click="set('dark'); close()"
            >
                <Moon class="h-4 w-4" /> Тёмная
            </button>
            <button
                class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-[var(--text)] hover:bg-[var(--surface)]"
                :class="theme === 'system' && 'bg-[var(--surface)]'"
                @click="set('system'); close()"
            >
                <Monitor class="h-4 w-4" /> Системная
            </button>
        </template>
    </Dropdown>
</template>
