<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

type CatState = 'idle' | 'walk' | 'sleep' | 'eat' | 'play' | 'petted';

const SIZE = 80;
const MARGIN = 16;
const WALK_SPEED = 1.8;
const PLAY_SPEED = 3.2;

const catX = ref(120);
const catY = ref(window.innerHeight - SIZE - MARGIN);
const targetX = ref(catX.value);
const targetY = ref(catY.value);
const state = ref<CatState>('idle');
const direction = ref(1);
const message = ref('');
const showFood = ref(false);
const showZzz = ref(false);
const hearts = ref<{ id: number; x: number; y: number }[]>([]);

let mouseX = window.innerWidth / 2;
let mouseY = window.innerHeight / 2;
let rafId: number;
let actionTimer: ReturnType<typeof setTimeout> | null = null;
let heartId = 0;

const random = (min: number, max: number) => Math.random() * (max - min) + min;

const clamp = (value: number, min: number, max: number) => Math.max(min, Math.min(max, value));

const distance = (x1: number, y1: number, x2: number, y2: number) =>
    Math.sqrt((x2 - x1) ** 2 + (y2 - y1) ** 2);

const setMessage = (text: string, duration = 1800) => {
    message.value = text;
    setTimeout(() => {
        if (message.value === text) message.value = '';
    }, duration);
};

const clearActionTimer = () => {
    if (actionTimer) {
        clearTimeout(actionTimer);
        actionTimer = null;
    }
};

const pickRandomSpot = () => {
    const maxX = window.innerWidth - SIZE - MARGIN;
    const maxY = window.innerHeight - SIZE - MARGIN;
    targetX.value = random(MARGIN, maxX);
    targetY.value = random(80, maxY);
};

const pickAction = () => {
    clearActionTimer();

    const distToMouse = distance(catX.value + SIZE / 2, catY.value + SIZE / 2, mouseX, mouseY);

    if (distToMouse < 180) {
        state.value = 'play';
        showFood.value = false;
        showZzz.value = false;
        targetX.value = clamp(mouseX - SIZE / 2, MARGIN, window.innerWidth - SIZE - MARGIN);
        targetY.value = clamp(mouseY - SIZE / 2, 80, window.innerHeight - SIZE - MARGIN);
        actionTimer = setTimeout(pickAction, 600);
        return;
    }

    const roll = Math.random();
    if (roll < 0.25) {
        state.value = 'sleep';
        showZzz.value = true;
        showFood.value = false;
        actionTimer = setTimeout(pickAction, random(4000, 7000));
    } else if (roll < 0.45) {
        state.value = 'eat';
        showFood.value = true;
        showZzz.value = false;
        actionTimer = setTimeout(() => {
            showFood.value = false;
            pickAction();
        }, random(3000, 5000));
    } else {
        state.value = 'walk';
        showFood.value = false;
        showZzz.value = false;
        pickRandomSpot();
    }
};

const pet = () => {
    clearActionTimer();
    state.value = 'petted';
    showFood.value = false;
    showZzz.value = false;
    setMessage('мур ❤️');

    const id = ++heartId;
    hearts.value.push({ id, x: random(10, 50), y: 0 });
    setTimeout(() => {
        hearts.value = hearts.value.filter((h) => h.id !== id);
    }, 1500);

    actionTimer = setTimeout(pickAction, 2000);
};

const updateMouse = (e: MouseEvent) => {
    mouseX = e.clientX;
    mouseY = e.clientY;
};

const loop = () => {
    const dx = targetX.value - catX.value;
    const dy = targetY.value - catY.value;
    const dist = Math.sqrt(dx * dx + dy * dy);

    if (dist > 4 && (state.value === 'walk' || state.value === 'play')) {
        const speed = state.value === 'play' ? PLAY_SPEED : WALK_SPEED;
        catX.value += (dx / dist) * speed;
        catY.value += (dy / dist) * speed;
        direction.value = dx > 0 ? 1 : -1;
    } else if (state.value === 'walk') {
        state.value = 'idle';
        actionTimer = setTimeout(pickAction, random(800, 2000));
    } else if (state.value === 'play') {
        actionTimer = setTimeout(pickAction, 300);
    }

    rafId = requestAnimationFrame(loop);
};

onMounted(() => {
    window.addEventListener('mousemove', updateMouse);
    actionTimer = setTimeout(pickAction, 1000);
    rafId = requestAnimationFrame(loop);
});

onUnmounted(() => {
    window.removeEventListener('mousemove', updateMouse);
    cancelAnimationFrame(rafId);
    clearActionTimer();
});
</script>

<template>
    <div
        class="pointer-events-none fixed z-40"
        :style="{ left: `${catX}px`, top: `${catY}px`, width: `${SIZE}px`, height: `${SIZE}px` }"
    >
        <div
            class="pointer-events-auto relative cursor-pointer transition-transform duration-200"
            :class="{ 'animate-bounce': state === 'play' }"
            @click="pet"
            @mouseenter="pet"
        >
            <svg
                viewBox="0 0 80 80"
                class="h-20 w-20 drop-shadow-lg"
                :style="{ transform: `scaleX(${direction})` }"
                :class="{
                    'cat-idle': state === 'idle',
                    'cat-walk': state === 'walk',
                    'cat-sleep': state === 'sleep',
                    'cat-eat': state === 'eat',
                    'cat-play': state === 'play',
                    'cat-petted': state === 'petted',
                }"
            >
                <!-- Tail -->
                <path
                    class="tail"
                    d="M55 55 Q70 45 68 30"
                    fill="none"
                    stroke="#FBBF24"
                    stroke-width="6"
                    stroke-linecap="round"
                />
                <!-- Body -->
                <ellipse cx="40" cy="52" rx="22" ry="18" fill="#FBBF24" />
                <!-- Head -->
                <circle cx="40" cy="32" r="18" fill="#FBBF24" />
                <!-- Ears -->
                <polygon points="26,22 30,6 38,18" fill="#F59E0B" />
                <polygon points="54,22 50,6 42,18" fill="#F59E0B" />
                <!-- Eyes -->
                <circle
                    class="eye"
                    cx="34"
                    cy="30"
                    r="2.5"
                    fill="#1F2937"
                />
                <circle
                    class="eye"
                    cx="46"
                    cy="30"
                    r="2.5"
                    fill="#1F2937"
                />
                <!-- Nose / mouth -->
                <path
                    class="mouth"
                    d="M38 38 L42 38 L40 41 Z"
                    fill="#F472B6"
                />
                <!-- Paws -->
                <ellipse class="paw" cx="30" cy="66" rx="5" ry="4" fill="#F59E0B" />
                <ellipse class="paw" cx="50" cy="66" rx="5" ry="4" fill="#F59E0B" />
            </svg>

            <TransitionGroup name="heart">
                <div
                    v-for="heart in hearts"
                    :key="heart.id"
                    class="absolute text-lg"
                    :style="{ left: `${heart.x}px`, top: `${heart.y}px` }"
                >
                    ❤️
                </div>
            </TransitionGroup>
        </div>

        <div
            v-if="message"
            class="absolute -top-8 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-[var(--surface-elevated)] px-2 py-0.5 text-xs font-medium text-[var(--text)] shadow"
        >
            {{ message }}
        </div>

        <div
            v-if="showZzz"
            class="absolute -right-2 -top-4 animate-pulse text-sm font-semibold text-[var(--muted)]"
        >
            Zzz
        </div>

        <div
            v-if="showFood"
            class="absolute -left-4 top-8 text-lg"
        >
            🐟
        </div>
    </div>
</template>

<style scoped>
.tail {
    transform-origin: 55px 55px;
    animation: tailWag 2s ease-in-out infinite;
}

.cat-play .tail {
    animation: tailWagFast 0.5s ease-in-out infinite;
}

.cat-sleep .eye {
    animation: blink 3s ease-in-out infinite;
}

.cat-walk .paw {
    animation: pawStep 0.4s ease-in-out infinite alternate;
}

.cat-eat .mouth {
    animation: munch 0.4s ease-in-out infinite alternate;
}

.cat-petted {
    animation: wiggle 0.4s ease-in-out infinite;
}

@keyframes tailWag {
    0%, 100% { transform: rotate(0deg); }
    50% { transform: rotate(15deg); }
}

@keyframes tailWagFast {
    0%, 100% { transform: rotate(-10deg); }
    50% { transform: rotate(25deg); }
}

@keyframes blink {
    0%, 90%, 100% { transform: scaleY(1); }
    95% { transform: scaleY(0.1); }
}

@keyframes pawStep {
    0% { transform: translateY(0); }
    100% { transform: translateY(-3px); }
}

@keyframes munch {
    0% { transform: scale(1); }
    100% { transform: scale(1.3) translateY(1px); }
}

@keyframes wiggle {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(-5deg); }
    75% { transform: rotate(5deg); }
}

.heart-enter-active,
.heart-leave-active {
    transition: all 1s ease;
}

.heart-enter-from,
.heart-leave-to {
    opacity: 0;
    transform: translateY(0) scale(0.5);
}

.heart-enter-to,
.heart-leave-from {
    opacity: 1;
    transform: translateY(-24px) scale(1);
}
</style>
