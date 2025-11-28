<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

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
    isOpen: boolean;
    item: ActiveListItem | null;
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
                    <div>
                        <h3 class="text-[#080D6E] text-lg font-semibold mb-4">Текущий этап</h3>
                        <div v-if="!item.current_stage" class="text-gray-400 text-sm text-center py-8 bg-gray-50 rounded-lg">
                            Этап не указан
                        </div>
                        <div v-else class="space-y-2">
                            <div class="flex items-center p-3 rounded-lg border bg-[#FFB800] bg-opacity-10 border-[#FFB800] border-opacity-30">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-[#FFB800] opacity-50"></div>
                                    <span class="text-sm text-[#080D6E] font-medium">{{ item.current_stage }}</span>
                                </div>
                            </div>
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
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Тип разработки</label>
                            <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 text-[#080D6E]">
                                {{ item.development_type || '—' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Текущий этап</label>
                            <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 text-[#080D6E]">
                                {{ item.current_stage || '—' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Срок начала</label>
                            <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 text-[#080D6E]">
                                {{ item.start_date || '—' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Срок окончания</label>
                            <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 text-[#080D6E]">
                                {{ item.end_date || '—' }}
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

