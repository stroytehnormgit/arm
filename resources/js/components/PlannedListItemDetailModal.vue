<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';

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
    isOpen: boolean;
    item: PlannedListItem | null;
    stages?: Array<{
        id: number;
        name: string;
    }>;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    close: [];
}>();

const activeTab = ref<string>('stages');

const tabs = [
    { id: 'stages', label: 'Этапы' },
    { id: 'files', label: 'Файлы' },
    { id: 'general', label: 'Общие сведения' },
    { id: 'stages-info', label: 'Информация об этапах' },
];

const switchTab = (tabId: string) => {
    activeTab.value = tabId;
};

const closeModal = () => {
    emit('close');
};

const handleEscapeKey = (event: KeyboardEvent) => {
    if ((event.key === 'Escape' || event.keyCode === 27) && props.isOpen) {
        closeModal();
        event.preventDefault();
        event.stopPropagation();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleEscapeKey);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleEscapeKey);
});

const firstYearStagesList = computed(() => {
    if (!props.item?.first_year_stages) return [];
    return props.item.first_year_stages.split('\n').filter(s => s.trim());
});

const subsequentYearsStagesList = computed(() => {
    if (!props.item?.subsequent_years_stages) return [];
    return props.item.subsequent_years_stages.split('\n').filter(s => s.trim());
});

const allStagesList = computed(() => {
    return [...firstYearStagesList.value, ...subsequentYearsStagesList.value];
});

const editableStages = ref<Array<{
    id: number;
    name: string;
    start_date: string;
    end_date: string;
    amount: string;
}>>([]);

watch(() => [props.item, allStagesList.value], ([newItem, stagesNames]) => {
    const item = newItem as PlannedListItem | null;
    const stages = Array.isArray(stagesNames) ? stagesNames : [];
    
    if (!item || stages.length === 0) {
        editableStages.value = [];
        return;
    }
    
    editableStages.value = stages.map((stageName: string) => {
        const stageFromPivot = item.stages?.find((s: any) => s.name === stageName);
        
        const stageId = stageFromPivot?.id || null;
        
        return {
            id: stageId || 0,
            name: stageName,
            start_date: stageFromPivot?.pivot?.start_date || '',
            end_date: stageFromPivot?.pivot?.end_date || '',
            amount: (stageFromPivot?.pivot?.amount != null && stageFromPivot?.pivot?.amount !== undefined) ? String(stageFromPivot.pivot.amount) : ''
        };
    });
}, { immediate: true });

const stagesForm = useForm({
    development_name: '',
    stages: [] as Array<{
        stage_id: number;
        start_date: string;
        end_date: string;
        amount: string;
    }>
});

const saveStages = () => {
    if (!props.item?.id) return;
    
    const stagesToSave: Array<{
        stage_id: number;
        start_date: string;
        end_date: string;
        amount: string;
    }> = [];
    
    for (const editableStage of editableStages.value) {
        let stageId = editableStage.id;
        
        if (!stageId || stageId === 0) {
            const stageFromPivot = props.item?.stages?.find((s: any) => s.name === editableStage.name);
            if (stageFromPivot) {
                stageId = stageFromPivot.id;
            } else {
                const stageFromProps = props.stages?.find(s => s.name === editableStage.name);
                if (stageFromProps) {
                    stageId = stageFromProps.id;
                }
            }
        }
        
        if (stageId && stageId > 0) {
            stagesToSave.push({
                stage_id: stageId,
                start_date: editableStage.start_date || '',
                end_date: editableStage.end_date || '',
                amount: editableStage.amount || ''
            });
        }
    }
    
    if (stagesToSave.length === 0) {
        alert('Не удалось найти ID этапов для сохранения. Убедитесь, что этапы существуют в системе.');
        return;
    }
    
    stagesForm.development_name = props.item?.development_name || '';
    stagesForm.stages = stagesToSave;
    
    stagesForm.put(`/planned-list/${props.item.id}`, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            window.location.reload();
        },
        onError: (errors) => {
            console.error('Ошибка сохранения этапов:', errors);
            let errorMessage = 'Ошибка при сохранении этапов';
            if (errors && typeof errors === 'object') {
                const errorKeys = Object.keys(errors);
                if (errorKeys.length > 0) {
                    errorMessage += ': ' + errorKeys.map(key => `${key}: ${Array.isArray(errors[key]) ? errors[key].join(', ') : errors[key]}`).join('; ');
                }
            } else if (typeof errors === 'string') {
                errorMessage += ': ' + errors;
            }
            alert(errorMessage);
        }
    });
};

const stageParametersMapping: Record<string, Array<{ label: string; field: 'start_date' | 'end_date' | 'amount' }>> = {
    'ТЗ': [
        { label: 'Дата утверждения ТЗ', field: 'start_date' }
    ],
    'ПР': [
        { label: 'Письмо на отзыв по ПР', field: 'start_date' },
        { label: 'Дата размещения на сайте ПР', field: 'start_date' },
        { label: 'Дата уведомления о ПР', field: 'end_date' }
    ],
    'ОР': [
        { label: 'Дата уведомления о завершении', field: 'start_date' },
        { label: 'Дата размещения на сайте СО и ОР', field: 'start_date' },
        { label: 'Письмо на согласование ОР', field: 'end_date' }
    ],
    'НТЭ': [
        { label: 'Дата направления на НТЭ', field: 'start_date' }
    ],
    'ИР': [
        { label: 'Дата направления ОР в 15 отдел', field: 'start_date' }
    ],
    'МВС': [
        { label: 'Дата направления на МВС', field: 'start_date' }
    ],
    'ВЭ': [
        { label: 'Дата отправления на ВЭ', field: 'start_date' }
    ],
    'У': [
        { label: 'Дата утверждения', field: 'start_date' }
    ],
    'С': [
        { label: 'Дата утверждения', field: 'start_date' }
    ],
    'БелГИСС': [
        { label: 'Дата утверждения', field: 'start_date' }
    ],
    'ПЕР': [
        { label: 'Дата утверждения', field: 'start_date' }
    ],
    'НЦЗПИ': [
        { label: 'Дата утверждения', field: 'start_date' }
    ],
    'И': [
        { label: 'Дата утверждения', field: 'start_date' }
    ]
};

const stageParametersList = computed(() => {
    return [
        { stage: 'ТЗ', parameter: 'Дата утверждения ТЗ', value: '22.10.2025' },
        { stage: 'ПР', parameter: 'Письмо на отзыв по ПР', value: '—' },
        { stage: 'ПР', parameter: 'Дата размещения на сайте ПР', value: '23.10.2025' },
        { stage: 'ПР', parameter: 'Дата уведомления о ПР', value: '22.10.2025' },
        { stage: 'ОР', parameter: 'Дата уведомления о завершении', value: '28.10.2025' },
        { stage: 'ОР', parameter: 'Дата размещения на сайте СО и ОР', value: '22.10.2025' },
        { stage: 'ОР', parameter: 'Письмо на согласование ОР', value: '—' },
        { stage: 'НТЭ', parameter: 'Дата направления на НТЭ', value: '22.10.2025' },
        { stage: 'ИР', parameter: 'Дата направления ОР в 15 отдел', value: '22.10.2025' },
        { stage: 'МВС', parameter: 'Дата направления на МВС', value: '22.10.2025' },
        { stage: 'ВЭ', parameter: 'Дата отправления на ВЭ', value: '22.10.2025' },
        { stage: 'У', parameter: 'Дата утверждения', value: '22.10.2025' }
    ];
});

watch(() => props.item, (newItem: PlannedListItem | null) => {
    if (newItem) {
        console.log('PlannedListItemDetailModal - item:', newItem);
        console.log('PlannedListItemDetailModal - stages:', newItem.stages);
        if (newItem.stages) {
            newItem.stages.forEach((stage, index: number) => {
                console.log(`Stage ${index}:`, stage.name, 'pivot:', stage.pivot);
            });
        }
    }
}, { immediate: true });
</script>

<template>
    <div v-if="isOpen && item" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" @click="closeModal">
        <div class="bg-white rounded-2xl p-6 max-w-6xl w-full max-h-[95vh] overflow-y-auto shadow-2xl" @click.stop>
            <!-- Заголовок -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-[#080D6E] text-xl font-bold">
                    {{ item.development_name || item.code || 'Детали записи' }}
                </h2>
                <button @click="closeModal" class="text-[#080D6E] hover:text-gray-600 text-2xl font-bold leading-none w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">
                    ×
                </button>
            </div>

            <!-- Табы -->
            <div class="border-b border-gray-200 mb-6">
                <div class="flex gap-2">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="switchTab(tab.id)"
                        :class="[
                            'px-6 py-3 font-medium transition-colors border-b-2',
                            activeTab === tab.id
                                ? 'text-[#FFB800] border-[#FFB800]'
                                : 'text-gray-500 border-transparent hover:text-[#080D6E] hover:border-gray-300'
                        ]"
                    >
                        {{ tab.label }}
                    </button>
                </div>
            </div>

            <!-- Содержимое табов -->
            <div class="min-h-[400px]">
                <!-- Таб: Этапы -->
                <div v-if="activeTab === 'stages'" class="space-y-6">
                    <div v-if="allStagesList.length === 0" class="text-center py-12 text-gray-400">
                        <p class="text-lg mb-2">Нет информации об этапах</p>
                        <p class="text-sm">Этапы не добавлены к данной записи</p>
                    </div>
                    <div v-else>
                        <!-- Таблица этапов с возможностью редактирования -->
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <!-- Заголовок таблицы -->
                                <thead>
                                    <tr class="bg-[#FFB800] text-white">
                                        <th class="px-4 py-3 text-left font-semibold text-sm border border-white border-opacity-20">Этап</th>
                                        <th class="px-4 py-3 text-left font-semibold text-sm border border-white border-opacity-20">Начало этапа</th>
                                        <th class="px-4 py-3 text-left font-semibold text-sm border border-white border-opacity-20">Окончание этапа</th>
                                        <th class="px-4 py-3 text-left font-semibold text-sm border border-white border-opacity-20">Сумма</th>
                                    </tr>
                                </thead>
                                <!-- Тело таблицы -->
                                <tbody>
                                    <tr
                                        v-for="(editableStage, index) in editableStages"
                                        :key="editableStage.id"
                                        :class="[
                                            'border-b border-gray-200',
                                            index % 2 === 0 ? 'bg-blue-50' : 'bg-white'
                                        ]"
                                    >
                                        <td class="px-4 py-3 text-[#080D6E] font-medium">{{ editableStage.name }}</td>
                                        <td class="px-4 py-3">
                                            <input
                                                v-model="editableStage.start_date"
                                                type="date"
                                                class="w-full px-3 py-2 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none text-[#080D6E] text-sm"
                                                placeholder="—"
                                            />
                                        </td>
                                        <td class="px-4 py-3">
                                            <input
                                                v-model="editableStage.end_date"
                                                type="date"
                                                class="w-full px-3 py-2 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none text-[#080D6E] text-sm"
                                                placeholder="—"
                                            />
                                        </td>
                                        <td class="px-4 py-3">
                                            <input
                                                v-model="editableStage.amount"
                                                type="number"
                                                step="0.01"
                                                class="w-full px-3 py-2 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none text-[#080D6E] text-sm"
                                                placeholder="0.00"
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- Кнопка сохранения -->
                        <div class="flex justify-end pt-4">
                            <button
                                @click="saveStages"
                                :disabled="stagesForm.processing"
                                class="px-6 py-2 bg-[#FFB800] text-white rounded-lg font-medium hover:bg-[#E6A600] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="stagesForm.processing">Сохранение...</span>
                                <span v-else>Сохранить изменения</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Таб: Файлы -->
                <div v-if="activeTab === 'files'" class="space-y-4">
                    <div class="text-center py-12 text-gray-400">
                        <p class="text-lg mb-2">Файлы</p>
                        <p class="text-sm">Функционал в разработке</p>
                    </div>
                </div>

                <!-- Таб: Общие сведения -->
                <div v-if="activeTab === 'general'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Шифр</label>
                            <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 text-[#080D6E]">
                                {{ item.code || '—' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Тип документа</label>
                            <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 text-[#080D6E]">
                                {{ item.document_type || '—' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Тип разработки</label>
                            <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 text-[#080D6E]">
                                {{ item.development_type || '—' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Количество страниц</label>
                            <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 text-[#080D6E]">
                                {{ item.page_count || '—' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Начало разработки</label>
                            <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 text-[#080D6E]">
                                {{ item.development_start || item.start_date || '—' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Окончание разработки</label>
                            <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 text-[#080D6E]">
                                {{ item.development_end || item.end_date || '—' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Автор</label>
                            <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 text-[#080D6E]">
                                {{ item.author || '—' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Отдел</label>
                            <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 text-[#080D6E]">
                                {{ item.department || '—' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Блок</label>
                            <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 text-[#080D6E]">
                                {{ item.block || '—' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Стоимость</label>
                            <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 text-[#080D6E]">
                                {{ item.cost ? (typeof item.cost === 'number' ? item.cost.toLocaleString() : item.cost) : '—' }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[#080D6E] text-sm font-medium mb-2">Наименование разработки</label>
                        <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 text-[#080D6E] min-h-[60px]">
                            {{ item.development_name || '—' }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-[#080D6E] text-sm font-medium mb-2">Наименование организаций, выполняющих работу</label>
                        <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 text-[#080D6E] min-h-[60px]">
                            {{ item.organizations || '—' }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-[#080D6E] text-sm font-medium mb-2">Разрабатывается взамен действующих нормативных документов</label>
                        <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 text-[#080D6E] min-h-[60px]">
                            {{ item.regulatory_documents || '—' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Стоимость всего</label>
                            <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 text-[#080D6E]">
                                {{ item.total_cost != null ? item.total_cost.toLocaleString() : '—' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Стоимость на 2025</label>
                            <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 text-[#080D6E]">
                                {{ item.cost_2025 != null ? item.cost_2025.toLocaleString() : '—' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Стоимость на 2026</label>
                            <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 text-[#080D6E]">
                                {{ item.cost_2026 != null ? item.cost_2026.toLocaleString() : '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Таб: Информация об этапах -->
                <div v-if="activeTab === 'stages-info'" class="space-y-6">
                    <!-- Таблица параметров этапов -->
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <!-- Заголовок таблицы -->
                            <thead>
                                <tr class="bg-[#FFB800] text-white">
                                    <th class="px-4 py-3 text-left font-semibold text-sm border border-white border-opacity-20">Этап</th>
                                    <th class="px-4 py-3 text-left font-semibold text-sm border border-white border-opacity-20">Параметр</th>
                                    <th class="px-4 py-3 text-left font-semibold text-sm border border-white border-opacity-20">Значение</th>
                                </tr>
                            </thead>
                            <!-- Тело таблицы -->
                            <tbody>
                                <tr
                                    v-for="(param, index) in stageParametersList"
                                    :key="`${param.stage}-${param.parameter}-${index}`"
                                    :class="[
                                        'border-b border-gray-200',
                                        index % 2 === 0 ? 'bg-blue-50' : 'bg-white'
                                    ]"
                                >
                                    <td class="px-4 py-3 text-[#080D6E] font-medium">{{ param.stage }}</td>
                                    <td class="px-4 py-3 text-[#080D6E]">{{ param.parameter }}</td>
                                    <td class="px-4 py-3 text-[#080D6E]">{{ param.value }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Кнопка закрытия -->
            <div class="flex justify-end gap-4 pt-6 border-t border-gray-300 mt-6">
                <button 
                    type="button"
                    @click="closeModal"
                    class="px-8 py-3 bg-[#FFB800] text-white rounded-lg font-medium hover:bg-[#E6A600] transition-colors"
                >
                    Закрыть
                </button>
            </div>
        </div>
    </div>
</template>

