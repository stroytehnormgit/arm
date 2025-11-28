<template>
    <SvgIcon :src="src" :alt="alt" :className="className" />
</template>

<script setup lang="ts">
import SvgIcon from './SvgIcon.vue';

interface Props {
    src?: string;
    alt?: string;
    className?: string;
}

const props = withDefaults(defineProps<Props>(), {
    src: '/icons/arrow.svg',
    alt: 'Стрелка',
    className: 'w-12 h-12'
});
</script>
