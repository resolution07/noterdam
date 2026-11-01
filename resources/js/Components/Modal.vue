<script setup lang="ts">
import { computed } from 'vue';
import { X } from 'lucide-vue-next';

const props = defineProps<{
    show: boolean;
    maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl';
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const maxWidthClass = computed(() => {
    return {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth || 'md'];
});

const close = () => emit('close');
</script>

<template>
    <Teleport to="body">
        <Transition leave-active-class="duration-150">
            <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
                <div
                    class="absolute inset-0 bg-black/60 backdrop-blur-sm"
                    @click="close"
                />
                <div
                    :class="[
                        'relative z-10 w-full rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-6 shadow-xl',
                        maxWidthClass,
                    ]"
                >
                    <button
                        type="button"
                        class="absolute right-4 top-4 rounded p-1 text-[var(--muted)] hover:bg-[var(--surface-elevated)] hover:text-[var(--text)]"
                        aria-label="Закрыть"
                        @click="close"
                    >
                        <X class="h-4 w-4" />
                    </button>
                    <slot />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
