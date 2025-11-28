<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import FireIcon from './icons/FireIcon.vue';
import LampIcon from './icons/LampIcon.vue';
import ScrepkaIcon from './icons/ScrepkaIcon.vue';
import OtchetIcon from './icons/OtchetIcon.vue';
import ArchiveIcon from './icons/ArchiveIcon.vue';
import CoinsIcon from './icons/CoinsIcon.vue';
import UsersIcon from './icons/UsersIcon.vue';
import StagesIcon from './icons/StagesIcon.vue';
import ExitIcon from './icons/ExitIcon.vue';

const page = usePage();
const auth = computed(() => page.props.auth as any);
const isAdmin = computed(() => auth.value?.user?.is_admin || false);

const mainNavItems = computed(() => [
    {
        title: 'Действующий перечень',
        href: '/active-list',
        icon: FireIcon,
    },
    {
        title: 'Планируемый перечень',
        href: '/planned-list',
        icon: LampIcon,
    },
    {
        title: 'Файлы',
        href: '/files',
        icon: ScrepkaIcon,
    },
    {
        title: 'Отчеты',
        href: '/reports',
        icon: OtchetIcon,
    },
    {
        title: 'Архив',
        href: '/archive',
        icon: ArchiveIcon,
    },
    ...(isAdmin.value ? [
        {
            title: 'Стоимость',
            href: '/cost',
            icon: CoinsIcon,
        },
        {
            title: 'Пользователи',
            href: '/users',
            icon: UsersIcon,
        },
        {
            title: 'Этапы',
            href: '/stages',
            icon: StagesIcon,
        },
    ] : []),
]);

const footerNavItems: NavItem[] = [
    {
        title: 'Выход',
        href: '/logout',
        icon: ExitIcon,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
