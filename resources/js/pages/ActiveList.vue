<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import ActiveListItemDetailModal from '@/components/ActiveListItemDetailModal.vue';

interface ActiveListItem {
    id: number;
    code: string;
    development_name: string;
    total_cost: number;
    cost_2025: number;
    cost_2026: number;
    start_date: string;
    end_date: string;
    organizations: string;
    development_type: string;
    current_stage: string;
}

interface Props {
    activeList: {
        data: ActiveListItem[];
        links: any[];
        meta: any;
    };
    filters: {
        name?: string;
        development_type?: string;
        stage?: string;
        department?: string;
    };
    developmentTypes?: string[];
    stages?: string[];
    departments?: string[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Действующий перечень',
        href: '/active-list',
    },
];

const isDetailModalOpen = ref(false);
const detailModalItem = ref<ActiveListItem | null>(null);

let clickTimeout: ReturnType<typeof setTimeout> | null = null;

const filters = ref({
    name: props.filters.name || '',
    development_type: props.filters.development_type || '',
    stage: props.filters.stage || '',
    department: props.filters.department || '',
});

const filterForm = useForm({
    name: filters.value.name,
    development_type: filters.value.development_type,
    stage: filters.value.stage,
    department: filters.value.department,
});

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    filterForm.name = filters.value.name;
    filterForm.development_type = filters.value.development_type;
    filterForm.stage = filters.value.stage;
    filterForm.department = filters.value.department;
    
    filterForm.get('/active-list', {
        preserveState: true,
        preserveScroll: true,
    });
};

watch(() => filters.value.name, () => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
    debounceTimer = setTimeout(() => {
        applyFilters();
    }, 500);
});

watch(() => filters.value.development_type, () => {
    applyFilters();
});

watch(() => filters.value.stage, () => {
    applyFilters();
});

watch(() => filters.value.department, () => {
    applyFilters();
});

const handleItemClick = (itemId: number) => {
    if (clickTimeout) {
        clearTimeout(clickTimeout);
        clickTimeout = null;
        const item = props.activeList.data.find(i => i.id === itemId);
        if (item) {
            detailModalItem.value = item;
            isDetailModalOpen.value = true;
        }
        return;
    }

    clickTimeout = setTimeout(() => {
        clickTimeout = null;
    }, 300);
};

const closeDetailModal = () => {
    isDetailModalOpen.value = false;
    detailModalItem.value = null;
};
</script>

<template>
    <Head title="Действующий перечень" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl">
            <!-- Фильтры -->
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
                        <label class="block text-sm font-medium mb-2">Тип разработки</label>
                        <select class="w-full rounded-md border border-gray-300 px-3 py-2" v-model="filters.development_type">
                            <option value="">Выберите тип</option>
                            <option v-for="type in (props.developmentTypes || [])" :key="type" :value="type">{{ type }}</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium mb-2">Этап</label>
                        <select class="w-full rounded-md border border-gray-300 px-3 py-2" v-model="filters.stage">
                            <option value="">Выберите этап</option>
                            <option v-for="stage in (props.stages || [])" :key="stage" :value="stage">{{ stage }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Отдел</label>
                        <select class="w-full rounded-md border border-gray-300 px-3 py-2" v-model="filters.department">
                            <option value="">Выберите отдел</option>
                            <option v-for="dept in (props.departments || [])" :key="dept" :value="dept">{{ dept }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Таблица данных -->
            <div class="flex-1 rounded-xl border-gray-400 overflow-hidden">
                <div class="overflow-x-auto">
                    <div>
                        <!-- Заголовок таблицы -->
                        <div class="bg-[#FFB800] rounded-2xl px-4 py-3">
                            <div class="grid grid-cols-10 gap-4 font-semibold table-header-text table-header-container table-header-dividers">
                                <div>№ п/п (шифр)</div>
                                <div>Наименование разработки</div>
                                <div>Стоимость всего</div>
                                <div>Стоимость на 2025</div>
                                <div>Стоимость на 2026</div>
                                <div>Срок начала</div>
                                <div>Срок окончания</div>
                                <div>Организации</div>
                                <div>Тип разработки</div>
                                <div>Текущий этап</div>
                            </div>
                        </div>
                        
                        <!-- Строки данных -->
                        <div v-if="props.activeList.data.length === 0" class="text-center py-8 text-white">
                            Нет данных для отображения
                        </div>
                        <div v-else>
                            <div 
                                v-for="(item, index) in props.activeList.data" 
                                :key="item.id" 
                                @click="handleItemClick(item.id)"
                                :class="index % 2 === 0 ? 'bg-[#F1EFF4]' : 'bg-[#F1EFF4BF]'" 
                                class="rounded-2xl px-4 py-3 table-row-spacing mb-3 cursor-pointer transition-all hover:ring-2 hover:ring-[#FFB800] hover:ring-opacity-30"
                            >
                                <div class="grid grid-cols-10 gap-4 text-[#080D6E] table-row-align">
                                    <div>{{ item.code }}</div>
                                    <div>{{ item.development_name }}</div>
                                    <div>{{ item.total_cost.toLocaleString() }}</div>
                                    <div>{{ item.cost_2025.toLocaleString() }}</div>
                                    <div>{{ item.cost_2026.toLocaleString() }}</div>
                                    <div>{{ item.start_date }}</div>
                                    <div>{{ item.end_date }}</div>
                                    <div>{{ item.organizations }}</div>
                                    <div>{{ item.development_type }}</div>
                                    <div>{{ item.current_stage }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно детальной информации -->
        <ActiveListItemDetailModal 
            :is-open="isDetailModalOpen"
            :item="detailModalItem"
            @close="closeDetailModal"
        />
    </AppLayout>
</template>
