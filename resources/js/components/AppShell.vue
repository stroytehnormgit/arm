<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import { usePage } from '@inertiajs/vue3';
import HeaderUserButton from './HeaderUserButton.vue';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

const isOpen = usePage().props.sidebarOpen;
</script>

<template>
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
        <slot />
    </div>
    <SidebarProvider v-else :default-open="isOpen" class="sidebar-provider">
        <!-- Секция АРМ на всю ширину -->
        <div class="w-full px-6 py-4 flex items-center justify-between">
            <div class="text-5xl font-bold text-white">АРМ</div>
            <div class="flex-shrink-0">
                <HeaderUserButton />
            </div>
        </div>
        <!-- Контейнер для сайдбара и контента -->
        <div class="flex flex-1 min-h-0">
            <slot />
        </div>
    </SidebarProvider>
</template>
