<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps<{
    align?: 'left' | 'right';
}>();

const open = ref(false);
const trigger = ref<HTMLElement | null>(null);
const menu = ref<HTMLElement | null>(null);

const toggle = () => (open.value = !open.value);
const close = () => (open.value = false);

const onClickOutside = (e: MouseEvent) => {
    if (
        trigger.value?.contains(e.target as Node) ||
        menu.value?.contains(e.target as Node)
    ) {
        return;
    }
    close();
};

onMounted(() => document.addEventListener('click', onClickOutside));
onUnmounted(() => document.removeEventListener('click', onClickOutside));
</script>

<template>
    <div class="relative inline-block text-left">
        <div ref="trigger" @click="toggle">
            <slot name="trigger" />
        </div>
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="open"
                ref="menu"
                :class="[
                    'absolute z-50 mt-2 min-w-[10rem] rounded-xl border border-[var(--border)] bg-[var(--surface-elevated)] py-1 shadow-lg',
                    align === 'right' ? 'right-0 origin-top-right' : 'left-0 origin-top-left',
                ]"
            >
                <slot :close="close" />
            </div>
        </Transition>
    </div>
</template>
