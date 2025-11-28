<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import CrossIcon from '@/components/icons/CrossIcon.vue';

interface User {
    id: number;
    name: string;
    email: string;
    block_rank: number | null;
    department?: string | null;
    roles: Array<{ name: string }>;
    created_at: string;
}

interface Props {
    users: {
        data: User[];
        links: any[];
        meta: any;
    };
    roles: string[];
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Пользователи',
        href: '/users',
    },
];

// Состояние модального окна
const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const selectedUser = ref<User | null>(null);

// Форма для создания/редактирования
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'employee',
    block_rank: null as number | null,
    department: '' as string | null,
});

// Открытие модального окна добавления
const openAddModal = () => {
    form.reset();
    form.role = 'employee';
    form.block_rank = null;
    form.department = '';
    isAddModalOpen.value = true;
};

// Закрытие модальных окон
const closeModals = () => {
    isAddModalOpen.value = false;
    isEditModalOpen.value = false;
    selectedUser.value = null;
    form.reset();
};

// Открытие модального окна редактирования
const openEditModal = (user: User) => {
    selectedUser.value = user;
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.password_confirmation = '';
    form.role = user.roles[0]?.name || 'employee';
    form.block_rank = user.block_rank;
    form.department = user.department || '';
    isEditModalOpen.value = true;
};

// Сохранение пользователя
const submitForm = () => {
    if (selectedUser.value) {
        form.put(`/users/${selectedUser.value.id}`, {
            onSuccess: () => {
                closeModals();
            },
        });
    } else {
        form.post('/users', {
            onSuccess: () => {
                closeModals();
            },
        });
    }
};

// Удаление пользователя
const deleteUser = (userId: number) => {
    if (confirm('Вы уверены, что хотите удалить этого пользователя?')) {
        form.delete(`/users/${userId}`, {
            onSuccess: () => {
                // Форма уже очистится автоматически
            },
        });
    }
};

// Получение названия роли на русском
const getRoleName = (roles: Array<{ name: string }>) => {
    if (roles.length === 0) return 'Нет роли';
    return roles[0].name === 'admin' ? 'Администратор' : 'Сотрудник';
};

// Фильтрация поиска
const searchForm = useForm({
    search: props.filters.search || '',
});

const applySearch = () => {
    searchForm.get('/users', {
        preserveState: true,
    });
};

const clearSearch = () => {
    searchForm.search = '';
    searchForm.get('/users', {
        preserveState: true,
    });
};
</script>

<template>
    <Head title="Пользователи" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl">
            <!-- Кнопка добавления и поиск -->
            <div class="flex gap-4 items-center mb-4">
                <button 
                    @click="openAddModal" 
                    class="min-h-[83px] rounded-xl px-4 py-3 text-white font-medium flex items-center justify-center gap-2 whitespace-nowrap" 
                    style="background-color: rgba(255, 253, 253, 0.2);"
                >
                    <CrossIcon class="w-5 h-5" />
                    ДОБАВИТЬ ПОЛЬЗОВАТЕЛЯ
                </button>

                <!-- Поиск -->
                <div class="flex gap-2 flex-1">
                    <input 
                        v-model="searchForm.search"
                        @keyup.enter="applySearch"
                        type="text" 
                        placeholder="Поиск по имени или email..."
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

            <!-- Таблица пользователей -->
            <div class="flex-1 rounded-xl border-gray-400 overflow-hidden">
                <div class="overflow-x-auto">
                    <div>
                        <!-- Заголовок таблицы -->
                        <div class="bg-[#FFB800] rounded-2xl px-4 py-3">
                            <div class="grid grid-cols-6 gap-4 font-semibold table-header-text table-header-container table-header-dividers">
                                <div>Имя</div>
                                <div>Email</div>
                                <div>Роль</div>
                                <div>Отдел</div>
                                <div>Дата создания</div>
                                <div>Действия</div>
                            </div>
                        </div>
                        
                        <!-- Строки данных -->
                        <div v-if="props.users.data.length === 0" class="text-center py-8 text-white">
                            Нет пользователей для отображения
                        </div>
                        <div v-else>
                            <div 
                                v-for="(user, index) in props.users.data" 
                                :key="user.id" 
                                :class="[
                                    index % 2 === 0 ? 'bg-[#F1EFF4]' : 'bg-[#F1EFF4BF]',
                                    'hover:ring-2 hover:ring-[#FFB800] hover:ring-opacity-30'
                                ]" 
                                class="rounded-2xl px-4 py-3 table-row-spacing mb-3 transition-all"
                            >
                                <div class="grid grid-cols-6 gap-4 text-[#080D6E] table-row-align">
                                    <div>{{ user.name }}</div>
                                    <div>{{ user.email }}</div>
                                    <div>
                                        <span :class="user.roles[0]?.name === 'admin' ? 'text-red-600 font-semibold' : 'text-blue-600'">
                                            {{ getRoleName(user.roles) }}
                                        </span>
                                    </div>
                                    <div>{{ user.department || '—' }}</div>
                                    <div>{{ new Date(user.created_at).toLocaleDateString('ru-RU') }}</div>
                                    <div>
                                        <div class="flex gap-2">
                                            <button 
                                                @click="openEditModal(user)"
                                                class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors text-xs"
                                            >
                                                Редактировать
                                            </button>
                                            <button 
                                                @click="deleteUser(user.id)"
                                                class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition-colors text-xs"
                                            >
                                                Удалить
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Пагинация -->
                <div v-if="props.users.links.length > 3" class="flex justify-center items-center gap-2 mt-4">
                    <template v-for="(link, index) in props.users.links" :key="index">
                        <a 
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            :class="[
                                'px-4 py-2 rounded-lg',
                                link.active ? 'bg-[#FFB800] text-white font-semibold' : 'bg-gray-200 text-[#080D6E] hover:bg-gray-300'
                            ]"
                        />
                        <span 
                            v-else
                            v-html="link.label"
                            class="px-4 py-2 text-gray-400"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- Модальное окно добавления/редактирования -->
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
                        {{ selectedUser ? 'Редактировать пользователя' : 'Добавить пользователя' }}
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
                        <label class="block text-[#080D6E] text-sm font-medium mb-2">Имя *</label>
                        <input 
                            v-model="form.name"
                            type="text" 
                            required
                            class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                        />
                        <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                    </div>

                    <div>
                        <label class="block text-[#080D6E] text-sm font-medium mb-2">Email *</label>
                        <input 
                            v-model="form.email"
                            type="email" 
                            required
                            class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                        />
                        <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <label class="block text-[#080D6E] text-sm font-medium mb-2">
                            Пароль {{ selectedUser ? '(оставьте пустым, чтобы не менять)' : '*' }}
                        </label>
                        <input 
                            v-model="form.password"
                            type="password" 
                            :required="!selectedUser"
                            autocomplete="new-password"
                            @focus="form.password = ''"
                            class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                        />
                        <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</div>
                    </div>

                    <div v-if="form.password || !selectedUser">
                        <label class="block text-[#080D6E] text-sm font-medium mb-2">Подтверждение пароля *</label>
                        <input 
                            v-model="form.password_confirmation"
                            type="password" 
                            :required="Boolean(form.password && form.password.length > 0)"
                            autocomplete="new-password"
                            class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                        />
                    </div>

                    <div>
                        <label class="block text-[#080D6E] text-sm font-medium mb-2">Роль *</label>
                        <select 
                            v-model="form.role"
                            required
                            class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                        >
                            <option value="employee">Сотрудник</option>
                            <option value="admin">Администратор</option>
                        </select>
                    </div>

                    <!-- Разряд (устарело) убран из UI -->

                    <div>
                        <label class="block text-[#080D6E] text-sm font-medium mb-2">Отдел</label>
                        <select 
                            v-model="form.department"
                            class="w-full px-4 py-3 bg-white rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB800] focus:outline-none shadow-sm text-[#080D6E]"
                        >
                            <option value="">Не указан</option>
                            <option value="Отдел №2">Отдел №2</option>
                            <option value="Отдел №5">Отдел №5</option>
                            <option value="Отдел №10">Отдел №10</option>
                            <option value="Отдел №11">Отдел №11</option>
                            <option value="Отдел №14">Отдел №14</option>
                            <option value="Центр №19">Центр №19</option>
                            <option value="Сектор №18">Сектор №18</option>
                        </select>
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
                            {{ form.processing ? 'Сохранение...' : (selectedUser ? 'Обновить' : 'Создать') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

