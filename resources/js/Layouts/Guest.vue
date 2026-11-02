<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-[var(--background)] px-4 py-12 sm:px-6 lg:px-8">
        <div class="absolute right-4 top-4">
            <ThemeToggle />
        </div>

        <div class="w-full max-w-md space-y-6">
            <div class="flex justify-center">
                <ApplicationLogo class="scale-125" />
            </div>

            <div class="rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-8 shadow-xl">
                <slot />
            </div>
        </div>
    </div>
</template>
