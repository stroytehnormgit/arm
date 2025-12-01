<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import ArrowLeftIcon from '@/components/icons/ArrowLeftIcon.vue';
import ArrowRightIcon from '@/components/icons/ArrowRightIcon.vue';
import DownloadIcon from '@/components/icons/DownloadIcon.vue';
import OtchetIcon from '@/components/icons/OtchetIcon.vue';
import { ref, watch } from 'vue';

interface ReportItem {
    id: number;
    punkt: string;
    etap: string;
    nazvanie: string;
    razrabotchik: string;
    stoimost: number;
    period: string;
}

interface CalendarPlanItem {
    id: number;
    number: string;
    stage: string;
    deadline: string;
    amount: number;
    result: string;
}

interface Props {
    activeTab: string;
    reports: {
        data: ReportItem[];
        links: any[];
        meta: any;
    };
    calendarPlans: {
        data: CalendarPlanItem[];
        links: any[];
        meta: any;
    };
    filters: {
        name?: string;
        etap?: string;
        razrabotchik?: string;
        period?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Отчеты',
        href: '/reports',
    },
];

const activeTab = ref(props.activeTab);

const tabs = [
    { id: 'stages', label: 'Этапы за период' },
    { id: 'calendar', label: 'Календарный план' }
];

const switchTab = (tabId: string) => {
    activeTab.value = tabId;
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tabId);
    window.history.pushState({}, '', url.toString());
};

const navigateTabs = (direction: 'left' | 'right') => {
    const currentIndex = tabs.findIndex(tab => tab.id === activeTab.value);
    let newIndex;
    
    if (direction === 'left') {
        newIndex = currentIndex > 0 ? currentIndex - 1 : tabs.length - 1;
    } else {
        newIndex = currentIndex < tabs.length - 1 ? currentIndex + 1 : 0;
    }
    
    activeTab.value = tabs[newIndex].id;
    switchTab(activeTab.value);
};

const filters = ref({
    name: props.filters.name || '',
    etap: props.filters.etap || '',
    razrabotchik: props.filters.razrabotchik || '',
    period: props.filters.period || '',
});

const filterForm = useForm({
    name: filters.value.name,
    etap: filters.value.etap,
    razrabotchik: filters.value.razrabotchik,
    period: filters.value.period,
});

const applyFilters = () => {
    const params = new URLSearchParams();
    params.set('tab', activeTab.value);
    if (filterForm.name) params.set('name', filterForm.name);
    if (filterForm.etap) params.set('etap', filterForm.etap);
    if (filterForm.razrabotchik) params.set('razrabotchik', filterForm.razrabotchik);
    if (filterForm.period) params.set('period', filterForm.period);
    
    filterForm.get(`/reports?${params.toString()}`, {
        preserveState: true,
        preserveScroll: true,
    });
};

watch(filters, () => {
    filterForm.name = filters.value.name;
    filterForm.etap = filters.value.etap;
    filterForm.razrabotchik = filters.value.razrabotchik;
    filterForm.period = filters.value.period;
    applyFilters();
}, { deep: true });
</script>

<template>
    <Head title="Отчеты" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl">
            <div class="grid gap-4 rounded-xl p-4 filter-block mb-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
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
                        <label class="block text-sm font-medium mb-2">Этап</label>
                        <select class="w-full rounded-md border border-gray-300 px-3 py-2" v-model="filters.etap">
                            <option value="">Выберите этап</option>
                            <option value="OP">OP</option>
                            <option value="ПР">ПР</option>
                            <option value="ТЗ">ТЗ</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium mb-2">Разработчик</label>
                        <input 
                            type="text" 
                            class="w-full rounded-md border border-gray-300 px-3 py-2"
                            placeholder="Введите разработчика"
                            v-model="filters.razrabotchik"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Период</label>
                        <select class="w-full rounded-md border border-gray-300 px-3 py-2" v-model="filters.period">
                            <option value="">Выберите период</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                            <option value="2025-2026">2025-2026</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-4 mb-4 w-full">
                <div class="flex items-center gap-4 flex-1">
                    <button @click="navigateTabs('left')" class="w-12 h-12 rounded-full bg-[#FFB800] flex items-center justify-center flex-shrink-0 hover:bg-[#E6A600] transition-colors">
                        <ArrowLeftIcon className="w-6 h-6" style="color: #4A427B;" />
                    </button>
                    
                    <div class="flex gap-2 flex-1">
                        <button 
                            v-for="tab in tabs" 
                            :key="tab.id"
                            @click="switchTab(tab.id)"
                            class="flex-1 px-6 py-3 rounded-xl text-white font-medium transition-colors"
                            :style="{
                                backgroundColor: activeTab === tab.id 
                                    ? 'rgba(255, 253, 253, 0.3)' 
                                    : 'rgba(255, 253, 253, 0.2)',
                                height: '83px'
                            }"
                        >
                            {{ tab.label }}
                        </button>
                    </div>
                    
                    <button @click="navigateTabs('right')" class="w-12 h-12 rounded-full bg-[#FFB800] flex items-center justify-center flex-shrink-0 hover:bg-[#E6A600] transition-colors">
                        <ArrowRightIcon className="w-6 h-6" style="color: #4A427B;" />
                    </button>
                </div>

                <div class="flex gap-4 flex-1">
                    <button class="flex-1 px-6 py-3 rounded-xl text-white font-medium flex items-center justify-center gap-2" style="background-color: rgba(255, 253, 253, 0.2);height: 83px;">
                        <OtchetIcon class="w-5 h-5" />
                        ФОРМИРОВАТЬ
                    </button>
                    <button class="flex-1 px-6 py-3 rounded-xl text-white font-medium flex items-center justify-center gap-2" style="background-color: rgba(255, 253, 253, 0.2);height: 83px;">
                        <DownloadIcon class="w-5 h-5" />
                        СКАЧАТЬ ФАЙЛ
                    </button>
                </div>
            </div>

            <div class="flex-1 rounded-xl border-gray-400 overflow-hidden">
                <div class="overflow-x-auto">
                    <div>
                        <div v-if="activeTab === 'stages'" class="bg-[#FFB800] rounded-2xl px-4 py-3">
                            <div class="grid grid-cols-6 gap-4 font-semibold table-header-text table-header-container table-header-dividers">
                                <div>№ п/п (шифр)</div>
                                <div>Этап</div>
                                <div>Наименование разработки</div>
                                <div>Разработчик</div>
                                <div>Стоимость</div>
                                <div>Период</div>
                            </div>
                        </div>
                        
                        <div v-else class="bg-[#FFB800] rounded-2xl px-4 py-3">
                            <div class="grid grid-cols-5 gap-4 font-semibold table-header-text table-header-container table-header-dividers">
                                <div>№</div>
                                <div>Стадии и виды работ</div>
                                <div>Сроки выполнения</div>
                                <div>Сумма, руб</div>
                                <div>Чем заканчивается стадия</div>
                            </div>
                        </div>
                        
                        <div v-if="activeTab === 'stages'">
                            <div v-if="props.reports.data.length === 0" class="text-center py-8 text-white">
                                Нет данных для отображения
                            </div>
                            <div v-else>
                                <div v-for="(item, index) in props.reports.data" :key="item.id" :class="index % 2 === 0 ? 'bg-[#F1EFF4]' : 'bg-[#F1EFF4BF]'" class="rounded-2xl px-4 py-3 table-row-spacing mb-3">
                                    <div class="grid grid-cols-6 gap-4 text-[#080D6E] table-row-align">
                                        <div>{{ item.punkt }}</div>
                                        <div>{{ item.etap }}</div>
                                        <div>{{ item.nazvanie }}</div>
                                        <div>{{ item.razrabotchik }}</div>
                                        <div>{{ item.stoimost.toLocaleString() }}</div>
                                        <div>{{ item.period }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-else>
                            <div v-if="props.calendarPlans.data.length === 0" class="text-center py-8 text-white">
                                Нет данных для отображения
                            </div>
                            <div v-else>
                                <div v-for="(item, index) in props.calendarPlans.data" :key="item.id" :class="index % 2 === 0 ? 'bg-[#F1EFF4]' : 'bg-[#F1EFF4BF]'" class="rounded-2xl px-4 py-3 table-row-spacing mb-3">
                                    <div class="grid grid-cols-5 gap-4 text-[#080D6E] table-row-align">
                                        <div>{{ item.number }}</div>
                                        <div>{{ item.stage }}</div>
                                        <div>{{ item.deadline }}</div>
                                        <div>{{ item.amount.toLocaleString() }}</div>
                                        <div>{{ item.result }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>