<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';
import { computed } from 'vue';

interface Props {
    user: User;
    showEmail?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

const { getInitials } = useInitials();

const showAvatar = computed(
    () => props.user.avatar && props.user.avatar !== '',
);
</script>

<template>
    <Avatar class="h-8 w-8 overflow-hidden rounded-full">
        <AvatarImage v-if="showAvatar" :src="user.avatar!" :alt="user.name" />
        <AvatarFallback class="flex h-full w-full items-center justify-center rounded-full bg-[#FFB800] font-semibold text-[#080D6E] text-xs">
            {{ getInitials(user.name) }}
        </AvatarFallback>
    </Avatar>

    <div class="grid flex-1 text-left text-xs leading-tight items-center">
        <span class="truncate font-semibold text-[#080D6E]">{{ user.name }}</span>
        <span v-if="showEmail" class="truncate text-[11px] text-muted-foreground">{{ user.email }}</span>
    </div>
</template>
