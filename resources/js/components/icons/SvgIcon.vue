<template>
    <img :src="src" :alt="alt" :class="className || 'w-12 h-12'" />
</template>

<script setup lang="ts">
interface Props {
    src: string;
    alt?: string;
    className?: string;
}

defineProps<Props>();
</script>
