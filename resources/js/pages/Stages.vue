<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import CrossIcon from '@/components/icons/CrossIcon.vue';
import TrashIcon from '@/components/icons/TrashIcon.vue';

interface Stage {
    id: number;
    name: string;
    created_at: string;
}

interface Props {
    stages: {
        data: Stage[];
        links: any[];
        meta: any;
    };
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => {
    const items: BreadcrumbItem[] = [
        {
            title: 'Этапы',
            href: '/stages',
        },
    ];
    
    const previousLink = props.stages.links.find(link => 
        link.label && (link.label.includes('Previous') || link.label.includes('&laquo;'))
    );
    const nextLink = props.stages.links.find(link => 
        link.label && (link.label.includes('Next') || link.label.includes('&raquo;'))
    );
    
    if (previousLink && previousLink.url) {
        items.push({
            title: 'Предыдущая',
            href: previousLink.url,
        });
    }
    
    if (nextLink && nextLink.url) {
        items.push({
            title: 'Следующая',
            href: nextLink.url,
        });
    }
    
    return items;
});

const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const selectedStage = ref<Stage | null>(null);

const form = useForm({
    name: '',
});

const openAddModal = () => {
    form.reset();
    isAddModalOpen.value = true;
};

const closeModals = () => {
    isAddModalOpen.value = false;
    isEditModalOpen.value = false;
    selectedStage.value = null;
    form.reset();
};

const openEditModal = (stage: Stage) => {
    selectedStage.value = stage;
    form.name = stage.name;
    isEditModalOpen.value = true;
};

const submitForm = () => {
    if (selectedStage.value) {
        form.put(`/stages/${selectedStage.value.id}`, {
            onSuccess: () => {
                closeModals();
            },
        });
    } else {
        form.post('/stages', {
            onSuccess: () => {
                closeModals();
            },
        });
    }
};

const deleteStage = (stageId: number) => {
    if (confirm('Вы уверены, что хотите удалить этот этап?')) {
        form.delete(`/stages/${stageId}`, {
            onSuccess: () => {
            },
        });
    }
};
const searchForm = useForm({
    search: props.filters.search || '',
});

const applySearch = () => {
    searchForm.get('/stages', {
        preserveState: true,
    });
};

const clearSearch = () => {
    searchForm.search = '';
    searchForm.get('/stages', {
        preserveState: true,
    });
};
</script>

<template>
    <Head title="Этапы" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl">
            <div class="flex gap-4 items-center mb-4">
                <button 
                    @click="openAddModal" 
                    class="min-h-[83px] rounded-xl px-4 py-3 text-white font-medium flex items-center justify-center gap-2 whitespace-nowrap" 
                    style="background-color: rgba(255, 253, 253, 0.2);"
                >
                    <CrossIcon class="w-5 h-5" />
                    ДОБАВИТЬ ЭТАП
                </button>

                <div class="flex gap-2 flex-1">
                    <input 
                        v-model="searchForm.search"
                        @keyup.enter="applySearch"
                        type="text" 
                        placeholder="Поиск по названию этапа или датам..."
                        class="flex-1 px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none"
                    />
                    <button 
                        @click="applySearch"
                        class="px-6 py-3 bg-[#FFB800] text-white rounded-xl font-medium hover:bg-[#E6A600] transition-colors whitespace-nowrap"
                    >
                        Найти
                    </button>
                    <button 
                        v-if="searchForm.search"
                        @click="clearSearch"
                        class="px-6 py-3 bg-gray-500 text-white rounded-xl font-medium hover:bg-gray-600 transition-colors whitespace-nowrap"
                    >
                        Сброс
                    </button>
                </div>
            </div>

            <div class="flex-1 rounded-xl border-gray-400 overflow-hidden flex flex-col">
                <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-300px)]">
                    <div>
                        <div class="bg-[#FFB800] rounded-2xl px-4 py-3 sticky top-0 z-10">
                            <div class="grid grid-cols-2 gap-4 font-semibold table-header-text table-header-container table-header-dividers">
                                <div>Этап</div>
                                <div>Действия</div>
                            </div>
                        </div>
                        
                        <div v-if="props.stages.data.length === 0" class="text-center py-8 text-white">
                            Нет этапов для отображения
                        </div>
                        <div v-else>
                            <div 
                                v-for="(stage, index) in props.stages.data" 
                                :key="stage.id" 
                                :class="[
                                    index % 2 === 0 ? 'bg-[#F1EFF4]' : 'bg-[#F1EFF4BF]',
                                    'hover:ring-2 hover:ring-[#FFB800] hover:ring-opacity-30'
                                ]" 
                                class="rounded-2xl px-4 py-3 table-row-spacing mb-3 transition-all"
                            >
                                <div class="grid grid-cols-2 gap-4 text-[#080D6E] table-row-align">
                                    <div>{{ stage.name }}</div>
                                    <div>
                                        <div class="flex gap-2 justify-center">
                                            <button 
                                                @click="openEditModal(stage)"
                                                class="bg-[#FFB800] text-white rounded hover:bg-[#E6A600] transition-colors flex items-center justify-center gap-2 text-center max-w-[250px]"
                                                style="font-size: 0.975rem; padding: 0.325rem 0.975rem;"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="black" class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>
                                                Редактировать
                                            </button>
                                            <button 
                                                @click="deleteStage(stage.id)"
                                                class="bg-[#FFB800] text-white rounded hover:bg-[#E6A600] transition-colors flex items-center justify-center gap-2 text-center max-w-[250px]"
                                                style="font-size: 0.975rem; padding: 0.325rem 0.975rem;"
                                            >
                                                <TrashIcon class="w-5 h-5 text-black" />
                                                Удалить
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="props.stages.links.length > 3" class="flex justify-center items-center gap-2 mt-4">
                    <template v-for="(link, index) in props.stages.links" :key="index">
                        <a 
                            v-if="link.url && link.label && !link.label.includes('Previous') && !link.label.includes('Next') && !link.label.includes('&laquo;') && !link.label.includes('&raquo;')"
                            :href="link.url"
                            v-html="link.label"
                            :class="[
                                'px-4 py-2 rounded-lg',
                                link.active ? 'bg-[#FFB800] text-white font-semibold' : 'bg-gray-200 text-[#080D6E] hover:bg-gray-300'
                            ]"
                        />
                        <span 
                            v-else-if="link.label && !link.label.includes('Previous') && !link.label.includes('Next') && !link.label.includes('&laquo;') && !link.label.includes('&raquo;')"
                            v-html="link.label"
                            class="px-4 py-2 text-gray-400"
                        />
                    </template>
                </div>
            </div>
        </div>

        <div 
            v-if="isAddModalOpen || isEditModalOpen" 
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
            @click="closeModals"
        >
            <div 
                class="bg-white rounded-2xl p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl"
                @click.stop
            >
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-[#080D6E]">
                        {{ selectedStage ? 'Редактировать этап' : 'Добавить этап' }}
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
                        <label class="block text-[#080D6E] text-sm font-medium mb-2">Этап *</label>
                        <input 
                            v-model="form.name"
                            type="text" 
                            required
                            placeholder="Например: Т3"
                            class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                        />
                        <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                    </div>


                    <div class="flex justify-end gap-4 pt-4">
                        <button 
                            type="button"
                            @click="closeModals"
                            class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-400 transition-colors"
                        >
                            Отмена
                        </button>
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-3 bg-[#FFB800] text-white rounded-lg font-medium hover:bg-[#E6A600] transition-colors disabled:opacity-50"
                        >
                            {{ form.processing ? 'Сохранение...' : (selectedStage ? 'Обновить' : 'Создать') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

