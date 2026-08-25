<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { t } from '@/i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
  CheckSquare,
  Plus,
  Filter,
  Search,
  Calendar,
  User,
  Trash2,
  Edit2,
  X,
  Sparkles,
  CheckCircle2,
  Clock,
  AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
  tasks: {
    type: Object,
    default: () => ({ data: [] })
  },
  departments: {
    type: Array,
    default: () => []
  },
  employees: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Object,
    default: () => ({})
  }
});

// Modal state
const isModalOpen = ref(false);
const editingTask = ref(null);

const taskForm = useForm({
  user_id: '',
  title: '',
  description: '',
  task_date: new Date().toISOString().split('T')[0],
  progress: 0,
  status: 'pending',
});

function openCreateModal() {
  editingTask.value = null;
  taskForm.reset();
  taskForm.task_date = new Date().toISOString().split('T')[0];
  isModalOpen.value = true;
}

function openEditModal(task) {
  editingTask.value = task;
  taskForm.user_id = task.user_id;
  taskForm.title = task.title;
  taskForm.description = task.description || '';
  taskForm.task_date = task.task_date;
  taskForm.progress = task.progress;
  taskForm.status = task.status;
  isModalOpen.value = true;
}

function submitTask() {
  if (editingTask.value) {
    taskForm.put(`/tasks/${editingTask.value.id}`, {
      onSuccess: () => {
        isModalOpen.value = false;
      }
    });
  } else {
    taskForm.post('/tasks', {
      onSuccess: () => {
        isModalOpen.value = false;
      }
    });
  }
}

function deleteTask(task) {
  if (confirm('هل أنت متأكد من رغبتك في حذف هذه المهمة؟')) {
    router.delete(`/tasks/${task.id}`);
  }
}

const filterForm = ref({
  department_id: props.filters.department_id || '',
  user_id: props.filters.user_id || '',
  status: props.filters.status || '',
  task_type: props.filters.task_type || '',
  date: props.filters.date || '',
});

function applyFilters() {
  router.get('/tasks', filterForm.value, { preserveState: true, replace: true });
}

function resetFilters() {
  filterForm.value = {
    department_id: '',
    user_id: '',
    status: '',
    task_type: '',
    date: '',
  };
  applyFilters();
}
</script>

<template>
  <Head :title="t('navTasks')" />

  <AppLayout>
    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
          {{ t('navTasks') }}
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
          إدارة وتكليف ومتابعة المهام الميدانية والمؤسسية للموظفين
        </p>
      </div>

      <button
        @click="openCreateModal"
        type="button"
        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 active:scale-95 text-white font-bold text-xs shadow-md shadow-brand-500/20 transition-all cursor-pointer"
      >
        <Plus class="w-4 h-4" />
        <span>{{ t('assignNewTask') }}</span>
      </button>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <!-- Department Filter -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-500 mb-1">القسم</label>
          <select
            v-model="filterForm.department_id"
            @change="applyFilters"
            class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100"
          >
            <option value="">{{ t('all') }} (كافة الأقسام)</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
        </div>

        <!-- Employee Filter -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-500 mb-1">الموظف</label>
          <select
            v-model="filterForm.user_id"
            @change="applyFilters"
            class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100"
          >
            <option value="">{{ t('all') }} (كافة الموظفين)</option>
            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
              {{ emp.name }}
            </option>
          </select>
        </div>

        <!-- Status Filter -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-500 mb-1">الحالة</label>
          <select
            v-model="filterForm.status"
            @change="applyFilters"
            class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100"
          >
            <option value="">{{ t('all') }}</option>
            <option value="pending">معلقة (Pending)</option>
            <option value="in_progress">قيد التنفيذ (In Progress)</option>
            <option value="completed">مكتملة (Completed)</option>
          </select>
        </div>

        <!-- Task Type -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-500 mb-1">النوع</label>
          <select
            v-model="filterForm.task_type"
            @change="applyFilters"
            class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100"
          >
            <option value="">{{ t('all') }}</option>
            <option value="assigned">موكلة من الإدارة</option>
            <option value="self_reported">عمل ذاتي</option>
          </select>
        </div>

        <!-- Date -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-500 mb-1">التاريخ</label>
          <input
            v-model="filterForm.date"
            @change="applyFilters"
            type="date"
            class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100"
          />
        </div>
      </div>
    </div>

    <!-- Tasks Table Card -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-start text-xs">
          <thead class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 text-slate-500 font-bold">
            <tr>
              <th class="py-3.5 px-4 text-start">عنوان المهمة</th>
              <th class="py-3.5 px-4 text-start">الموظف المنفذ</th>
              <th class="py-3.5 px-4 text-start">القسم</th>
              <th class="py-3.5 px-4 text-start">النوع</th>
              <th class="py-3.5 px-4 text-center">نسبة الإنجاز</th>
              <th class="py-3.5 px-4 text-center">الحالة</th>
              <th class="py-3.5 px-4 text-start">التاريخ</th>
              <th class="py-3.5 px-4 text-center">{{ t('actions') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr 
              v-for="task in tasks.data" 
              :key="task.id"
              class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors"
            >
              <td class="py-3.5 px-4">
                <div class="font-bold text-slate-900 dark:text-white">{{ task.title }}</div>
                <div v-if="task.description" class="text-[11px] text-slate-500 mt-0.5 line-clamp-1">
                  {{ task.description }}
                </div>
              </td>
              <td class="py-3.5 px-4 font-semibold text-slate-700 dark:text-slate-300">
                {{ task.user?.name }}
              </td>
              <td class="py-3.5 px-4 text-slate-500">
                {{ task.department?.name }}
              </td>
              <td class="py-3.5 px-4">
                <span 
                  v-if="task.task_type === 'assigned'" 
                  class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300"
                >
                  موكلة
                </span>
                <span 
                  v-else 
                  class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300"
                >
                  ذاتية
                </span>
              </td>
              <td class="py-3.5 px-4 text-center">
                <div class="inline-flex items-center gap-2">
                  <div class="w-16 bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-brand-600 h-full rounded-full" :style="{ width: `${task.progress}%` }"></div>
                  </div>
                  <span class="font-bold text-slate-700 dark:text-slate-300">{{ task.progress }}%</span>
                </div>
              </td>
              <td class="py-3.5 px-4 text-center">
                <span 
                  :class="{
                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300': task.status === 'completed',
                    'bg-teal-100 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300': task.status === 'in_progress',
                    'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300': task.status === 'pending',
                  }"
                  class="px-2.5 py-1 rounded-full text-[10px] font-bold"
                >
                  {{ task.status === 'completed' ? 'مكتملة' : (task.status === 'in_progress' ? 'قيد التنفيذ' : 'معلقة') }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-slate-500 font-mono text-[11px]">
                {{ task.task_date }}
              </td>
              <td class="py-3.5 px-4 text-center">
                <div class="inline-flex items-center gap-1">
                  <button
                    @click="openEditModal(task)"
                    type="button"
                    class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 transition-colors"
                  >
                    <Edit2 class="w-3.5 h-3.5" />
                  </button>
                  <button
                    @click="deleteTask(task)"
                    type="button"
                    class="p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 transition-colors"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="tasks.data.length === 0">
              <td colspan="8" class="py-12 text-center text-slate-400">
                لا توجد مهام متطابقة مع شروط التصفية الحالية.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create / Edit Modal -->
    <div v-if="isModalOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="isModalOpen = false">
      <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 max-w-lg w-full p-6 shadow-2xl relative" @click.stop>
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
          <h3 class="text-sm font-bold text-slate-900 dark:text-white">
            {{ editingTask ? 'تعديل بيانات المهمة' : t('assignNewTask') }}
          </h3>
          <button @click="isModalOpen = false" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitTask" class="space-y-4 text-xs">
          <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('assignTo') }} *</label>
            <select
              v-model="taskForm.user_id"
              required
              :disabled="!!editingTask"
              class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none"
            >
              <option value="" disabled>اختر الموظف...</option>
              <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                {{ emp.name }} ({{ emp.department?.name || 'القسم' }})
              </option>
            </select>
          </div>

          <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('taskTitle') }} *</label>
            <input
              v-model="taskForm.title"
              type="text"
              required
              placeholder="أدخل عنوان المهمة بوضوح..."
              class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-brand-500"
            />
          </div>

          <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('taskDesc') }}</label>
            <textarea
              v-model="taskForm.description"
              rows="3"
              placeholder="الملاحظات والتوجيهات الميدانية..."
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-brand-500"
            ></textarea>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('taskDate') }}</label>
              <input
                v-model="taskForm.task_date"
                type="date"
                required
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none"
              />
            </div>

            <div v-if="editingTask">
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('progress') }} ({{ taskForm.progress }}%)</label>
              <input
                v-model.number="taskForm.progress"
                type="range"
                min="0"
                max="100"
                step="5"
                class="w-full mt-2 cursor-pointer"
              />
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
            <button
              @click="isModalOpen = false"
              type="button"
              class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 font-semibold text-slate-600 dark:text-slate-300"
            >
              {{ t('cancel') }}
            </button>
            <button
              :disabled="taskForm.processing"
              type="submit"
              class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold shadow-sm disabled:opacity-50"
            >
              {{ taskForm.processing ? 'جاري الحفظ...' : t('save') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
