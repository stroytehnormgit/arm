<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 40 40"
        :class="className"
        v-bind="$attrs"
    >
        <!-- Простая иконка АРМ -->
        <rect
            x="0"
            y="0"
            width="40"
            height="40"
            rx="6"
            fill="currentColor"
            opacity="0.1"
        />
        <text
            x="20"
            y="28"
            text-anchor="middle"
            font-family="Arial, sans-serif"
            font-size="14"
            font-weight="bold"
            fill="currentColor"
        >
            АРМ
        </text>
    </svg>
</template>
