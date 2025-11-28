<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import CrossIcon from '@/components/icons/CrossIcon.vue';
import DownloadIcon from '@/components/icons/DownloadIcon.vue';
import EyeIcon from '@/components/icons/EyeIcon.vue';
import { ref, watch } from 'vue';

interface UploadedFile {
    id: number | string;
    date: string;
    type: string;
    name: string;
    purpose: string;
    author: string;
    source?: 'local' | 'stnby';
    project_code?: string;
    filename?: string;
    download_url?: string;
    file_size?: number;
}

interface SiteFile {
    id: number;
    placement_date: string;
    file_type: string;
    development_name: string;
}

interface MvsFile {
    id: number;
    placement_date: string;
    development_name: string;
}

interface Props {
    activeTab: string;
    uploadedFiles: {
        data: UploadedFile[];
        links: any[];
        meta: any;
    };
    siteFiles: {
        data: SiteFile[];
        links: any[];
        meta: any;
    };
    mvsFiles: {
        data: MvsFile[];
        links: any[];
        meta: any;
    };
    filters: {
        name?: string;
        purpose?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Файлы',
        href: '/files',
    },
];

const activeTab = ref(props.activeTab);

const tabs = [
    { id: 'uploaded', label: 'Загруженные файлы' },
    { id: 'site', label: 'Размещены на сайте' },
    { id: 'mvs', label: 'Размещены на МВС' }
];

const switchTab = (tabId: string) => {
    activeTab.value = tabId;
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tabId);
    window.history.pushState({}, '', url.toString());
};

const filters = ref({
    name: props.filters.name || '',
    purpose: props.filters.purpose || '',
});

const filterForm = useForm({
    name: filters.value.name,
    purpose: filters.value.purpose,
});

const applyFilters = () => {
    const params = new URLSearchParams();
    params.set('tab', activeTab.value);
    
    if (filterForm.name && filterForm.name.trim()) {
        params.set('name', filterForm.name.trim());
    }
    if (filterForm.purpose) {
        params.set('purpose', filterForm.purpose);
    }
    
    filterForm.get(`/files?${params.toString()}`, {
        preserveState: true,
        preserveScroll: true,
    });
};

let debounceTimer: ReturnType<typeof setTimeout> | null = null;
const debouncedApplyFilters = () => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
    debounceTimer = setTimeout(() => {
        applyFilters();
    }, 500);
};

watch(
    () => filters.value.name,
    () => {
        filterForm.name = filters.value.name;
        debouncedApplyFilters();
    }
);

watch(
    () => filters.value.purpose,
    () => {
        filterForm.purpose = filters.value.purpose;
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }
        applyFilters();
    }
);

const getUploadedFiles = (): UploadedFile[] => props.uploadedFiles.data;
const getSiteFiles = (): SiteFile[] => props.siteFiles.data;
const getMvsFiles = (): MvsFile[] => props.mvsFiles.data;

const formatDate = (date: string): string => {
    if (!date) return '';
    try {
        const d = new Date(date);
        return d.toLocaleDateString('ru-RU', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
    } catch {
        return date;
    }
};

const isUploadModalOpen = ref(false);
const projects = ref<Array<{id: number, code: string, name: string, date_put?: string}>>([]);
const loadingProjects = ref(false);
const fileTypes = ref<Array<{id: number, name: string, affects_stage?: number, stage_effect?: string}>>([]);
const loadingFileTypes = ref(false);

const uploadForm = useForm({
    project_id: '',
    file_type: '',
    file: null as File | null,
});

const loadProjects = async () => {
    loadingProjects.value = true;
    try {
        const response = await fetch('/files/stnby/projects');
        const data = await response.json();
        
        if (data.success && data.projects) {
            projects.value = data.projects;
        }
    } catch (error) {
        console.error('Ошибка загрузки проектов:', error);
    } finally {
        loadingProjects.value = false;
    }
};

const loadFileTypes = async () => {
    loadingFileTypes.value = true;
    try {
        const response = await fetch('/files/stnby/filetypes');
        const data = await response.json();
        
        if (data.success && data.filetypes) {
            fileTypes.value = data.filetypes;
        }
    } catch (error) {
        console.error('Ошибка загрузки типов файлов:', error);
    } finally {
        loadingFileTypes.value = false;
    }
};

const openUploadModal = () => {
    isUploadModalOpen.value = true;
    loadProjects();
    loadFileTypes();
};

const closeUploadModal = () => {
    isUploadModalOpen.value = false;
    uploadForm.reset();
    uploadForm.clearErrors();
};

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        uploadForm.file = target.files[0];
    }
};

const submitUpload = () => {
    if (!uploadForm.project_id || !uploadForm.file_type || !uploadForm.file) {
        return;
    }

    uploadForm.post('/files/stnby/upload', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            closeUploadModal();
            router.reload({ only: ['uploadedFiles'] });
        },
        onError: (errors) => {
            console.error('Ошибки формы:', errors);
        },
    });
};
</script>

<template>
    <Head title="Файлы" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl min-h-0">
            <!-- Панель управления файлами -->
            <div class="grid gap-4 rounded-xl mb-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <!-- Поля ввода -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:col-span-2 filter-block p-4  filter_file">
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
                            <label class="block text-sm font-medium mb-2">Назначение</label>
                            <select class="w-full rounded-md border border-gray-300 px-3 py-2" v-model="filters.purpose">
                                <option value="">Выберите назначение</option>
                                <option value="На сайт">На сайт</option>
                                <option value="На МВС">На МВС</option>
                                <option value="В архив">В архив</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Кнопки действий -->
                    <div class="flex gap-4 md:col-span-2">
                        <button 
                            @click="openUploadModal"
                            class="flex-1 min-h-[83px] max-h-[83px] rounded-xl px-4 py-3 text-white font-medium flex items-center justify-center gap-2 hover:opacity-90 transition-opacity" 
                            style="background-color: rgba(255, 253, 253, 0.2);"
                        >
                            <CrossIcon class="w-5 h-5" />
                            ДОБАВИТЬ ФАЙЛ
                        </button>
                        <button class="flex-1 min-h-[83px] max-h-[83px] rounded-xl px-4 py-3 text-white font-medium flex items-center justify-center gap-2" style="background-color: rgba(255, 253, 253, 0.2);">
                            <DownloadIcon class="w-5 h-5" />
                            СКАЧАТЬ ФАЙЛ
                        </button>
                    </div>
                </div>
            </div>

            <!-- Табы переключатели -->
            <div class="flex gap-2 mb-4 flex-tabs-block">
                <button 
                    v-for="tab in tabs" 
                    :key="tab.id"
                    @click="switchTab(tab.id)"
                    class="flex-1 py-3 px-4 rounded-xl text-white font-medium transition-colors"
                    :style="{
                        backgroundColor: activeTab === tab.id 
                            ? 'rgba(255, 253, 253, 0.3)' 
                            : 'rgba(255, 253, 253, 0.2)'
                    }"
                >
                    {{ tab.label }}
                </button>
            </div>

            <!-- Список файлов -->
            <div class="flex-1 rounded-xl border-gray-400 overflow-hidden flex flex-col min-h-0">
                <div class="overflow-y-auto overflow-x-auto flex-1">
                    <div>
                        <!-- Заголовок таблицы -->
                        <div class="bg-[#FFB800] rounded-2xl px-4 py-3 sticky top-0 z-10">
                            <div v-if="activeTab === 'uploaded'" class="grid grid-cols-6 gap-4 font-semibold table-header-text table-header-container table-header-dividers">
                                <div>Дата</div>
                                <div>Тип файла</div>
                                <div>Наименование разработки</div>
                                <div>Назначение</div>
                                <div>Автор</div>
                                <div class="text-center">Действия</div>
                            </div>
                            <div v-else-if="activeTab === 'site'" class="grid grid-cols-3 gap-4 font-semibold table-header-text table-header-container table-header-dividers">
                                <div>Дата размещения</div>
                                <div>Тип файла</div>
                                <div>Наименование разработки</div>
                            </div>
                            <div v-else class="grid grid-cols-2 gap-4 font-semibold table-header-text table-header-container table-header-dividers">
                                <div>Дата размещения</div>
                                <div>Наименование разработки</div>
                            </div>
                        </div>
                        
                        <!-- Строки данных -->
                        <div v-if="activeTab === 'uploaded'">
                            <div v-if="getUploadedFiles().length === 0" class="text-center py-8 text-white">
                                Нет данных для отображения
                            </div>
                            <div v-else>
                                <div v-for="(file, index) in getUploadedFiles()" :key="file.id" :class="index % 2 === 0 ? 'bg-[#F1EFF4]' : 'bg-[#F1EFF4BF]'" class="rounded-2xl px-4 py-3 table-row-spacing mb-3">
                                    <div class="grid grid-cols-6 gap-4 text-[#080D6E] table-row-align">
                                        <div>{{ formatDate(file.date) }}</div>
                                        <div>{{ file.type }}</div>
                                        <div>
                                            <span v-if="file.project_code" class="text-xs text-gray-500 mr-1">
                                                [{{ file.project_code }}]
                                            </span>
                                            {{ file.name }}
                                        </div>
                                        <div>{{ file.purpose }}</div>
                                        <div>{{ file.author || 'Администратор' }}</div>
                                        <div class="flex items-center justify-center">
                                            <a 
                                                v-if="file.source === 'stnby' && file.download_url" 
                                                :href="file.download_url" 
                                                target="_blank"
                                                class="text-blue-600 hover:text-blue-800 transition-colors"
                                                title="Просмотреть файл"
                                            >
                                                <EyeIcon class="w-5 h-5" />
                                            </a>
                                            <span v-else class="text-gray-400">—</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-else-if="activeTab === 'site'">
                            <div v-if="getSiteFiles().length === 0" class="text-center py-8 text-white">
                                Нет данных для отображения
                            </div>
                            <div v-else>
                                <div v-for="(file, index) in getSiteFiles()" :key="file.id" :class="index % 2 === 0 ? 'bg-[#F1EFF4]' : 'bg-[#F1EFF4BF]'" class="rounded-2xl px-4 py-3 table-row-spacing mb-3">
                                    <div class="grid grid-cols-3 gap-4 text-[#080D6E] table-row-align">
                                        <div>{{ file.placement_date }}</div>
                                        <div>{{ file.file_type }}</div>
                                        <div>{{ file.development_name }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-else>
                            <div v-if="getMvsFiles().length === 0" class="text-center py-8 text-white">
                                Нет данных для отображения
                            </div>
                            <div v-else>
                                <div v-for="(file, index) in getMvsFiles()" :key="file.id" :class="index % 2 === 0 ? 'bg-[#F1EFF4]' : 'bg-[#F1EFF4BF]'" class="rounded-2xl px-4 py-3 table-row-spacing mb-3">
                                    <div class="grid grid-cols-2 gap-4 text-[#080D6E] table-row-align">
                                        <div>{{ file.placement_date }}</div>
                                        <div>{{ file.development_name }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно загрузки файла на stn.by -->
        <div 
            v-if="isUploadModalOpen" 
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" 
            @click="closeUploadModal"
        >
            <div 
                class="bg-white rounded-2xl p-6 max-w-2xl w-full max-h-[95vh] overflow-y-auto shadow-2xl" 
                @click.stop
            >
                <!-- Заголовок -->
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-[#080D6E] text-xl font-bold">
                        Загрузка файла на stn.by
                    </h2>
                    <button 
                        @click="closeUploadModal" 
                        class="text-[#080D6E] hover:text-gray-600 text-2xl font-bold leading-none w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors"
                    >
                        ×
                    </button>
                </div>

                <!-- Форма -->
                <form @submit.prevent="submitUpload" class="space-y-4">
                    <!-- Проект -->
                    <div>
                        <label class="block text-sm font-medium text-[#080D6E] mb-2">
                            Проект <span class="text-red-500">*</span>
                        </label>
                        <select 
                            v-model="uploadForm.project_id"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#FFB800]"
                            :disabled="loadingProjects"
                            required
                        >
                            <option value="">Выберите проект</option>
                            <option 
                                v-for="project in projects" 
                                :key="project.id" 
                                :value="project.id"
                            >
                                {{ project.code }} - {{ project.name }}{{ project.date_put ? ' (' + formatDate(project.date_put) + ')' : '' }}
                            </option>
                        </select>
                        <div v-if="uploadForm.errors.project_id" class="text-red-500 text-sm mt-1">
                            {{ uploadForm.errors.project_id }}
                        </div>
                        <div v-if="loadingProjects" class="text-gray-500 text-sm mt-1">
                            Загрузка проектов...
                        </div>
                    </div>

                    <!-- Тип файла -->
                    <div>
                        <label class="block text-sm font-medium text-[#080D6E] mb-2">
                            Тип файла <span class="text-red-500">*</span>
                        </label>
                        <select 
                            v-model="uploadForm.file_type"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#FFB800]"
                            :disabled="loadingFileTypes"
                            required
                        >
                            <option value="">Выберите тип файла</option>
                            <option 
                                v-for="type in fileTypes" 
                                :key="type.id" 
                                :value="type.id"
                            >
                                {{ type.name }}{{ type.stage_effect ? ' (' + type.stage_effect + ')' : '' }}
                            </option>
                        </select>
                        <div v-if="uploadForm.errors.file_type" class="text-red-500 text-sm mt-1">
                            {{ uploadForm.errors.file_type }}
                        </div>
                        <div v-if="loadingFileTypes" class="text-gray-500 text-sm mt-1">
                            Загрузка типов файлов...
                        </div>
                    </div>

                    <!-- Файл -->
                    <div>
                        <label class="block text-sm font-medium text-[#080D6E] mb-2">
                            Файл (PDF, макс. 10MB) <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="file"
                            accept=".pdf"
                            @change="handleFileSelect"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#FFB800]"
                            required
                        />
                        <div v-if="uploadForm.file" class="text-sm text-gray-600 mt-1">
                            Выбран: {{ uploadForm.file.name }} ({{ (uploadForm.file.size / 1024 / 1024).toFixed(2) }} MB)
                        </div>
                        <div v-if="uploadForm.errors.file" class="text-red-500 text-sm mt-1">
                            {{ uploadForm.errors.file }}
                        </div>
                    </div>

                    <!-- Кнопки -->
                    <div class="flex gap-4 pt-4">
                        <button 
                            type="submit"
                            :disabled="uploadForm.processing"
                            class="flex-1 bg-[#FFB800] text-[#080D6E] font-semibold py-3 px-6 rounded-xl hover:bg-[#FFC833] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="uploadForm.processing">Загрузка...</span>
                            <span v-else>Загрузить файл</span>
                        </button>
                        <button 
                            type="button"
                            @click="closeUploadModal"
                            class="flex-1 bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-xl hover:bg-gray-300 transition-colors"
                        >
                            Отмена
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
