<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { X } from 'lucide-vue-next';

interface Props {
    isOpen: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'select', backgroundUrl: string): void;
}>();

const presetBackgrounds = [
    {
        id: 'gradient-blue',
        name: 'Синий градиент',
        url: '/icons/gradient_bg.png',
        thumbnail: '/icons/gradient_bg.png'
    },
    {
        id: 'ubuntu',
        name: 'Ubuntu фон',
        url: '/icons/gradient_bg1.jpg',
        thumbnail: '/icons/gradient_bg1.jpg'
    },
    {
        id: 'abstract',
        name: 'Абстрактный фон',
        url: '/icons/gradient_bg2.jpg',
        thumbnail: '/icons/gradient_bg2.jpg'
    },
    {
        id: 'japanese',
        name: 'Японский фон',
        url: '/icons/gradient_bg3.jpg',
        thumbnail: '/icons/gradient_bg3.jpg'
    },
    {
        id: 'forest-house',
        name: 'Домик в лесу',
        url: '/icons/gradient_bg4.jpg',
        thumbnail: '/icons/gradient_bg4.jpg'
    },
    {
        id: 'flowers',
        name: 'Цветочный фон',
        url: '/icons/gradient_bg5.jpg',
        thumbnail: '/icons/gradient_bg5.jpg'
    },
    {
        id: 'cartoon',
        name: 'Мультяшный',
        url: '/icons/gradient_bg6.jpg',
        thumbnail: '/icons/gradient_bg6.jpg'
    },
    {
        id: 'cartoon-2',
        name: 'Мультяшный 2',
        url: '/icons/gradient_bg7.jpg',
        thumbnail: '/icons/gradient_bg7.jpg'
    },
    {
        id: 'cartoon-3',
        name: 'Мультяшный 3',
        url: '/icons/gradient_bg8.jpg',
        thumbnail: '/icons/gradient_bg8.jpg'
    },
    {
        id: 'cartoon-4',
        name: 'Мультяшный 4',
        url: '/icons/gradient_bg9.jpg',
        thumbnail: '/icons/gradient_bg9.jpg'
    },
    {
        id: 'halloween',
        name: 'Halloween',
        url: '/icons/gradient_bg10.jpg',
        thumbnail: '/icons/gradient_bg10.jpg'
    },
    {
        id: 'melancholy',
        name: 'Меланхолия',
        url: '/icons/gradient_bg11.webp',
        thumbnail: '/icons/gradient_bg11.webp'
    },
    {
        id: 'solid-dark',
        name: 'Темный однотонный',
        url: '#2B2358',
        thumbnail: 'data:image/svg+xml;charset=UTF-8,%3Csvg xmlns="http://www.w3.org/2000/svg" width="100" height="100"%3E%3Crect width="100" height="100" fill="%232B2358"/%3E%3C/svg%3E'
    },
    {
        id: 'solid-green',
        name: 'Зеленый однотонный',
        url: '#042C45',
        thumbnail: 'data:image/svg+xml;charset=UTF-8,%3Csvg xmlns="http://www.w3.org/2000/svg" width="100" height="100"%3E%3Crect width="100" height="100" fill="%23042C45"/%3E%3C/svg%3E'
    }
];

const selectedBackground = ref<string | null>(null);

onMounted(() => {
    initializeBackground();
});

watch(() => props.isOpen, (isOpen) => {
    if (isOpen) {
        initializeBackground();
    }
});

const handlePresetSelect = (backgroundUrl: string) => {
    selectedBackground.value = backgroundUrl;
};

const applyBackground = () => {
    if (selectedBackground.value) {
        localStorage.setItem('preset-background', selectedBackground.value);
        localStorage.removeItem('custom-background');
        
        emit('select', selectedBackground.value);
        emit('close');
    }
};

const initializeBackground = () => {
    const savedBackground = localStorage.getItem('preset-background');
    if (savedBackground) {
        selectedBackground.value = savedBackground;
    } else {
        selectedBackground.value = presetBackgrounds[0].url;
    }
};

const previewStyle = computed(() => {
    if (!selectedBackground.value) return {};
    
    if (selectedBackground.value.startsWith('#')) {
        return { backgroundColor: selectedBackground.value };
    }
    
    return { backgroundImage: `url(${selectedBackground.value})` };
});
</script>

<template>
    <div 
        v-if="isOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
        @click="emit('close')"
    >
        <div 
            class="bg-white rounded-2xl p-6 max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-2xl"
            @click.stop
        >
            <!-- Заголовок -->
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-[#080D6E] text-xl font-bold">Выбор фона</h3>
                <button 
                    @click="emit('close')"
                    class="text-[#080D6E] hover:text-gray-600 text-2xl font-bold leading-none w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Превью текущего фона -->
            <div class="mb-6">
                <label class="block text-[#080D6E] text-sm font-medium mb-2">Предпросмотр</label>
                <div 
                    class="w-full h-32 rounded-lg border-2 border-gray-300 overflow-hidden"
                    :style="{
                        ...previewStyle,
                        backgroundSize: 'cover',
                        backgroundPosition: 'center',
                        backgroundRepeat: 'no-repeat'
                    }"
                ></div>
            </div>

            <!-- Готовые фоны -->
            <div class="mb-6">
                <label class="block text-[#080D6E] text-sm font-medium mb-3">Готовые фоны</label>
                <div class="grid grid-cols-2 gap-4">
                    <div
                        v-for="preset in presetBackgrounds"
                        :key="preset.id"
                        @click="handlePresetSelect(preset.url)"
                        :class="[
                            'cursor-pointer rounded-lg border-2 transition-all p-2',
                            selectedBackground === preset.url
                                ? 'border-[#FFB800] ring-2 ring-[#FFB800] ring-opacity-50'
                                : 'border-gray-300 hover:border-[#FFB800]'
                        ]"
                    >
                        <div 
                            class="w-full h-20 rounded mb-2 overflow-hidden"
                            :style="{
                                backgroundImage: preset.url.startsWith('#') 
                                    ? 'none' 
                                    : `url(${preset.thumbnail || preset.url})`,
                                backgroundColor: preset.url.startsWith('#') ? preset.url : 'transparent',
                                backgroundSize: 'cover',
                                backgroundPosition: 'center'
                            }"
                        ></div>
                        <p class="text-xs text-[#080D6E] text-center">{{ preset.name }}</p>
                    </div>
                </div>
            </div>

            <!-- Кнопки -->
            <div class="flex justify-end gap-4">
                <button
                    @click="emit('close')"
                    class="px-6 py-2 bg-gray-200 text-[#080D6E] rounded-lg font-medium hover:bg-gray-300 transition-colors"
                >
                    Отмена
                </button>
                <button
                    @click="applyBackground"
                    class="px-6 py-2 bg-[#FFB800] text-white rounded-lg font-medium hover:bg-[#E6A600] transition-colors"
                >
                    Применить
                </button>
            </div>
        </div>
    </div>
</template>

