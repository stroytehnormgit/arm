<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import ArrowLeftIcon from '@/components/icons/ArrowLeftIcon.vue';
import ArrowRightIcon from '@/components/icons/ArrowRightIcon.vue';
import DownloadIcon from '@/components/icons/DownloadIcon.vue';
import { ref, watch } from 'vue';

interface ArchiveItem {
    id: number;
    code: string;
    name: string;
    total_cost: number;
    cost_2023: number;
    cost_2024: number;
    start_date: string;
    end_date: string;
    organization: string;
    type: string;
    year_period: string;
}

interface Props {
    activeYear: string;
    archives: {
        data: ArchiveItem[];
        links: any[];
        meta: any;
    };
    filters: {
        name?: string;
        type?: string;
        organization?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Архив',
        href: '/archive',
    },
];

const activeYear = ref(props.activeYear);

const yearTabs = [
    { id: '2023-2024', label: '2023-2024' },
    { id: '2022-2023', label: '2022-2023' },
    { id: '2021-2022', label: '2021-2022' },
    { id: '2020-2021', label: '2020-2021' },
    { id: '2019-2020', label: '2019-2020' }
];

const switchYear = (yearId: string) => {
    console.log('Switching to year:', yearId);
    activeYear.value = yearId;
    const params = new URLSearchParams();
    params.set('year', yearId);
    if (filterForm.name) params.set('name', filterForm.name);
    if (filterForm.type) params.set('type', filterForm.type);
    if (filterForm.organization) params.set('organization', filterForm.organization);
    
    console.log('Sending request with params:', params.toString());
    filterForm.get(`/archive?${params.toString()}`, {
        preserveState: true,
        preserveScroll: true,
    });
};

const navigateYears = (direction: 'left' | 'right') => {
    const currentIndex = yearTabs.findIndex(tab => tab.id === activeYear.value);
    let newIndex;
    
    if (direction === 'left') {
        newIndex = currentIndex > 0 ? currentIndex - 1 : yearTabs.length - 1;
    } else {
        newIndex = currentIndex < yearTabs.length - 1 ? currentIndex + 1 : 0;
    }
    
    activeYear.value = yearTabs[newIndex].id;
    switchYear(activeYear.value);
};

const filters = ref({
    name: props.filters.name || '',
    type: props.filters.type || '',
    organization: props.filters.organization || '',
});

const filterForm = useForm({
    name: filters.value.name,
    type: filters.value.type,
    organization: filters.value.organization,
});

const applyFilters = () => {
    const params = new URLSearchParams();
    params.set('year', activeYear.value);
    if (filterForm.name) params.set('name', filterForm.name);
    if (filterForm.type) params.set('type', filterForm.type);
    if (filterForm.organization) params.set('organization', filterForm.organization);
    
    filterForm.get(`/archive?${params.toString()}`, {
        preserveState: true,
        preserveScroll: true,
    });
};

watch(filters, () => {
    filterForm.name = filters.value.name;
    filterForm.type = filters.value.type;
    filterForm.organization = filters.value.organization;
    applyFilters();
}, { deep: true });

watch(() => props.activeYear, (newYear) => {
    activeYear.value = newYear;
});
</script>

<template>
    <Head title="Архив" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl">
            <div class="grid gap-4 rounded-xl p-4 filter-block mb-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium mb-2">Наименование</label>
                        <input 
                            type="text" 
                            class="w-full rounded-md border border-gray-300 px-3 py-2"
                            placeholder="Введите наименование"
                            v-model="filters.name"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Тип разработки</label>
                        <select class="w-full rounded-md border border-gray-300 px-3 py-2" v-model="filters.type">
                            <option value="">Выберите тип</option>
                            <option value="Впервые">Впервые</option>
                            <option value="Взамен">Взамен</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Организация</label>
                        <input 
                            type="text" 
                            class="w-full rounded-md border border-gray-300 px-3 py-2"
                            placeholder="Введите организацию"
                            v-model="filters.organization"
                        />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-4 mb-4 w-full">
                <div class="flex items-center gap-4 flex-1">
                    <button @click="navigateYears('left')" class="w-12 h-12 rounded-full bg-[#FFB800] flex items-center justify-center flex-shrink-0 hover:bg-[#E6A600] transition-colors">
                        <ArrowLeftIcon className="w-6 h-6" style="color: #4A427B;" />
                    </button>
                    
                    <div class="flex gap-2 flex-1">
                        <button 
                            v-for="tab in yearTabs" 
                            :key="tab.id"
                            @click="switchYear(tab.id)"
                            class="flex-1 px-6 py-3 rounded-xl text-white font-medium transition-colors"
                            :style="{
                                backgroundColor: activeYear === tab.id 
                                    ? 'rgba(255, 253, 253, 0.3)' 
                                    : 'rgba(255, 253, 253, 0.2)',
                                height: '83px'
                            }"
                        >
                            {{ tab.label }}
                        </button>
                    </div>
                    
                    <button @click="navigateYears('right')" class="w-12 h-12 rounded-full bg-[#FFB800] flex items-center justify-center flex-shrink-0 hover:bg-[#E6A600] transition-colors">
                        <ArrowRightIcon className="w-6 h-6" style="color: #4A427B;" />
                    </button>
                </div>

                <div class="flex gap-4">
                    <button class="px-6 py-3 rounded-xl text-white font-medium flex items-center justify-center gap-2" style="background-color: rgba(255, 253, 253, 0.2);height: 83px; max-width: 330px; width: 330px;">
                        <DownloadIcon class="w-5 h-5" />
                        СКАЧАТЬ ФАЙЛ
                    </button>
                </div>
            </div>

            <div class="flex-1 rounded-xl border-gray-400 overflow-hidden">
                <div class="overflow-x-auto">
                    <div>
                        <div class="bg-[#FFB800] rounded-2xl px-4 py-3">
                            <div class="grid grid-cols-9 gap-4 font-semibold table-header-text table-header-container table-header-dividers">
                                <div>№ п/п (шифр)</div>
                                <div>Наименование разработки</div>
                                <div>Стоимость всего</div>
                                <div>Стоимость на 2023</div>
                                <div>Стоимость на 2024</div>
                                <div>Срок начала разработки</div>
                                <div>Срок окончания разработки</div>
                                <div>Наименование организаций, выполняющих работу</div>
                                <div>Разрабатывается впервые или взамен</div>
                            </div>
                        </div>
                        
                        <div v-if="props.archives.data.length === 0" class="text-center py-8 text-white">
                            Нет данных для отображения
                        </div>
                        <div v-else>
                            <div v-for="(item, index) in props.archives.data" :key="item.id" :class="index % 2 === 0 ? 'bg-[#F1EFF4]' : 'bg-[#F1EFF4BF]'" class="rounded-2xl px-4 py-3 table-row-spacing mb-3">
                                <div class="grid grid-cols-9 gap-4 text-[#080D6E] table-row-align">
                                    <div>{{ item.code }}</div>
                                    <div>{{ item.name }}</div>
                                    <div>{{ item.total_cost.toLocaleString() }}</div>
                                    <div>{{ item.cost_2023.toLocaleString() }}</div>
                                    <div>{{ item.cost_2024.toLocaleString() }}</div>
                                    <div>{{ item.start_date }}</div>
                                    <div>{{ item.end_date }}</div>
                                    <div>{{ item.organization }}</div>
                                    <div>{{ item.type }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>