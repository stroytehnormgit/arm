<script setup lang="ts">
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

interface PlannedListItem {
    id: number;
    code?: string;
    development_name?: string;
    document_type?: string;
    designation?: string;
    development_end?: string;
    development_type?: string;
    page_count?: number;
    development_start?: string;
    block?: string;
    author?: string;
    cost?: number | string;
    cost_2025?: number | string | null;
    department?: string;
    organizations?: string;
    regulatory_documents?: string;
    first_year_stages?: string;
    subsequent_years_stages?: string;
    start_date?: string;
    end_date?: string;
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

interface Stage {
    id: number;
    name: string;
}

interface SelectedStage {
    stage_id: number;
    start_date: string;
    end_date: string;
    amount: string;
}

interface Props {
    isOpen: boolean;
    editItem?: PlannedListItem | null;
    stages?: Stage[];
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
const emit = defineEmits<{
    close: [];
}>();

const page = usePage();
const auth = computed(() => page.props.auth as any);
const accessibleBlocks = computed(() => auth.value?.user?.accessible_blocks || []);
const isAdmin = computed(() => auth.value?.user?.is_admin || false);
const userDepartment = computed(() => auth.value?.user?.department || '');

const allBlocks = [
    { value: '1.01', label: '1.01 Техническое нормирование, стандартизация, сертификация и метрология' },
    { value: '1.02', label: '1.02 Предпроектные и проектные работы' },
    { value: '1.03', label: '1.03 Организация строительного производства' },
    { value: '1.04', label: '1.04 Эксплуатация' },
    { value: '2.01', label: '2.01 Основные положения надежности зданий и сооружений' },
    { value: '2.02', label: '2.02 Пожарная безопасность' },
    { value: '2.03', label: '2.03 Защита от опасных геофизических и техногенных воздействий' },
    { value: '2.04', label: '2.04 Внутренний климат и защита от вредных воздействий' },
    { value: '2.05', label: '2.05 Размерная взаимозаменяемость и совместимость' },
    { value: '3.01', label: '3.01 Градостроительство' },
    { value: '3.02', label: '3.02 Жилые, общественные и производственные здания и сооружения, благоустройство территорий' },
    { value: '3.03', label: '3.03 Сооружения транспорта и транспортная инфраструктура' },
    { value: '3.04', label: '3.04 Гидротехнические и мелиоративные сооружения' },
    { value: '3.05', label: '3.05 Магистральные и промысловые трубопроводы' },
    { value: '4.01', label: '4.01 Водоснабжение и водоотведение' },
    { value: '4.02', label: '4.02 Теплоснабжение и холодоснабжение, отопление, вентиляция и кондиционирование воздуха' },
    { value: '4.03', label: '4.03 Газоснабжение' },
    { value: '4.04', label: '4.04 Электроснабжение, электросиловое оборудование и электрическое освещение, телефонизация, радиофикация и телефикация' },
    { value: '5.01', label: '5.01 Основания и фундаменты зданий и сооружений' },
    { value: '5.02', label: '5.02 Каменные и армокаменные конструкции' },
    { value: '5.03', label: '5.03 Железобетонные и бетонные конструкции и изделия' },
    { value: '5.04', label: '5.04 Металлические конструкции и изделия' },
    { value: '5.05', label: '5.05 Деревянные конструкции и изделия' },
    { value: '5.06', label: '5.06 Конструкции и изделия из других материалов' },
    { value: '5.07', label: '5.07 Светопрозрачные ограждения в различных конструктивных исполнениях, двери, ворота и приборы к ним' },
    { value: '5.08', label: '5.08 Кровли, изоляционные покрытия' },
    { value: '5.09', label: '5.09 Полы, отделочные и защитные покрытия' },
    { value: '6.01', label: '6.01 Стеновые кладочные изделия' },
    { value: '6.02', label: '6.02 Минеральные вяжущие материалы' },
    { value: '6.03', label: '6.03 Бетоны и растворы' },
    { value: '6.04', label: '6.04 Щебень, гравий и песок для строительных работ' },
    { value: '6.05', label: '6.05 Теплоизоляционные, звукоизоляционные и звукопоглощающие материалы и изделия' },
    { value: '6.06', label: '6.06 Кровельные, гидроизоляционные и герметизирующие материалы и изделия' },
    { value: '6.07', label: '6.07 Отделочные и облицовочные материалы и изделия' },
    { value: '6.08', label: '6.08 Асбестоцементные изделия' },
    { value: '6.09', label: '6.09 Дорожные материалы' },
    { value: '6.10', label: '6.10 Строительное стекло' },
    { value: '6.11', label: '6.11 Композитные и полимерные материалы и изделия' },
    { value: '7.01', label: '7.01 Мобильные здания и сооружения' },
    { value: '7.02', label: '7.02 Специализированная оснастка предприятий стройиндустрии' },
    { value: '7.03', label: '7.03 Оснастка строительных организаций' },
];

const filteredBlocks = computed(() => {
    return allBlocks;
});

const isCostModalOpen = ref(false);

const costDetails = ref({
    average_monthly_salary: '',
    mandatory_payments_qn: '',
    overhead_costs_qnr: '',
    profit_qp: '',
    other_expenses_qpr: '',
    review_cost_sp: ''
});

const calculationResult = ref<{
    sotr: number | null;
    sotz: number | null;
    totalCost: number | null;
    zsr: number | null;
    zsrWithBonus: number | null;
    td: number | null;
    b: number | null;
    tdCoeff: number | null;
    alpha: number | null;
    tdAlphaDiv: number | null;
    qn: number | null;
    qnr: number | null;
    qp: number | null;
    qpr: number | null;
    qSum: number | null;
    sp: number | null;
} | null>(null);

const selectedStage = ref<string | null>(null);

const documentTypeCoefficients = {
    'ТР': 445,
    'СН': 318,
    'СП': 318,
    'СТБ': 286,
    'СТБ EN': 286,
    'СТБ ISO': 286,
    'СТБ EN ISO': 286,
    'ГОСТ': 318,
    'ГОСТ EN': 318,
    'ГОСТ ISO': 318,
    'Изменение СП': 318,
    'Изменение СН': 318,
    'Изменение СТБ': 286,
    'Изменение ГОСТ': 318,
    'Изменение ГОСТ EN': 318,
    'Изменение ГОСТ ISO': 318,
    'прочие работы': 300,
    'CH': 300
};

const developmentTypeCoefficients = {
    'разработка': 1.0,
    'разработка изменения': 0.12,
    'пересмотр (в целом)': 0.9,
    'пересмотр (в части)': 0.5,
    'разработка с идентичной степенью соответствия': 0.8,
    'разработка с неидентичной степенью соответствия': 0.9,
    'проверка научно-технического уровня': 0.3,
};

const currentCoefficient = computed(() => {
    return documentTypeCoefficients[form.document_type as keyof typeof documentTypeCoefficients] || 0;
});

const currentDevelopmentTypeCoefficient = computed(() => {
    return developmentTypeCoefficients[form.development_type as keyof typeof developmentTypeCoefficients] || 0;
});

const getAlphaCoefficient = (pageCount: number | string | null | undefined): number => {
    const pages = typeof pageCount === 'string' ? parseInt(pageCount) || 0 : (pageCount || 0);
    
    const referencePoints: Array<[number, number]> = [
        [12, 0.8],
        [24, 0.9],
        [36, 1.0],
        [48, 1.1],
        [60, 1.2],
        [72, 1.3],
        [84, 1.4],
        [96, 1.5],
        [108, 1.6],
        [120, 1.7],
        [132, 1.8]
    ];
    
    if (pages < referencePoints[0][0]) {
        return referencePoints[0][1];
    }
    
    if (pages >= referencePoints[referencePoints.length - 1][0]) {
        return referencePoints[referencePoints.length - 1][1];
    }
    
    for (let i = 0; i < referencePoints.length - 1; i++) {
        const [x1, y1] = referencePoints[i];
        const [x2, y2] = referencePoints[i + 1];
        
        if (pages === x1) {
            return y1;
        }
        
        if (pages > x1 && pages <= x2) {
            const alpha = y1 + (y2 - y1) * (pages - x1) / (x2 - x1);
            return Math.round(alpha * 10000) / 10000;
        }
    }
    
    return referencePoints[0][1];
};

const alphaCoefficient = computed(() => {
    return getAlphaCoefficient(form.page_count);
});

const tdCoefficient = computed(() => {
    const td = currentCoefficient.value;
    const b = currentDevelopmentTypeCoefficient.value;
    return td * b;
});

const selectedStages = ref<SelectedStage[]>([]);

const form = useForm({
    document_type: '',
    designation: '',
    development_end: '',
    development_type: '',
    page_count: '',
    development_start: '',
    block: '',
    author: '',
    cost: '',
    cost_2025: '',
    department: '',
    development_name: '',
    organizations: '',
    regulatory_documents: '',
    first_year_stages: '',
    subsequent_years_stages: '',
    code: '',
    stages: [] as SelectedStage[],
});

watch(() => form.block, async (newBlock) => {
    if (!newBlock) return;
    if (props.editItem && props.editItem.block && newBlock === props.editItem.block && form.designation) {
        return;
    }
    try {
        const res = await fetch(`/planned-list/next-code?block=${encodeURIComponent(newBlock)}`, { credentials: 'same-origin' });
        if (!res.ok) return;
        const data = await res.json();
        if (data && data.code) {
            form.designation = data.code;
        }
    } catch {}
});

watch(() => props.isOpen, (isOpen) => {
    if (isOpen && !isAdmin.value && !props.editItem) {
        form.department = userDepartment.value || '';
    }
});

const documentTypeStagesMapping: Record<string, string[]> = {
    'СН': ['ТЗ', 'ПР', 'ОР', 'НТЭ', 'С', 'ИР', 'МВС', 'ВЭ', 'У', 'НЦЗПИ', 'И'],
    'СП': ['ТЗ', 'ПР', 'ОР', 'НТЭ', 'С', 'ИР', 'МВС', 'ВЭ', 'У', 'И'],
    'СТБ': ['ТЗ', 'ПР', 'ОР', 'С', 'БелГИСС', 'ИР', 'У', 'И'],
    'СТБ EN': ['ПЕР', 'ТЗ', 'ПР', 'ОР', 'С', 'БелГИСС', 'ИР', 'У', 'И'],
    'СТБ ISO': ['ПЕР', 'ТЗ', 'ПР', 'ОР', 'С', 'БелГИСС', 'ИР', 'У', 'И'],
    'СТБ EN ISO': ['ПЕР', 'ТЗ', 'ПР', 'ОР', 'С', 'БелГИСС', 'ИР', 'У', 'И'],
    'ГОСТ': ['ТЗ', 'ПР', 'ОР'],
    'ГОСТ EN': ['ТЗ', 'ПР', 'ОР'],
    'ГОСТ ISO': ['ТЗ', 'ПР', 'ОР'],
    'Изменение СН': ['ПР', 'ОР', 'НТЭ', 'С', 'ИР', 'МВС', 'ВЭ', 'У', 'НЦЗПИ', 'И'],
    'Изменение СП': ['ПР', 'ОР', 'НТЭ', 'С', 'ИР', 'МВС', 'ВЭ', 'У', 'И'],
    'Изменение СТБ': ['ПР', 'ОР', 'С', 'БелГИСС', 'ИР', 'У', 'И'],
    'Изменение СТБ EN': ['ПЕР', 'ПР', 'ОР', 'С', 'БелГИСС', 'ИР', 'У', 'И'],
    'Изменение СТБ ISO': ['ПЕР', 'ПР', 'ОР', 'С', 'БелГИСС', 'ИР', 'У', 'И'],
    'Изменение СТБ EN ISO': ['ПЕР', 'ПР', 'ОР', 'С', 'БелГИСС', 'ИР', 'У', 'И'],
    'Изменение ГОСТ': ['ПР', 'ОР'],
    'Изменение ГОСТ EN': ['ПР', 'ОР'],
    'Изменение ГОСТ ISO': ['ПР', 'ОР'],
    'прочие работы': ['Э1', 'Э2', 'Э3', 'Э4', 'Э5', 'Э6', 'Э7', 'Э8', 'Э9', 'Э10'],
    'CH': ['ТЗ', 'ПР', 'ОР', 'НТЭ', 'С', 'ИР', 'МВС', 'ВЭ', 'У', 'НЦЗПИ', 'И'],
    'ТР': ['ТЗ', 'ПР', 'ОР', 'НТЭ', 'С', 'ИР', 'МВС', 'ВЭ', 'У', 'НЦЗПИ', 'И'],
};

interface StageWithData {
    name: string;
    stage_id?: number;
    start_date?: string;
    end_date?: string;
    amount?: string;
}

const availableStages = ref<string[]>([]);

const firstYearStages = ref<StageWithData[]>([]);

const subsequentYearsStages = ref<StageWithData[]>([]);

const updateFormStages = () => {
    form.first_year_stages = firstYearStages.value.map(s => s.name).join('\n');
    form.subsequent_years_stages = subsequentYearsStages.value.map(s => s.name).join('\n');
    
    form.stages = [];
    
    firstYearStages.value.forEach(stage => {
        const stageObj = props.stages?.find(s => s.name === stage.name);
        if (stageObj) {
            form.stages.push({
                stage_id: stageObj.id,
                start_date: stage.start_date || '',
                end_date: stage.end_date || '',
                amount: stage.amount || ''
            });
        }
    });
    
    subsequentYearsStages.value.forEach(stage => {
        const stageObj = props.stages?.find(s => s.name === stage.name);
        if (stageObj) {
            form.stages.push({
                stage_id: stageObj.id,
                start_date: stage.start_date || '',
                end_date: stage.end_date || '',
                amount: stage.amount || ''
            });
        }
    });
};

const isInitialLoad = ref(false);

watch(() => form.document_type, (newType, oldType) => {
    if (newType) {
        const stages = documentTypeStagesMapping[newType] || [];
        availableStages.value = stages;
        
        if (props.isOpen) {
            if (!props.editItem) {
                firstYearStages.value = stages.map(name => ({ name, start_date: '', end_date: '', amount: '' }));
                subsequentYearsStages.value = [];
                updateFormStages();
            } else {
                const hasStages = firstYearStages.value.length > 0 || subsequentYearsStages.value.length > 0;
                if (!hasStages || (!isInitialLoad.value && newType !== oldType && oldType !== undefined && oldType !== '')) {
                    firstYearStages.value = stages.map(name => ({ name, start_date: '', end_date: '', amount: '' }));
                    subsequentYearsStages.value = [];
                    updateFormStages();
                }
            }
        }
    } else {
        availableStages.value = [];
        if (props.isOpen && !props.editItem) {
            firstYearStages.value = [];
            subsequentYearsStages.value = [];
            updateFormStages();
        }
    }
}, { immediate: false });

const selectStage = (stage: StageWithData) => {
    selectedStage.value = selectedStage.value === stage.name ? null : stage.name;
};

const moveStageToSubsequentYears = () => {
    if (!selectedStage.value) return;
    
    const index = firstYearStages.value.findIndex(s => s.name === selectedStage.value);
    if (index !== -1) {
        const stage = firstYearStages.value[index];
        firstYearStages.value.splice(index, 1);
        if (!subsequentYearsStages.value.find(s => s.name === stage.name)) {
            subsequentYearsStages.value.push(stage);
        }
        updateFormStages();
        selectedStage.value = null;
    }
};

const moveStageToFirstYear = () => {
    if (!selectedStage.value) return;
    
    const index = subsequentYearsStages.value.findIndex(s => s.name === selectedStage.value);
    if (index !== -1) {
        const stage = subsequentYearsStages.value[index];
        subsequentYearsStages.value.splice(index, 1);
        if (!firstYearStages.value.find(s => s.name === stage.name)) {
            firstYearStages.value.push(stage);
        }
        updateFormStages();
        selectedStage.value = null;
    }
};

const addStageToFirstYear = (stage: string) => {
    if (!firstYearStages.value.find(s => s.name === stage)) {
        firstYearStages.value.push({ name: stage, start_date: '', end_date: '', amount: '' });
        updateFormStages();
    }
};

const addStageToSubsequentYears = (stage: string) => {
    if (!subsequentYearsStages.value.find(s => s.name === stage)) {
        subsequentYearsStages.value.push({ name: stage, start_date: '', end_date: '', amount: '' });
        updateFormStages();
    }
};

const removeStageFromFirstYear = (stage: StageWithData) => {
    const index = firstYearStages.value.findIndex(s => s.name === stage.name);
    if (index > -1) {
        firstYearStages.value.splice(index, 1);
        updateFormStages();
    }
};

const removeStageFromSubsequentYears = (stage: StageWithData) => {
    const index = subsequentYearsStages.value.findIndex(s => s.name === stage.name);
    if (index > -1) {
        subsequentYearsStages.value.splice(index, 1);
        updateFormStages();
    }
};

const moveStageUpInFirstYear = () => {
    if (!selectedStage.value) return;
    const index = firstYearStages.value.findIndex(s => s.name === selectedStage.value);
    if (index > 0) {
        const temp = firstYearStages.value[index];
        firstYearStages.value[index] = firstYearStages.value[index - 1];
        firstYearStages.value[index - 1] = temp;
        updateFormStages();
    }
};

const moveStageDownInFirstYear = () => {
    if (!selectedStage.value) return;
    const index = firstYearStages.value.findIndex(s => s.name === selectedStage.value);
    if (index >= 0 && index < firstYearStages.value.length - 1) {
        const temp = firstYearStages.value[index];
        firstYearStages.value[index] = firstYearStages.value[index + 1];
        firstYearStages.value[index + 1] = temp;
        updateFormStages();
    }
};

const moveStageUpInSubsequentYears = () => {
    if (!selectedStage.value) return;
    const index = subsequentYearsStages.value.findIndex(s => s.name === selectedStage.value);
    if (index > 0) {
        const temp = subsequentYearsStages.value[index];
        subsequentYearsStages.value[index] = subsequentYearsStages.value[index - 1];
        subsequentYearsStages.value[index - 1] = temp;
        updateFormStages();
    }
};

const moveStageDownInSubsequentYears = () => {
    if (!selectedStage.value) return;
    const index = subsequentYearsStages.value.findIndex(s => s.name === selectedStage.value);
    if (index >= 0 && index < subsequentYearsStages.value.length - 1) {
        const temp = subsequentYearsStages.value[index];
        subsequentYearsStages.value[index] = subsequentYearsStages.value[index + 1];
        subsequentYearsStages.value[index + 1] = temp;
        updateFormStages();
    }
};


const submitForm = () => {
    updateFormStages();
    
    form.stages = [];
    
    if (props.editItem) {
        form.put(`/planned-list/${props.editItem.id}`, {
            onSuccess: () => {
                emit('close');
                form.reset();
                firstYearStages.value = [];
                subsequentYearsStages.value = [];
                selectedStages.value = [];
                selectedStage.value = null;
            },
        });
    } else {
        form.post('/planned-list', {
            onSuccess: () => {
                emit('close');
                form.reset();
                firstYearStages.value = [];
                subsequentYearsStages.value = [];
                selectedStages.value = [];
                selectedStage.value = null;
            },
        });
    }
};

const fillFormFromItem = (item: PlannedListItem | null | undefined) => {
    if (item && item.id) {
        form.code = item.code ?? '';
        form.document_type = item.document_type ?? '';
        form.designation = (item.designation ?? item.code ?? '');
        form.development_end = item.development_end ?? item.end_date ?? '';
        form.development_type = item.development_type ?? '';
        form.page_count = item.page_count !== undefined && item.page_count !== null ? String(item.page_count) : '';
        form.development_start = item.development_start ?? item.start_date ?? '';
        form.block = item.block ?? '';
        form.author = item.author ?? '';
        form.cost = item.cost !== undefined && item.cost !== null ? String(item.cost) : '';
        form.cost_2025 = item.cost_2025 !== undefined && item.cost_2025 !== null ? String(item.cost_2025) : '';
        form.department = item.department ?? '';
        form.development_name = item.development_name ?? '';
        form.organizations = item.organizations ?? '';
        form.regulatory_documents = item.regulatory_documents ?? '';
        
        if (item.first_year_stages) {
            const stages = item.first_year_stages.split('\n').filter(s => s.trim());
            firstYearStages.value = stages.map(name => ({ name, start_date: '', end_date: '', amount: '' }));
        } else {
            firstYearStages.value = [];
        }
        if (item.subsequent_years_stages) {
            const stages = item.subsequent_years_stages.split('\n').filter(s => s.trim());
            subsequentYearsStages.value = stages.map(name => ({ name, start_date: '', end_date: '', amount: '' }));
        } else {
            subsequentYearsStages.value = [];
        }
        
        if (firstYearStages.value.length === 0 && subsequentYearsStages.value.length === 0 && item.document_type) {
            const stages = documentTypeStagesMapping[item.document_type] || [];
            if (stages.length > 0) {
                firstYearStages.value = stages.map(name => ({ name, start_date: '', end_date: '', amount: '' }));
                subsequentYearsStages.value = [];
            }
        }
        
        if (item.stages && item.stages.length > 0) {
            selectedStages.value = item.stages.map(stage => ({
                stage_id: stage.id,
                start_date: stage.pivot?.start_date || '',
                end_date: stage.pivot?.end_date || '',
                amount: stage.pivot?.amount ? String(stage.pivot.amount) : '',
            }));
            
            item.stages.forEach(stage => {
                const stageData: StageWithData = {
                    name: stage.name,
                    stage_id: stage.id,
                    start_date: stage.pivot?.start_date || '',
                    end_date: stage.pivot?.end_date || '',
                    amount: stage.pivot?.amount ? String(stage.pivot.amount) : ''
                };
                
                const firstYearIndex = firstYearStages.value.findIndex(s => s.name === stage.name);
                if (firstYearIndex !== -1) {
                    firstYearStages.value[firstYearIndex] = stageData;
                } else {
                    const subsequentYearsIndex = subsequentYearsStages.value.findIndex(s => s.name === stage.name);
                    if (subsequentYearsIndex !== -1) {
                        subsequentYearsStages.value[subsequentYearsIndex] = stageData;
                    }
                }
            });
        } else {
            selectedStages.value = [];
        }
        
        updateFormStages();
    } else {
        form.reset();
        firstYearStages.value = [];
        subsequentYearsStages.value = [];
        selectedStages.value = [];
        selectedStage.value = null;
    }
};

const closeModal = () => {
    emit('close');
    form.reset();
    firstYearStages.value = [];
    subsequentYearsStages.value = [];
    selectedStages.value = [];
    selectedStage.value = null;
};

watch(() => props.isOpen, (isOpen) => {
    if (isOpen) {
        nextTick(() => {
            if (props.editItem) {
                isInitialLoad.value = true;
                fillFormFromItem(props.editItem);
                setTimeout(() => {
                    isInitialLoad.value = false;
                }, 100);
            } else {
                isInitialLoad.value = false;
                form.reset();
                firstYearStages.value = [];
                subsequentYearsStages.value = [];
                selectedStages.value = [];
                selectedStage.value = null;
                
                if (form.document_type) {
                    const stages = documentTypeStagesMapping[form.document_type] || [];
                    if (stages.length > 0) {
                        firstYearStages.value = stages.map(name => ({ name, start_date: '', end_date: '', amount: '' }));
                        subsequentYearsStages.value = [];
                        updateFormStages();
                    }
                }
            }
        });
    } else {
        isInitialLoad.value = false;
        form.reset();
        firstYearStages.value = [];
        subsequentYearsStages.value = [];
        selectedStages.value = [];
        selectedStage.value = null;
    }
});

watch(() => props.editItem, (item) => {
    if (props.isOpen && item) {
        fillFormFromItem(item);
    }
}, { deep: true });

const uniqueCoefficients = computed(() => {
    const unique = Array.from(new Set(Object.values(documentTypeCoefficients))).sort((a, b) => a - b);
    return unique;
});

const uniqueDevelopmentTypeCoefficients = computed(() => {
    const unique = Array.from(new Set(Object.values(developmentTypeCoefficients))).sort((a, b) => a - b);
    return unique;
});

const currentYear = computed(() => new Date().getFullYear());

const formatNumber = (value: number | null | undefined | string): string => {
    if (value === null || value === undefined || value === '') return '—';
    const numValue = typeof value === 'string' ? parseFloat(value) : value;
    if (isNaN(numValue)) return '—';
    return numValue.toLocaleString('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDecimal = (value: number | null | undefined | string): string => {
    if (value === null || value === undefined || value === '') return '—';
    const numValue = typeof value === 'string' ? parseFloat(value) : value;
    if (isNaN(numValue)) return '—';
    return numValue.toFixed(4);
};

const openCostModal = (event?: Event) => {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    calculationResult.value = null;
    
    isCostModalOpen.value = true;
};

const closeCostModal = () => {
    isCostModalOpen.value = false;
};

const handleEscapeKey = (event: KeyboardEvent) => {
    if (event.key === 'Escape' || event.keyCode === 27) {
        if (isCostModalOpen.value) {
            closeCostModal();
            event.preventDefault();
            event.stopPropagation();
        }
        else if (props.isOpen) {
            closeModal();
            event.preventDefault();
            event.stopPropagation();
        }
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleEscapeKey);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleEscapeKey);
});

const calculateResult = () => {
    try {
        if (!props.costData) {
            calculationResult.value = null;
            return;
        }
        
        const zsr = typeof props.costData.average_monthly_salary === 'number' ? props.costData.average_monthly_salary : (parseFloat(String(props.costData.average_monthly_salary)) || 0);
        const qn = typeof props.costData.mandatory_payments_qn === 'number' ? props.costData.mandatory_payments_qn : (parseFloat(String(props.costData.mandatory_payments_qn)) || 0);
        const qnr = typeof props.costData.overhead_costs_qnr === 'number' ? props.costData.overhead_costs_qnr : (parseFloat(String(props.costData.overhead_costs_qnr)) || 0);
        const qp = typeof props.costData.profit_qp === 'number' ? props.costData.profit_qp : (parseFloat(String(props.costData.profit_qp)) || 0);
        const qpr = typeof props.costData.other_expenses_qpr === 'number' ? props.costData.other_expenses_qpr : (parseFloat(String(props.costData.other_expenses_qpr)) || 0);
        const sp = typeof props.costData.review_cost_sp === 'number' ? props.costData.review_cost_sp : (parseFloat(String(props.costData.review_cost_sp)) || 0);
        
        if (!zsr) {
            calculationResult.value = null;
            return;
        }
        
        if (!form.document_type || !form.development_type || !form.page_count) {
            calculationResult.value = null;
            return;
        }
        
        const zsrWithBonus = zsr + (0.05 * zsr);
        const td = currentCoefficient.value;
        const b = currentDevelopmentTypeCoefficient.value;
        const tdCoeff = tdCoefficient.value;
        const alpha = alphaCoefficient.value;
        const tdAlphaDiv = (tdCoeff * alpha) / 21.2;
        const qSum = 1 + qn + qnr + qp + qpr;
        
        const sotr = zsrWithBonus * tdAlphaDiv * qSum;
        
        const sotz = 5.0 * sp;
        
        const totalCost = sotr + sotz;
        
        let cost2025 = totalCost;
        const firstYearStagesCount = firstYearStages.value.length;
        const subsequentYearsStagesCount = subsequentYearsStages.value.length;
        const totalStagesCount = firstYearStagesCount + subsequentYearsStagesCount;
        
        if (totalStagesCount > 0 && firstYearStagesCount > 0) {
            cost2025 = (totalCost * firstYearStagesCount) / totalStagesCount;
        } else if (totalStagesCount === 0 && firstYearStagesCount === 0) {
            cost2025 = totalCost;
        }
        
        calculationResult.value = {
            sotr,
            sotz,
            totalCost,
            zsr,
            zsrWithBonus,
            td,
            b,
            tdCoeff,
            alpha,
            tdAlphaDiv,
            qn,
            qnr,
            qp,
            qpr,
            qSum,
            sp
        };
        
        form.cost = totalCost.toFixed(2);
        form.cost_2025 = cost2025.toFixed(2);
    } catch (error) {
        calculationResult.value = null;
        console.error('Ошибка при расчете стоимости:', error);
    }
};

const saveCostDetails = () => {
    closeCostModal();
};
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" @click="closeModal">
        <div class="bg-white rounded-2xl p-6 max-w-6xl w-full max-h-[95vh] overflow-y-auto shadow-2xl" @click.stop>
            <!-- Заголовок -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-[#080D6E] text-xl font-bold">
                    {{ props.editItem ? 'Редактировать предложение' : 'Добавить предложение во вновь начинаемую тематику' }}
                </h2>
                <button @click="closeModal" class="text-[#080D6E] hover:text-gray-600 text-2xl font-bold leading-none w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">
                    ×
                </button>
            </div>

            <!-- Форма -->
            <form @submit.prevent="submitForm" class="space-y-8">
                <!-- Основная информация -->
                <div class="bg-white bg-opacity-10 rounded-xl p-6">
                    <h3 class="text-[#080D6E] text-lg font-semibold mb-4 flex items-center">
                        <div class="w-2 h-2 bg-[#FFB800] rounded-full mr-3"></div>
                        Основная информация
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Тип документа *</label>
                            <select 
                                v-model="form.document_type"
                                class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                                required
                            >
                                <option value="">Выберите тип документа</option>
                                <option value="CH">CH</option>
                                <option value="СН">СН</option>
                                <option value="СП">СП</option>
                                <option value="СТБ">СТБ</option>
                                <option value="СТБ EN">СТБ EN</option>
                                <option value="СТБ ISO">СТБ ISO</option>
                                <option value="СТБ EN ISO">СТБ EN ISO</option>
                                <option value="прочие работы">прочие работы</option>
                                <option value="ТР">ТР</option>
                                <option value="ГОСТ">ГОСТ</option>
                                <option value="ГОСТ EN">ГОСТ EN</option>
                                <option value="ГОСТ ISO">ГОСТ ISO</option>
                                <option value="Изменение СН">Изменение СН</option>
                                <option value="Изменение СП">Изменение СП</option>
                                <option value="Изменение СТБ">Изменение СТБ</option>
                                <option value="Изменение СТБ EN">Изменение СТБ EN</option>
                                <option value="Изменение СТБ ISO">Изменение СТБ ISO</option>
                                <option value="Изменение СТБ EN ISO">Изменение СТБ EN ISO</option>
                                <option value="Изменение ГОСТ">Изменение ГОСТ</option>
                                <option value="Изменение ГОСТ EN">Изменение ГОСТ EN</option>
                                <option value="Изменение ГОСТ ISO">Изменение ГОСТ ISO</option>
                            </select>
                            <div v-if="currentCoefficient > 0" class="mt-2 text-sm text-[#080D6E] bg-[#FFB800] bg-opacity-10 px-3 py-2 rounded-lg">
                                <strong>Коэффициент:</strong> {{ currentCoefficient }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Шифр (формируется автоматически)</label>
                            <input 
                                v-model="form.designation"
                                type="text" 
                                class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                                placeholder="Автогенерация после выбора блока"
                                readonly
                            />
                        </div>
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Тип разработки *</label>
                            <select 
                                v-model="form.development_type"
                                class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                                required
                            >
                                <option value="">Выберите тип разработки</option>
                                <option value="разработка">разработка</option>
                                <option value="разработка изменения">разработка изменения</option>
                                <option value="пересмотр (в целом)">пересмотр (в целом)</option>
                                <option value="пересмотр (в части)">пересмотр (в части)</option>
                                <option value="разработка с идентичной степенью соответствия">разработка с идентичной степенью соответствия</option>
                                <option value="разработка с неидентичной степенью соответствия">разработка с неидентичной степенью соответствия</option>
                                <option value="проверка научно-технического уровня">проверка научно-технического уровня</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Количество страниц</label>
                            <input 
                                v-model="form.page_count"
                                type="number" 
                                class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                                placeholder="Введите количество страниц"
                                min="1"
                            />
                        </div>
                    </div>
                </div>

                <!-- Наименование и описание -->
                <div class="bg-white bg-opacity-10 rounded-xl p-6">
                    <h3 class="text-[#080D6E] text-lg font-semibold mb-4 flex items-center">
                        <div class="w-2 h-2 bg-[#FFB800] rounded-full mr-3"></div>
                        Наименование и описание
                    </h3>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Наименование разработки *</label>
                            <textarea 
                                v-model="form.development_name"
                                rows="3"
                                class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none resize-none shadow-sm text-[#080D6E]"
                                placeholder="Введите наименование разработки"
                                required
                            ></textarea>
                        </div>
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Наименование организаций, выполняющих работу</label>
                            <textarea 
                                v-model="form.organizations"
                                rows="2"
                                class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none resize-none shadow-sm text-[#080D6E]"
                                placeholder="Введите наименование организаций"
                            ></textarea>
                        </div>
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Разрабатывается взамен действующих нормативных документов</label>
                            <textarea 
                                v-model="form.regulatory_documents"
                                rows="2"
                                class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none resize-none shadow-sm text-[#080D6E]"
                                placeholder="Введите информацию о нормативных документах"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Сроки и ответственные -->
                <div class="bg-white bg-opacity-10 rounded-xl p-6">
                    <h3 class="text-[#080D6E] text-lg font-semibold mb-4 flex items-center">
                        <div class="w-2 h-2 bg-[#FFB800] rounded-full mr-3"></div>
                        Сроки и ответственные
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Начало разработки *</label>
                            <input 
                                v-model="form.development_start"
                                type="date" 
                                class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                                required
                            />
                        </div>
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Окончание разработки *</label>
                            <input 
                                v-model="form.development_end"
                                type="date" 
                                class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                                required
                            />
                        </div>
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Автор</label>
                            <input 
                                v-model="form.author"
                                type="text" 
                                class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                                placeholder="Введите автора"
                            />
                        </div>
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Отдел</label>
                            <select 
                                v-model="form.department"
                                :disabled="!isAdmin"
                                class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E] disabled:bg-gray-100 disabled:text-gray-500"
                            >
                                <template v-if="isAdmin">
                                    <option value="">Выберите отдел</option>
                                    <option value="Отдел №2">Отдел №2</option>
                                    <option value="Отдел №5">Отдел №5</option>
                                    <option value="Отдел №10">Отдел №10</option>
                                    <option value="Отдел №11">Отдел №11</option>
                                    <option value="Отдел №14">Отдел №14</option>
                                    <option value="Центр №19">Центр №19</option>
                                    <option value="Сектор №18">Сектор №18</option>
                                </template>
                                <template v-else>
                                    <option :value="userDepartment">{{ userDepartment || '—' }}</option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Классификация и стоимость -->
                <div class="bg-white bg-opacity-10 rounded-xl p-6">
                    <h3 class="text-[#080D6E] text-lg font-semibold mb-4 flex items-center">
                        <div class="w-2 h-2 bg-[#FFB800] rounded-full mr-3"></div>
                        Классификация и стоимость
                    </h3>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-[#080D6E] text-sm font-medium mb-2">Блок</label>
                            <select 
                                v-model="form.block"
                                class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                            >
                                <option value="">Выберите блок</option>
                                <option 
                                    v-for="block in filteredBlocks" 
                                    :key="block.value" 
                                    :value="block.value"
                                >
                                    {{ block.label }}
                                </option>
                            </select>
                        </div>
                        <!-- Стоимость - только для администраторов -->
                        <div v-if="isAdmin" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[#080D6E] text-sm font-medium mb-2">Стоимость всего (руб.)</label>
                                <div class="flex gap-2">
                                    <input 
                                        v-model="form.cost"
                                        type="number" 
                                        step="0.01"
                                        class="flex-1 px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                                        placeholder="Введите стоимость"
                                        min="0"
                                    />
                                    <button
                                        type="button"
                                        @click="openCostModal"
                                        class="px-3 py-3 bg-gray-100 text-gray-600 rounded-lg border border-gray-300 hover:bg-gray-200 transition-colors"
                                        title="Детализация стоимости"
                                    >
                                        ...
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[#080D6E] text-sm font-medium mb-2">Стоимость на 2025 год (руб.)</label>
                                <input 
                                    v-model="form.cost_2025"
                                    type="number" 
                                    step="0.01"
                                    class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                                    placeholder="Введите стоимость на 2025 год"
                                    min="0"
                                />
                                <p class="text-xs text-gray-500 mt-1">
                                    Рассчитывается автоматически при расчете стоимости, можно изменить вручную
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Распределение этапов -->
                <div v-if="form.document_type" class="bg-white bg-opacity-10 rounded-xl p-6">
                    <h3 class="text-[#080D6E] text-lg font-semibold mb-4 flex items-center">
                        <div class="w-2 h-2 bg-[#FFB800] rounded-full mr-3"></div>
                        Распределение этапов
                    </h3>
                    

                    <div class="flex flex-col md:flex-row gap-4">
                        <!-- Первый год -->
                        <div class="flex-1" style="flex: 1 1 48%;">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-[#080D6E] text-sm font-medium">На первый год</label>
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        @click.stop="moveStageUpInFirstYear"
                                        :disabled="!selectedStage || !firstYearStages.find(s => s.name === selectedStage) || firstYearStages.findIndex(s => s.name === selectedStage) === 0"
                                        class="flex items-center justify-center bg-gray-200 text-[#080D6E] rounded-lg hover:bg-gray-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        style="width: 1.8rem; height: 1.8rem;"
                                        title="Переместить вверх"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        @click.stop="moveStageDownInFirstYear"
                                        :disabled="!selectedStage || !firstYearStages.find(s => s.name === selectedStage) || firstYearStages.findIndex(s => s.name === selectedStage) === firstYearStages.length - 1"
                                        class="flex items-center justify-center bg-gray-200 text-[#080D6E] rounded-lg hover:bg-gray-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        style="width: 1.8rem; height: 1.8rem;"
                                        title="Переместить вниз"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="min-h-[200px] max-h-[300px] overflow-y-auto p-4 bg-white rounded-lg border-2 border-gray-300">
                                <div v-if="firstYearStages.length === 0" class="text-gray-400 text-sm text-center py-8">
                                    <span>Нет этапов</span>
                                </div>
                                <div v-else class="space-y-2">
                                    <div
                                        v-for="(stage, index) in firstYearStages"
                                        :key="index"
                                        @click="selectStage(stage)"
                                        :class="[
                                            'flex items-center p-3 rounded-lg border cursor-pointer transition-all shadow-sm',
                                            selectedStage === stage.name 
                                                ? 'bg-orange-400 border-orange-500 border-2 shadow-md scale-[1.02] font-semibold' 
                                                : 'bg-[#FFB800] bg-opacity-10 border-[#FFB800] border-opacity-30 hover:bg-opacity-20'
                                        ]"
                                    >
                                        <div class="flex items-center gap-2">
                                            <div :class="[
                                                'w-2 h-2 rounded-full',
                                                selectedStage === stage.name ? 'bg-orange-600' : 'bg-[#FFB800] opacity-50'
                                            ]"></div>
                                            <span :class="[
                                                'text-sm',
                                                selectedStage === stage.name ? 'text-white font-bold' : 'text-[#080D6E]'
                                            ]">{{ stage.name }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Кнопки перемещения -->
                        <div class="flex flex-col items-center justify-center gap-4" style="flex: 0 0 9.33%;">
                            <button
                                type="button"
                                @click="moveStageToSubsequentYears"
                                :disabled="!selectedStage || !firstYearStages.find(s => s.name === selectedStage)"
                                class="flex items-center justify-center bg-[#FFB800] text-white rounded-lg hover:bg-[#E6A600] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                style="width: 2.1rem; height: 2.1rem;"
                                title="Переместить в следующие годы"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </button>
                            <button
                                type="button"
                                @click="moveStageToFirstYear"
                                :disabled="!selectedStage || !subsequentYearsStages.find(s => s.name === selectedStage)"
                                class="flex items-center justify-center bg-[#FFB800] text-white rounded-lg hover:bg-[#E6A600] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                style="width: 2.1rem; height: 2.1rem;"
                                title="Переместить в первый год"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                </svg>
                            </button>
                        </div>

                        <!-- Следующие годы -->
                        <div class="flex-1" style="flex: 1 1 48%;">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-[#080D6E] text-sm font-medium">На следующие годы</label>
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        @click.stop="moveStageUpInSubsequentYears"
                                        :disabled="!selectedStage || !subsequentYearsStages.find(s => s.name === selectedStage) || subsequentYearsStages.findIndex(s => s.name === selectedStage) === 0"
                                        class="flex items-center justify-center bg-gray-200 text-[#080D6E] rounded-lg hover:bg-gray-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        style="width: 1.8rem; height: 1.8rem;"
                                        title="Переместить вверх"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        @click.stop="moveStageDownInSubsequentYears"
                                        :disabled="!selectedStage || !subsequentYearsStages.find(s => s.name === selectedStage) || subsequentYearsStages.findIndex(s => s.name === selectedStage) === subsequentYearsStages.length - 1"
                                        class="flex items-center justify-center bg-gray-200 text-[#080D6E] rounded-lg hover:bg-gray-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        style="width: 1.8rem; height: 1.8rem;"
                                        title="Переместить вниз"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="min-h-[200px] max-h-[300px] overflow-y-auto p-4 bg-white rounded-lg border-2 border-gray-300">
                                <div v-if="subsequentYearsStages.length === 0" class="text-gray-400 text-sm text-center py-8">
                                    <span>Нет этапов</span>
                                </div>
                                <div v-else class="space-y-2">
                                    <div
                                        v-for="(stage, index) in subsequentYearsStages"
                                        :key="index"
                                        @click="selectStage(stage)"
                                        :class="[
                                            'flex items-center p-3 rounded-lg border cursor-pointer transition-all shadow-sm',
                                            selectedStage === stage.name 
                                                ? 'bg-orange-400 border-orange-500 border-2 shadow-md scale-[1.02] font-semibold' 
                                                : 'bg-[#FFB800] bg-opacity-10 border-[#FFB800] border-opacity-30 hover:bg-opacity-20'
                                        ]"
                                    >
                                        <div class="flex items-center gap-2">
                                            <div :class="[
                                                'w-2 h-2 rounded-full',
                                                selectedStage === stage.name ? 'bg-orange-600' : 'bg-[#FFB800] opacity-50'
                                            ]"></div>
                                            <span :class="[
                                                'text-sm',
                                                selectedStage === stage.name ? 'text-white font-bold' : 'text-[#080D6E]'
                                            ]">{{ stage.name }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Кнопки действий -->
                <div class="flex justify-end gap-4 pt-6 border-t border-gray-300">
                    <button 
                        type="button"
                        @click="closeModal"
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
                        <span v-else>{{ props.editItem ? 'Обновить' : 'Сохранить' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Модальное окно для детализации стоимости -->
    <div v-if="isCostModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-black bg-opacity-50 p-4" @click="closeCostModal">
        <div class="bg-white rounded-2xl p-6 max-w-2xl w-full shadow-2xl" @click.stop>
            <!-- Заголовок -->
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-[#080D6E] text-xl font-bold">
                    Детализация стоимости
                </h3>
                <button @click="closeCostModal" class="text-[#080D6E] hover:text-gray-600 text-2xl font-bold leading-none w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">
                    ×
                </button>
            </div>

            <!-- Справочная информация о коэффициентах из costs -->
            <div v-if="props.costData" class="mb-4 bg-gray-50 border border-gray-200 rounded-lg p-3">
                <div class="text-[10px] text-gray-600 space-y-1">
                    <div class="font-semibold text-[#080D6E] mb-1">Используемые данные из справочника (год {{ currentYear }}):</div>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-0.5">
                        <div>Зср: <span class="font-medium">{{ formatNumber(props.costData.average_monthly_salary) }}</span></div>
                        <div>Qн: <span class="font-medium">{{ formatDecimal(props.costData.mandatory_payments_qn) }}</span></div>
                        <div>Qнр: <span class="font-medium">{{ formatDecimal(props.costData.overhead_costs_qnr) }}</span></div>
                        <div>Qп: <span class="font-medium">{{ formatDecimal(props.costData.profit_qp) }}</span></div>
                        <div>Qпр: <span class="font-medium">{{ formatDecimal(props.costData.other_expenses_qpr) }}</span></div>
                        <div>Сп: <span class="font-medium">{{ formatNumber(props.costData.review_cost_sp) }}</span></div>
                    </div>
                </div>
            </div>

            <!-- Информация о параметрах расчета -->
            <div v-if="form.document_type && form.development_type && form.page_count" class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
                <div class="text-xs text-[#080D6E] space-y-1">
                    <div><strong>Тип документа:</strong> {{ form.document_type }} (ТД = {{ currentCoefficient }})</div>
                    <div><strong>Тип разработки:</strong> {{ form.development_type }} (В = {{ currentDevelopmentTypeCoefficient.toFixed(2) }})</div>
                    <div><strong>Количество страниц:</strong> {{ form.page_count }} (α = {{ alphaCoefficient.toFixed(4) }})</div>
                    <div><strong>Тд(коэфф) = ТД × В:</strong> {{ tdCoefficient.toFixed(2) }}</div>
                </div>
            </div>

                <!-- Результаты расчета -->
                <div v-if="calculationResult" class="bg-[#FFB800] bg-opacity-10 border-2 border-[#FFB800] rounded-lg p-4">
                    <h4 class="text-sm font-bold text-[#080D6E] mb-3">Результаты расчета:</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-[#080D6E]">Сотр (стоимость основных работ):</span>
                            <span class="font-bold text-[#080D6E]">
                                {{ calculationResult.sotr?.toLocaleString('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} руб.
                            </span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-gray-300">
                            <span class="text-[#080D6E]">Сотз (стоимость отзывов):</span>
                            <span class="font-bold text-[#080D6E]">
                                {{ calculationResult.sotz?.toLocaleString('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} руб.
                            </span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t-2 border-[#FFB800] mt-2">
                            <span class="text-base font-bold text-[#080D6E]">Итого: С = Сотр + Сотз</span>
                            <span class="text-lg font-bold text-[#080D6E]">
                                {{ calculationResult.totalCost?.toLocaleString('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} руб.
                            </span>
                        </div>
                    </div>
                </div>

            <!-- Кнопки действий -->
            <div class="flex justify-end gap-4 pt-6 border-t border-gray-300 mt-6">
                <button 
                    type="button"
                    @click="closeCostModal"
                    class="px-8 py-3 bg-gray-200 text-[#080D6E] rounded-lg font-medium hover:bg-gray-300 transition-colors"
                >
                    Отмена
                </button>
                <button 
                    type="button"
                    @click="calculateResult"
                    class="px-8 py-3 bg-blue-500 text-white rounded-lg font-medium hover:bg-blue-600 transition-colors"
                >
                    СЧИТАТЬ
                </button>
                <button 
                    type="button"
                    @click="saveCostDetails"
                    class="px-8 py-3 bg-[#FFB800] text-white rounded-lg font-medium hover:bg-[#E6A600] transition-colors"
                >
                    СОХРАНИТЬ
                </button>
            </div>
        </div>
    </div>
</template>