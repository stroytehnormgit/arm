<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface Activity {
    id: number;
    log_name: string;
    description: string;
    subject_type: string | null;
    subject_id: number | null;
    event: string | null;
    causer_type: string | null;
    causer_id: number | null;
    properties: any;
    created_at: string;
    causer?: {
        id: number;
        name: string;
        email: string;
    } | null;
    subject?: any;
}

interface Props {
    activities: {
        data: Activity[];
        links: any[];
        meta: any;
    };
    users: Array<{
        id: number;
        name: string;
        email: string;
    }>;
    subjectTypes: Array<{
        value: string;
        label: string;
    }>;
    events: Array<{
        value: string;
        label: string;
    }>;
    filters: {
        user_id?: string;
        subject_type?: string;
        event?: string;
        search?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Журнал действий',
        href: '/activity-log',
    },
];

const filters = ref({
    user_id: props.filters.user_id || '',
    subject_type: props.filters.subject_type || '',
    event: props.filters.event || '',
    search: props.filters.search || '',
});

const filterForm = useForm({
    user_id: filters.value.user_id,
    subject_type: filters.value.subject_type,
    event: filters.value.event,
    search: filters.value.search,
});

const applyFilters = () => {
    filterForm.get('/activity-log', {
        preserveState: true,
        preserveScroll: true,
    });
};

watch(filters, () => {
    filterForm.user_id = filters.value.user_id;
    filterForm.subject_type = filters.value.subject_type;
    filterForm.event = filters.value.event;
    filterForm.search = filters.value.search;
    applyFilters();
}, { deep: true });

const clearFilters = () => {
    filters.value = {
        user_id: '',
        subject_type: '',
        event: '',
        search: '',
    };
};

const getSubjectName = (activity: Activity): string => {
    if (!activity.subject) return '—';
    
    if (activity.subject_type?.includes('PlannedList')) {
        return activity.subject.development_name || activity.subject.code || `ID: ${activity.subject_id}`;
    }
    if (activity.subject_type?.includes('ActiveList')) {
        return activity.subject.development_name || activity.subject.code || `ID: ${activity.subject_id}`;
    }
    if (activity.subject_type?.includes('User')) {
        return activity.subject.name || activity.subject.email || `ID: ${activity.subject_id}`;
    }
    if (activity.subject_type?.includes('Cost')) {
        return `Год: ${activity.subject.year || activity.subject_id}`;
    }
    
    return `ID: ${activity.subject_id}`;
};

const formatProperties = (properties: any): string => {
    if (!properties) return '';
    
    if (properties.attributes && properties.old) {
        const changes: string[] = [];
        Object.keys(properties.attributes).forEach(key => {
            if (properties.old[key] !== properties.attributes[key]) {
                changes.push(`${key}: "${properties.old[key]}" → "${properties.attributes[key]}"`);
            }
        });
        return changes.join(', ');
    }
    
    if (properties.attributes) {
        return Object.keys(properties.attributes).slice(0, 3).join(', ') + (Object.keys(properties.attributes).length > 3 ? '...' : '');
    }
    
    return JSON.stringify(properties).substring(0, 100);
};

const getEventColor = (event: string | null): string => {
    switch (event) {
        case 'created':
            return 'text-green-600';
        case 'updated':
            return 'text-blue-600';
        case 'deleted':
            return 'text-red-600';
        default:
            return 'text-gray-600';
    }
};

const getEventLabel = (event: string | null): string => {
    switch (event) {
        case 'created':
            return 'Создание';
        case 'updated':
            return 'Обновление';
        case 'deleted':
            return 'Удаление';
        default:
            return event || '—';
    }
};
</script>

<template>
    <Head title="Журнал действий" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl">
            <div class="rounded-xl p-4 mb-4" style="background: rgba(255, 253, 253, 0.2) !important;">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Поиск</label>
                        <input 
                            v-model="filters.search"
                            type="text" 
                            placeholder="Поиск по описанию..."
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Пользователь</label>
                        <select 
                            v-model="filters.user_id"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none"
                        >
                            <option value="">Все пользователи</option>
                            <option v-for="user in props.users" :key="user.id" :value="user.id">
                                {{ user.name }} ({{ user.email }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Тип объекта</label>
                        <select 
                            v-model="filters.subject_type"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none"
                        >
                            <option value="">Все типы</option>
                            <option v-for="type in props.subjectTypes" :key="type.value" :value="type.value">
                                {{ type.label }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Действие</label>
                        <select 
                            v-model="filters.event"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none"
                        >
                            <option value="">Все действия</option>
                            <option v-for="event in props.events" :key="event.value" :value="event.value">
                                {{ event.label }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex gap-2">
                    <button 
                        @click="clearFilters"
                        class="px-4 py-2 bg-gray-200 text-[#080D6E] rounded-lg font-medium hover:bg-gray-300 transition-colors"
                    >
                        Сбросить фильтры
                    </button>
                </div>
            </div>

            <div class="flex-1 rounded-xl border-gray-400 overflow-hidden">
                <div class="overflow-x-auto">
                    <div>
                        <div class="bg-[#FFB800] rounded-2xl px-3 py-2 mb-2">
                            <div class="grid gap-2 font-semibold table-header-text table-header-container table-header-dividers" style="grid-template-columns: 180px 200px 150px 200px 250px 1fr; font-size: 13px;">
                                <div>Время</div>
                                <div>Пользователь</div>
                                <div>Действие</div>
                                <div>Тип объекта</div>
                                <div>Объект</div>
                                <div>Описание / Изменения</div>
                            </div>
                        </div>
                        
                        <div v-if="props.activities.data.length === 0" class="text-center py-8 text-white text-base">
                            Нет записей для отображения
                        </div>
                        <div v-else>
                            <div 
                                v-for="(activity, index) in props.activities.data" 
                                :key="activity.id" 
                                :class="[
                                    index % 2 === 0 ? 'bg-[#F1EFF4]' : 'bg-[#F1EFF4BF]',
                                ]" 
                                class="rounded-2xl px-4 py-3 table-row-spacing mb-2 transition-all"
                            >
                                <div class="grid gap-4 text-[#080D6E] table-row-align" style="grid-template-columns: 180px 200px 150px 200px 250px 1fr; font-size: 13px;">
                                    <div class="whitespace-nowrap">
                                        {{ new Date(activity.created_at).toLocaleString('ru-RU') }}
                                    </div>
                                    <div>
                                        <div v-if="activity.causer" class="font-medium">
                                            {{ activity.causer.name }}
                                        </div>
                                        <div v-if="activity.causer" class="text-xs text-gray-500">
                                            {{ activity.causer.email }}
                                        </div>
                                        <div v-else class="text-gray-400">Система</div>
                                    </div>
                                    <div>
                                        <span :class="getEventColor(activity.event)" class="font-semibold">
                                            {{ getEventLabel(activity.event) }}
                                        </span>
                                    </div>
                                    <div>
                                        {{ activity.subject_type ? activity.subject_type.split('\\').pop() : '—' }}
                                    </div>
                                    <div class="font-medium">
                                        {{ getSubjectName(activity) }}
                                    </div>
                                    <div class="text-sm">
                                        <div class="font-medium mb-1">{{ activity.description }}</div>
                                        <div v-if="formatProperties(activity.properties)" class="text-xs text-gray-600">
                                            {{ formatProperties(activity.properties) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="props.activities.links.length > 3" class="flex justify-center items-center gap-2 mt-4">
                    <template v-for="(link, index) in props.activities.links" :key="index">
                        <a 
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            :class="[
                                'px-4 py-2 rounded-lg',
                                link.active ? 'bg-[#FFB800] text-white font-semibold' : 'bg-gray-200 text-[#080D6E] hover:bg-gray-300'
                            ]"
                        />
                        <span 
                            v-else
                            v-html="link.label"
                            class="px-4 py-2 text-gray-400"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

