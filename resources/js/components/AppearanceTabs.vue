<script setup lang="ts">
import { ref } from 'vue';
import { useAppearance, applyCustomBackground } from '@/composables/useAppearance';
import { ImageIcon } from 'lucide-vue-next';
import BackgroundSelector from './BackgroundSelector.vue';

const { appearance, updateAppearance } = useAppearance();
const isBackgroundSelectorOpen = ref(false);

const handleTabClick = () => {
    isBackgroundSelectorOpen.value = true;
};

const handleBackgroundSelect = (backgroundUrl: string) => {
    updateAppearance('system');
    applyCustomBackground(backgroundUrl);
};
</script>

<template>
    <div
        class="inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800"
    >
        <button
            @click="handleTabClick"
            :class="[
                'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                appearance === 'system'
                    ? 'bg-white shadow-xs dark:bg-neutral-700 text-black dark:text-white'
                    : 'text-black hover:bg-neutral-200/60 hover:text-black dark:text-white dark:hover:bg-neutral-700/60',
            ]"
        >
            <ImageIcon class="-ml-1 h-4 w-4" />
            <span class="ml-1.5 text-sm">Фон</span>
        </button>

        <BackgroundSelector 
            :is-open="isBackgroundSelectorOpen"
            @close="isBackgroundSelectorOpen = false"
            @select="handleBackgroundSelect"
        />
    </div>
</template>
