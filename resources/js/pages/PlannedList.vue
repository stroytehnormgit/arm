<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import CrossIcon from '@/components/icons/CrossIcon.vue';
import PencilIcon from '@/components/icons/PencilIcon.vue';
import EyeIcon from '@/components/icons/EyeIcon.vue';
import ArrowIcon from '@/components/icons/ArrowIcon.vue';
import TrashIcon from '@/components/icons/TrashIcon.vue';
import AddProposalModal from '@/components/AddProposalModal.vue';
import PlannedListItemDetailModal from '@/components/PlannedListItemDetailModal.vue';
import { ref, watch, computed } from 'vue';

interface PlannedListItem {
    id: number;
    code?: string;
    development_name?: string;
    total_cost?: number | null;
    cost_2025?: number | null;
    cost_2026?: number | null;
    start_date?: string;
    end_date?: string;
    organizations?: string;
    development_type?: string;
    document_type?: string;
    designation?: string;
    development_end?: string;
    page_count?: number;
    development_start?: string;
    block?: string;
    author?: string;
    cost?: number | string;
    department?: string;
    regulatory_documents?: string;
    first_year_stages?: string;
    subsequent_years_stages?: string;
    stages?: Array<{
        id: number;
        name: string;
        pivot: {
            start_date?: string;
            end_date?: string;
            amount?: number;
        };
    }>;
}

interface Props {
    plannedList: {
        data: PlannedListItem[];
        links: any[];
        meta: any;
    };
    stages?: Array<{
        id: number;
        name: string;
    }>;
    filters: {
        name?: string;
        development_type?: string;
        organization?: string;
    };
    costData?: {
        average_monthly_salary: number | null;
        document_volume_coefficient: number | null;
        mandatory_payments_qn: number | null;
        overhead_costs_qnr: number | null;
        profit_qp: number | null;
        other_expenses_qpr: number | null;
        review_cost_sp: number | null;
    } | null;
}

const props = defineProps<Props>();

const page = usePage();
const auth = computed(() => page.props.auth as any);
const isAdmin = computed(() => auth.value?.user?.is_admin || false);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Планируемый перечень',
        href: '/planned-list',
    },
];

const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDetailModalOpen = ref(false);
const detailModalItem = ref<PlannedListItem | null>(null);

const selectedItemId = ref<number | null>(null);

const selectedItemsIds = ref<Set<number>>(new Set());
const isGenerateModalOpen = ref(false);

let clickTimeout: number | null = null;

const filters = ref({
    name: props.filters.name || '',
    development_type: props.filters.development_type || '',
    organization: props.filters.organization || '',
});

const filterForm = useForm({
    name: filters.value.name,
    development_type: filters.value.development_type,
    organization: filters.value.organization,
});

const applyFilters = () => {
    filterForm.get('/planned-list', {
        preserveState: true,
        preserveScroll: true,
    });
};

watch(filters, () => {
    filterForm.name = filters.value.name;
    filterForm.development_type = filters.value.development_type;
    filterForm.organization = filters.value.organization;
    applyFilters();
}, { deep: true });

const openAddModal = () => {
    isAddModalOpen.value = true;
};

const closeAddModal = () => {
    isAddModalOpen.value = false;
};

const handleItemClick = (itemId: number, event?: MouseEvent) => {
    if (event?.ctrlKey || event?.shiftKey) {
        if (selectedItemsIds.value.has(itemId)) {
            selectedItemsIds.value.delete(itemId);
        } else {
            selectedItemsIds.value.add(itemId);
        }
        selectedItemId.value = null;
        return;
    }

    if (clickTimeout) {
        clearTimeout(clickTimeout);
        clickTimeout = null;
        const item = props.plannedList.data.find(i => i.id === itemId);
        if (item) {
            detailModalItem.value = item;
            isDetailModalOpen.value = true;
        }
        return;
    }

    clickTimeout = setTimeout(() => {
        selectedItemId.value = selectedItemId.value === itemId ? null : itemId;
        selectedItemsIds.value.clear();
        clickTimeout = null;
    }, 300);
};

const selectItem = (itemId: number, event?: MouseEvent) => {
    handleItemClick(itemId, event);
};

const toggleItemForGenerate = (itemId: number) => {
    if (selectedItemsIds.value.has(itemId)) {
        selectedItemsIds.value.delete(itemId);
    } else {
        selectedItemsIds.value.add(itemId);
    }
    selectedItemId.value = null;
};

const openEditModal = () => {
    if (selectedItemId.value) {
        isEditModalOpen.value = true;
    }
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
    selectedItemId.value = null;
};

const closeDetailModal = () => {
    isDetailModalOpen.value = false;
    detailModalItem.value = null;
};

const selectedItem = computed(() => {
    if (!selectedItemId.value) return null;
    return props.plannedList.data.find(item => item.id === selectedItemId.value) || null;
});

const selectedItemsForGenerate = computed(() => {
    return props.plannedList.data.filter(item => selectedItemsIds.value.has(item.id));
});

const groupedItems = computed(() => {
    const groups = new Map<string, PlannedListItem[]>();
    
    selectedItemsForGenerate.value.forEach(item => {
        const name = item.development_name || 'Без наименования';
        if (!groups.has(name)) {
            groups.set(name, []);
        }
        groups.get(name)!.push(item);
    });
    
    return Array.from(groups.entries()).map(([name, items]) => ({
        name,
        items,
        totalCost: items.reduce((sum, item) => sum + (item.total_cost || 0), 0),
        cost2025: items.reduce((sum, item) => sum + (item.cost_2025 || 0), 0),
        cost2026: items.reduce((sum, item) => sum + (item.cost_2026 || 0), 0),
    }));
});

const closeGenerateModal = () => {
    isGenerateModalOpen.value = false;
};

const formatDateForTable = (dateStr: string | undefined): string => {
    if (!dateStr) return '';
    
    const months = [
        'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
        'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
    ];
    
    try {
        const date = new Date(dateStr);
        const month = months[date.getMonth()];
        const year = date.getFullYear();
        return `${month} ${year}`;
    } catch {
        return dateStr;
    }
};

const exportToWord = () => {
    if (selectedItemsForGenerate.value.length === 0) {
        return;
    }
    const itemIds = Array.from(selectedItemsIds.value);
    if (itemIds.length === 0) {
        return;
    }
    window.location.href = `/planned-list/export?ids=${itemIds.join(',')}`;
};

const previewWord = () => {
    if (selectedItemsForGenerate.value.length === 0) {
        return;
    }
    exportToWord();
};

const openGenerateModal = () => {
    if (!isAdmin.value) {
        alert('Только администраторы могут формировать перечень');
        return;
    }
    
    approvePlannedList();
};

const openPreviewModal = () => {
    if (selectedItemsForGenerate.value.length > 0) {
        isGenerateModalOpen.value = true;
    }
};

const deleteItem = (itemId: number) => {
    const item = props.plannedList.data.find(i => i.id === itemId);
    const itemName = item?.development_name || item?.code || 'эту запись';
    
    if (confirm(`Вы уверены, что хотите удалить запись "${itemName}"?`)) {
        router.delete(`/planned-list/${itemId}`, {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedItemId.value === itemId) {
                    selectedItemId.value = null;
                }
                selectedItemsIds.value.delete(itemId);
            },
        });
    }
};

const approvePlannedList = () => {
    if (confirm('Вы уверены, что хотите сформировать перечень? Все записи будут перенесены в действующий перечень с этапом "ТЗ".')) {
        router.post('/planned-list/approve', {}, {
            preserveScroll: true,
            onSuccess: () => {
                const flash = (page.props as any).flash;
                if (flash?.success) {
                    alert(flash.success);
                } else {
                    alert('Перечень успешно сформирован! Все записи перенесены в действующий перечень.');
                }
                router.reload();
            },
            onError: (errors) => {
                const flash = (page.props as any).flash;
                const errorMessage = flash?.error || (errors as any).message || 'Ошибка при формировании перечня';
                alert(errorMessage);
                console.error('Ошибка при утверждении перечня:', errors);
            },
        });
    }
};

const deleteSelectedItems = () => {
    const itemsToDeleteIds: number[] = [];
    
    if (selectedItemId.value) {
        itemsToDeleteIds.push(selectedItemId.value);
    }
    
    itemsToDeleteIds.push(...Array.from(selectedItemsIds.value));
    
    const uniqueIds = Array.from(new Set(itemsToDeleteIds));
    
    if (uniqueIds.length === 0) {
        return;
    }
    
    const itemsToDelete = props.plannedList.data.filter(item => uniqueIds.includes(item.id));
    const itemsCount = itemsToDelete.length;
    const itemsList = itemsToDelete.map(item => `"${item.development_name || item.code || 'Без наименования'}"`).join('\n');
    
    if (confirm(`Вы уверены, что хотите удалить ${itemsCount} ${itemsCount === 1 ? 'запись' : itemsCount < 5 ? 'записи' : 'записей'}?\n\n${itemsList}`)) {
        if (uniqueIds.length === 1) {
            deleteItem(uniqueIds[0]);
            return;
        }
        
        let currentIndex = 0;
        
        const deleteNext = () => {
            if (currentIndex >= uniqueIds.length) {
                selectedItemsIds.value.clear();
                selectedItemId.value = null;
                return;
            }
            
            const idToDelete = uniqueIds[currentIndex];
            router.delete(`/planned-list/${idToDelete}`, {
                preserveScroll: true,
                onSuccess: () => {
                    selectedItemsIds.value.delete(idToDelete);
                    if (selectedItemId.value === idToDelete) {
                        selectedItemId.value = null;
                    }
                    currentIndex++;
                    if (currentIndex < uniqueIds.length) {
                        setTimeout(deleteNext, 300);
                    } else {
                        selectedItemsIds.value.clear();
                        selectedItemId.value = null;
                    }
                },
                onError: () => {
                    currentIndex++;
                    if (currentIndex < uniqueIds.length) {
                        setTimeout(deleteNext, 300);
                    }
                },
            });
        };
        
        deleteNext();
    }
};
</script>

<template>
    <Head title="Планируемый перечень" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl">
            <!-- Кнопки действий -->
            <div class="flex gap-4 rounded-xl mb-4">
                <button @click="openAddModal" class="flex-1 min-h-[83px] rounded-xl px-4 py-3 text-white font-medium flex items-center justify-center gap-2" style="background-color: rgba(255, 253, 253, 0.2);">
                    <CrossIcon class="w-5 h-5" />
                    ДОБАВИТЬ
                </button>
                <button 
                    @click="openEditModal"
                    :disabled="!selectedItemId"
                    :class="[
                        'flex-1 min-h-[83px] rounded-xl px-4 py-3 text-white font-medium flex items-center justify-center gap-2',
                        selectedItemId ? 'cursor-pointer hover:opacity-90' : 'opacity-50 cursor-not-allowed'
                    ]"
                    style="background-color: rgba(255, 253, 253, 0.2);"
                >
                    <PencilIcon class="w-5 h-5" />
                    РЕДАКТИРОВАТЬ
                </button>
                <button 
                    @click="previewWord"
                    :disabled="selectedItemsForGenerate.length === 0"
                    :class="[
                        'flex-1 min-h-[83px] rounded-xl px-4 py-3 text-white font-medium flex items-center justify-center gap-2',
                        selectedItemsForGenerate.length > 0 ? 'cursor-pointer hover:opacity-90' : 'opacity-50 cursor-not-allowed'
                    ]"
                    style="background-color: rgba(255, 253, 253, 0.2);"
                >
                    <EyeIcon class="w-5 h-5" />
                    ПРЕДВАРИТЕЛЬНЫЙ ПРОСМОТР
                </button>
                <button 
                    v-if="isAdmin"
                    @click="openGenerateModal"
                    :class="[
                        'flex-1 min-h-[83px] rounded-xl px-4 py-3 text-white font-medium flex items-center justify-center gap-2 cursor-pointer hover:opacity-90'
                    ]"
                    style="background-color: rgba(34, 197, 94, 0.2);"
                    title="Сформировать перечень: перенести все записи из планируемого перечня в действующий"
                >
                    <ArrowIcon class="w-5 h-5" />
                    ФОРМИРОВАТЬ ПЕРЕЧЕНЬ
                </button>
                <button 
                    v-if="isAdmin"
                    @click="deleteSelectedItems"
                    :disabled="selectedItemsForGenerate.length === 0 && !selectedItemId"
                    :class="[
                        'flex-1 min-h-[83px] rounded-xl px-4 py-3 text-white font-medium flex items-center justify-center gap-2',
                        (selectedItemsForGenerate.length > 0 || selectedItemId) ? 'cursor-pointer hover:opacity-90' : 'opacity-50 cursor-not-allowed'
                    ]"
                    style="background-color: rgba(220, 38, 38, 0.2);"
                    title="Удалить выбранную запись или выбранные записи"
                >
                    <TrashIcon class="w-7 h-7 text-[#FFB800]" />
                    УДАЛИТЬ
                </button>
            </div>

            <!-- Таблица данных -->
            <div class="flex-1 rounded-xl border-gray-400 overflow-hidden">
                <div class="overflow-x-auto">
                    <div>
                        <!-- Заголовок таблицы -->
                        <div class="bg-[#FFB800] rounded-2xl px-4 py-3">
                            <div class="grid grid-cols-12 gap-4 font-semibold table-header-text table-header-container table-header-dividers">
                                <div></div>
                                <div>№ п/п (шифр)</div>
                                <div>Наименование разработки</div>
                                <div>Стоимость всего</div>
                                <div>Стоимость на 2025</div>
                                <div>Стоимость на 2026</div>
                                <div>Срок начала разработки</div>
                                <div>Срок окончания разработки</div>
                                <div>Наименование организаций, выполняющих работу</div>
                                <div>Ответственный отдел</div>
                                <div>Разрабатывается впервые или взамен</div>
                                <div>Действия</div>
                            </div>
                        </div>
                        
                        <!-- Строки данных -->
                        <div v-if="props.plannedList.data.length === 0" class="text-center py-8 text-white">
                            Нет данных для отображения
                        </div>
                        <div v-else>
                            <div 
                                v-for="(item, index) in props.plannedList.data" 
                                :key="item.id" 
                                @click="selectItem(item.id, $event)"
                                :class="[
                                    index % 2 === 0 ? 'bg-[#F1EFF4]' : 'bg-[#F1EFF4BF]',
                                    selectedItemId === item.id ? 'ring-4 ring-[#FFB800] ring-opacity-60 shadow-lg' : 
                                    selectedItemsIds.has(item.id) ? 'ring-4 ring-blue-500 ring-opacity-60 shadow-lg' :
                                    'hover:ring-2 hover:ring-[#FFB800] hover:ring-opacity-30'
                                ]" 
                                class="rounded-2xl px-4 py-3 table-row-spacing mb-3 cursor-pointer transition-all"
                            >
                                <div class="grid grid-cols-12 gap-4 text-[#080D6E] table-row-align">
                                    <!-- Чекбокс для множественного выбора -->
                                    <div class="flex items-center justify-center">
                                        <input 
                                            type="checkbox"
                                            :checked="selectedItemsIds.has(item.id)"
                                            @click.stop="toggleItemForGenerate(item.id)"
                                            class="w-5 h-5 text-[#FFB800] rounded focus:ring-[#FFB800] focus:ring-2"
                                        />
                                    </div>
                                    <div>{{ item.code }}</div>
                                    <div>{{ item.development_name }}</div>
                                    <div>{{ item.total_cost != null ? item.total_cost.toLocaleString() : '—' }}</div>
                                    <div>{{ item.cost_2025 != null ? item.cost_2025.toLocaleString() : '—' }}</div>
                                    <div>{{ item.cost_2026 != null ? item.cost_2026.toLocaleString() : '—' }}</div>
                                    <div>{{ item.start_date }}</div>
                                    <div>{{ item.end_date }}</div>
                                    <div>{{ item.organizations }}</div>
                                    <div>{{ item.department }}</div>
                                    <div>{{ item.development_type }}</div>
                                    <!-- Кнопка удаления (только для администратора) -->
                                    <div v-if="isAdmin" class="flex items-center justify-center">
                                        <button
                                            @click.stop="deleteItem(item.id)"
                                            class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors"
                                            title="Удалить запись"
                                        >
                                            <TrashIcon class="w-5 h-5" />
                                        </button>
                                    </div>
                                    <div v-else class="flex items-center justify-center">
                                        <!-- Пустая ячейка для не-администраторов -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно добавления предложения -->
        <AddProposalModal 
            :is-open="isAddModalOpen" 
            :stages="props.stages || []"
            :cost-data="props.costData"
            @close="closeAddModal" 
        />
        
        <!-- Модальное окно редактирования предложения -->
        <AddProposalModal 
            :is-open="isEditModalOpen" 
            :edit-item="selectedItem"
            :stages="props.stages || []"
            :cost-data="props.costData"
            @close="closeEditModal" 
        />
        
        <!-- Модальное окно детальной информации -->
        <PlannedListItemDetailModal 
            :is-open="isDetailModalOpen"
            :item="detailModalItem"
            :stages="props.stages"
            @close="closeDetailModal"
        />
        
        <!-- Модальное окно формирования перечня -->
        <div v-if="isGenerateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" @click="closeGenerateModal">
            <div class="bg-white rounded-2xl p-6 max-w-7xl w-full max-h-[95vh] overflow-y-auto shadow-2xl" @click.stop>
                <!-- Заголовок -->
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-[#080D6E] text-xl font-bold">
                        формировать перечень (ВЫГРУЗИТЬ ВОРД)
                    </h2>
                    <button @click="closeGenerateModal" class="text-[#080D6E] hover:text-gray-600 text-2xl font-bold leading-none w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">
                        ×
                    </button>
                </div>

                <!-- Таблица -->
                <div class="overflow-x-auto mb-6 max-w-full">
                    <table class="min-w-full border-collapse" style="font-size: 12px;">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="border border-gray-400 px-3 py-2 text-left font-semibold text-[#080D6E] whitespace-nowrap" style="font-size: 12px;">№ п/п (шифр)</th>
                                <th class="border border-gray-400 px-3 py-2 text-left font-semibold text-[#080D6E] min-w-[200px]" style="font-size: 12px;">Наименование разработки</th>
                                <th class="border border-gray-400 px-3 py-2 text-left font-semibold text-[#080D6E] whitespace-nowrap" style="font-size: 12px;">Стоимость всего</th>
                                <th class="border border-gray-400 px-3 py-2 text-left font-semibold text-[#080D6E] whitespace-nowrap" style="font-size: 12px;">Стоимость на 2025</th>
                                <th class="border border-gray-400 px-3 py-2 text-left font-semibold text-[#080D6E] whitespace-nowrap" style="font-size: 12px;">Стоимость на 2026</th>
                                <th class="border border-gray-400 px-3 py-2 text-left font-semibold text-[#080D6E] whitespace-nowrap" style="font-size: 12px;">Срок начала разработки (месяц, год)</th>
                                <th class="border border-gray-400 px-3 py-2 text-left font-semibold text-[#080D6E] whitespace-nowrap" style="font-size: 12px;">Срок окончания разработки (месяц, год)</th>
                                <th class="border border-gray-400 px-3 py-2 text-left font-semibold text-[#080D6E] min-w-[250px]" style="font-size: 12px;">Наименование организаций, выполняющих работу, и номер Технического Комитета</th>
                                <th class="border border-gray-400 px-3 py-2 text-left font-semibold text-[#080D6E] whitespace-nowrap" style="font-size: 12px;">Ответственный отдел</th>
                                <th class="border border-gray-400 px-3 py-2 text-left font-semibold text-[#080D6E] min-w-[200px]" style="font-size: 12px;">Разрабатывается впервые или взамен действующих нормативных документов</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="(group, groupIndex) in groupedItems" :key="groupIndex">
                                <tr 
                                    v-for="(item, itemIndex) in group.items" 
                                    :key="item.id"
                                    :class="itemIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
                                >
                                    <td class="border border-gray-400 px-3 py-2 text-[#080D6E] whitespace-nowrap" style="font-size: 12px;">{{ item.code || '' }}</td>
                                    <td class="border border-gray-400 px-3 py-2 text-[#080D6E]" style="font-size: 12px;">{{ item.development_name || '' }}</td>
                                    <td class="border border-gray-400 px-3 py-2 text-[#080D6E] whitespace-nowrap" style="font-size: 12px;">{{ item.total_cost != null ? item.total_cost.toLocaleString('ru-RU') : '' }}</td>
                                    <td class="border border-gray-400 px-3 py-2 text-[#080D6E] whitespace-nowrap" style="font-size: 12px;">{{ item.cost_2025 != null ? item.cost_2025.toLocaleString('ru-RU') : '' }}</td>
                                    <td class="border border-gray-400 px-3 py-2 text-[#080D6E] whitespace-nowrap" style="font-size: 12px;">{{ item.cost_2026 != null ? item.cost_2026.toLocaleString('ru-RU') : '' }}</td>
                                    <td class="border border-gray-400 px-3 py-2 text-[#080D6E] whitespace-nowrap" style="font-size: 12px;">{{ formatDateForTable(item.development_start || item.start_date) }}</td>
                                    <td class="border border-gray-400 px-3 py-2 text-[#080D6E] whitespace-nowrap" style="font-size: 12px;">{{ formatDateForTable(item.development_end || item.end_date) }}</td>
                                    <td class="border border-gray-400 px-3 py-2 text-[#080D6E]" style="font-size: 12px;">{{ item.organizations || '' }}</td>
                                    <td class="border border-gray-400 px-3 py-2 text-[#080D6E]" style="font-size: 12px;">{{ item.department || '' }}</td>
                                    <td class="border border-gray-400 px-3 py-2 text-[#080D6E]" style="font-size: 12px;">{{ item.development_type || '' }}</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Кнопка экспорта -->
                <div class="flex justify-end gap-4 pt-6 border-t border-gray-300">
                    <button 
                        type="button"
                        @click="closeGenerateModal"
                        class="px-8 py-3 bg-gray-200 text-[#080D6E] rounded-lg font-medium hover:bg-gray-300 transition-colors"
                    >
                        Отмена
                    </button>
                    <button 
                        type="button"
                        @click="exportToWord"
                        class="px-8 py-3 bg-[#FFB800] text-white rounded-lg font-medium hover:bg-[#E6A600] transition-colors"
                    >
                        ВЫГРУЗИТЬ Word
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
