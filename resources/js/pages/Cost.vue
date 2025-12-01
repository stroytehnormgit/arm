<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import CrossIcon from '@/components/icons/CrossIcon.vue';
import PencilIcon from '@/components/icons/PencilIcon.vue';
import TrashIcon from '@/components/icons/TrashIcon.vue';

interface CostItem {
    id: number;
    year: number;
    average_monthly_salary: number | null;
    document_volume_coefficient: number | null;
    mandatory_payments_qn: number | null;
    overhead_costs_qnr: number | null;
    profit_qp: number | null;
    other_expenses_qpr: number | null;
    review_cost_sp: number | null;
}

interface Props {
    costs?: {
        data: CostItem[];
        links: any[];
        meta: any;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Стоимость',
        href: '/cost',
    },
];

const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const selectedItem = ref<CostItem | null>(null);

const selectedItemId = ref<number | null>(null);

const form = useForm({
    year: new Date().getFullYear(),
    average_monthly_salary: '',
    document_volume_coefficient: '1',
    mandatory_payments_qn: '',
    overhead_costs_qnr: '',
    profit_qp: '',
    other_expenses_qpr: '',
    review_cost_sp: '',
});

const openAddModal = () => {
    form.reset();
    form.year = new Date().getFullYear();
    form.document_volume_coefficient = '1';
    isAddModalOpen.value = true;
};

const closeModals = () => {
    isAddModalOpen.value = false;
    isEditModalOpen.value = false;
    selectedItem.value = null;
    selectedItemId.value = null;
    form.reset();
};

const selectItem = (itemId: number) => {
    selectedItemId.value = selectedItemId.value === itemId ? null : itemId;
};

const selectedItemComputed = computed(() => {
    if (!selectedItemId.value) return null;
    return props.costs?.data.find(item => item.id === selectedItemId.value) || null;
});

const openEditModal = () => {
    if (selectedItemId.value && selectedItemComputed.value) {
        const item = selectedItemComputed.value;
        selectedItem.value = item;
        form.year = item.year;
        form.average_monthly_salary = (item.average_monthly_salary !== null && item.average_monthly_salary !== undefined) ? String(item.average_monthly_salary) : '';
        form.document_volume_coefficient = (item.document_volume_coefficient !== null && item.document_volume_coefficient !== undefined) ? String(item.document_volume_coefficient) : '1';
        form.mandatory_payments_qn = (item.mandatory_payments_qn !== null && item.mandatory_payments_qn !== undefined) ? String(item.mandatory_payments_qn) : '';
        form.overhead_costs_qnr = (item.overhead_costs_qnr !== null && item.overhead_costs_qnr !== undefined) ? String(item.overhead_costs_qnr) : '';
        form.profit_qp = (item.profit_qp !== null && item.profit_qp !== undefined) ? String(item.profit_qp) : '';
        form.other_expenses_qpr = (item.other_expenses_qpr !== null && item.other_expenses_qpr !== undefined) ? String(item.other_expenses_qpr) : '';
        form.review_cost_sp = (item.review_cost_sp !== null && item.review_cost_sp !== undefined) ? String(item.review_cost_sp) : '';
        isEditModalOpen.value = true;
    }
};

const submitForm = () => {
    if (selectedItem.value) {
        form.put(`/cost/${selectedItem.value.id}`, {
            onSuccess: () => {
                closeModals();
            },
        });
    } else {
        form.post('/cost', {
            onSuccess: () => {
                closeModals();
            },
        });
    }
};

const deleteItem = () => {
    if (!selectedItemId.value) return;
    const item = props.costs?.data.find(i => i.id === selectedItemId.value);
    const itemName = item?.year || 'эту запись';
    
    if (confirm(`Вы уверены, что хотите удалить запись за ${itemName} год?`)) {
        form.delete(`/cost/${selectedItemId.value}`, {
            onSuccess: () => {
                selectedItemId.value = null;
            },
        });
    }
};

const formatNumber = (value: number | null | undefined): string => {
    if (value === null || value === undefined) return '—';
    return value.toLocaleString('ru-RU', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};
</script>

<template>
    <Head title="Стоимость" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl">
            <div class="flex gap-4 items-center mb-4">
                <button 
                    @click="openAddModal" 
                    class="flex-1 min-h-[83px] rounded-xl px-4 py-3 text-white font-medium flex items-center justify-center gap-2" 
                    style="background-color: rgba(255, 253, 253, 0.2);"
                >
                    <CrossIcon class="w-5 h-5" />
                    ДОБАВИТЬ ДАННЫЕ
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
                    @click="deleteItem"
                    :disabled="!selectedItemId"
                    :class="[
                        'flex-1 min-h-[83px] rounded-xl px-4 py-3 text-white font-medium flex items-center justify-center gap-2',
                        selectedItemId ? 'cursor-pointer hover:opacity-90' : 'opacity-50 cursor-not-allowed'
                    ]"
                    style="background-color: rgba(220, 38, 38, 0.2);"
                >
                    <TrashIcon class="w-6 h-6 sm:w-8 sm:h-8 text-[#FFB800]" />
                    <span class="hidden sm:inline">УДАЛИТЬ</span>
                </button>
            </div>

            <div class="flex-1 rounded-xl border-gray-400 overflow-hidden">
                <div class="overflow-x-auto">
                    <div>
                        <div class="bg-[#FFB800] rounded-2xl px-4 py-3 sticky top-0 z-10">
                            <div class="grid gap-4 font-semibold table-header-text table-header-container table-header-dividers" style="grid-template-columns: 80px repeat(7, 1fr);">
                                <div>Год</div>
                                <div>Среднемесячная заработная плата</div>
                                <div>Коэффициент, учитывающий объем документа</div>
                                <div>Обязательные платежи (Qн)</div>
                                <div>Накладные расходы организации-исполнителя(Qнр)</div>
                                <div>Прибыль Q(п)</div>
                                <div>Прочие расходы организации-исполнителя(Qпр)</div>
                                <div>Стоимость одного отзыва (Сп)</div>
                            </div>
                        </div>
                        
                        <div v-if="!props.costs || props.costs.data.length === 0" class="text-center py-8 text-white">
                            Нет данных для отображения
                        </div>
                        <div v-else>
                            <div 
                                v-for="(item, index) in props.costs.data" 
                                :key="item.id"
                                @click="selectItem(item.id)"
                                :class="[
                                    index % 2 === 0 ? 'bg-[#F1EFF4]' : 'bg-[#F1EFF4BF]',
                                    selectedItemId === item.id ? 'ring-4 ring-[#FFB800] ring-opacity-60 shadow-lg' : 
                                    'hover:ring-2 hover:ring-[#FFB800] hover:ring-opacity-30'
                                ]"
                                class="rounded-2xl px-4 py-3 table-row-spacing mb-3 cursor-pointer transition-all"
                            >
                                <div class="grid gap-4 text-[#080D6E] table-row-align" style="grid-template-columns: 80px repeat(7, 1fr);">
                                    <div>{{ item.year }}</div>
                                    <div>{{ formatNumber(item.average_monthly_salary) }}</div>
                                    <div>{{ formatNumber(item.document_volume_coefficient) }}</div>
                                    <div>{{ formatNumber(item.mandatory_payments_qn) }}</div>
                                    <div>{{ formatNumber(item.overhead_costs_qnr) }}</div>
                                    <div>{{ formatNumber(item.profit_qp) }}</div>
                                    <div>{{ formatNumber(item.other_expenses_qpr) }}</div>
                                    <div>{{ formatNumber(item.review_cost_sp) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div 
            v-if="isAddModalOpen || isEditModalOpen" 
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
            @click="closeModals"
        >
            <div 
                class="bg-white rounded-2xl p-6 max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-2xl"
                @click.stop
            >
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-[#080D6E]">
                        {{ selectedItem ? 'Редактировать данные' : 'Добавить данные' }}
                    </h2>
                    <button 
                        @click="closeModals"
                        class="text-[#080D6E] hover:text-gray-600 text-2xl font-bold leading-none w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors"
                    >
                        ×
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label class="block text-[#080D6E] text-sm font-medium mb-2">Год *</label>
                        <input 
                            v-model.number="form.year"
                            type="number" 
                            required
                            min="2000"
                            max="2100"
                            class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                        />
                        <div v-if="form.errors.year" class="text-red-500 text-sm mt-1">{{ form.errors.year }}</div>
                    </div>

                    <div>
                        <label class="block text-[#080D6E] text-sm font-medium mb-2">Среднемесячная заработная плата</label>
                        <input 
                            v-model="form.average_monthly_salary"
                            type="number" 
                            step="any"
                            min="0"
                            class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                            placeholder="Введите сумму в BYN"
                        />
                        <div v-if="form.errors.average_monthly_salary" class="text-red-500 text-sm mt-1">{{ form.errors.average_monthly_salary }}</div>
                    </div>

                    <div>
                        <label class="block text-[#080D6E] text-sm font-medium mb-2">Коэффициент, учитывающий объем документа</label>
                        <input 
                            v-model="form.document_volume_coefficient"
                            type="number" 
                            step="any"
                            min="0"
                            class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                            placeholder="Введите коэффициент"
                        />
                        <div v-if="form.errors.document_volume_coefficient" class="text-red-500 text-sm mt-1">{{ form.errors.document_volume_coefficient }}</div>
                    </div>

                    <div>
                        <label class="block text-[#080D6E] text-sm font-medium mb-2">Обязательные платежи (Qн)</label>
                        <input 
                            v-model="form.mandatory_payments_qn"
                            type="number" 
                            step="any"
                            min="0"
                            class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                            placeholder="Введите сумму в BYN"
                        />
                        <div v-if="form.errors.mandatory_payments_qn" class="text-red-500 text-sm mt-1">{{ form.errors.mandatory_payments_qn }}</div>
                    </div>

                    <div>
                        <label class="block text-[#080D6E] text-sm font-medium mb-2">Накладные расходы организации-исполнителя(Qнр)</label>
                        <input 
                            v-model="form.overhead_costs_qnr"
                            type="number" 
                            step="any"
                            min="0"
                            class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                            placeholder="Введите сумму в BYN"
                        />
                        <div v-if="form.errors.overhead_costs_qnr" class="text-red-500 text-sm mt-1">{{ form.errors.overhead_costs_qnr }}</div>
                    </div>

                    <div>
                        <label class="block text-[#080D6E] text-sm font-medium mb-2">Прибыль Q(п)</label>
                        <input 
                            v-model="form.profit_qp"
                            type="number" 
                            step="any"
                            min="0"
                            class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                            placeholder="Введите сумму в BYN"
                        />
                        <div v-if="form.errors.profit_qp" class="text-red-500 text-sm mt-1">{{ form.errors.profit_qp }}</div>
                    </div>

                    <div>
                        <label class="block text-[#080D6E] text-sm font-medium mb-2">Прочие расходы организации-исполнителя(Qпр)</label>
                        <input 
                            v-model="form.other_expenses_qpr"
                            type="number" 
                            step="any"
                            min="0"
                            class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                            placeholder="Введите сумму в BYN"
                        />
                        <div v-if="form.errors.other_expenses_qpr" class="text-red-500 text-sm mt-1">{{ form.errors.other_expenses_qpr }}</div>
                    </div>

                    <div>
                        <label class="block text-[#080D6E] text-sm font-medium mb-2">Стоимость одного отзыва (Сп)</label>
                        <input 
                            v-model="form.review_cost_sp"
                            type="number" 
                            step="any"
                            min="0"
                            class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                            placeholder="Введите сумму в BYN"
                        />
                        <div v-if="form.errors.review_cost_sp" class="text-red-500 text-sm mt-1">{{ form.errors.review_cost_sp }}</div>
                    </div>

                    <div class="flex justify-end gap-4 pt-4">
                        <button 
                            type="button"
                            @click="closeModals"
                            class="px-8 py-3 bg-gray-200 text-[#080D6E] rounded-lg font-medium hover:bg-gray-300 transition-colors"
                        >
                            Отмена
                        </button>
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="px-8 py-3 bg-[#FFB800] text-white rounded-lg font-medium hover:bg-[#E6A600] transition-colors disabled:opacity-50"
                        >
                            <span v-if="form.processing">Сохранение...</span>
                            <span v-else>{{ selectedItem ? 'Обновить' : 'Сохранить' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
