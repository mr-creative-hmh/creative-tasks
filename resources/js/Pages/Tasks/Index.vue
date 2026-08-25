<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { t } from '@/i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
  CheckSquare,
  Plus,
  Filter,
  Calendar,
  User,
  Building2,
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

const filterForm = ref({
  department_id: props.filters.department_id || '',
  user_id: props.filters.user_id || '',
  status: props.filters.status || '',
  task_type: props.filters.task_type || '',
  date: props.filters.date ? props.filters.date.split('T')[0] : new Date().toISOString().split('T')[0],
});

const isModalOpen = ref(false);
const editingTask = ref(null);

const taskForm = useForm({
  user_id: '',
  title: '',
  description: '',
  progress: 0,
  status: 'pending',
  task_date: new Date().toISOString().split('T')[0],
});

// Watch progress change inside edit modal to auto-update status field
watch(() => taskForm.progress, (newVal) => {
  if (newVal === 100) {
    taskForm.status = 'completed';
  } else if (newVal > 0) {
    taskForm.status = 'in_progress';
  } else {
    taskForm.status = 'pending';
  }
});

function applyFilters() {
  router.get('/tasks', filterForm.value, { preserveState: true, replace: true });
}

function openCreateModal() {
  editingTask.value = null;
  taskForm.reset();
  taskForm.user_id = props.employees[0]?.id || '';
  taskForm.progress = 0;
  taskForm.status = 'pending';
  taskForm.task_date = filterForm.value.date || new Date().toISOString().split('T')[0];
  isModalOpen.value = true;
}

function openEditModal(task) {
  editingTask.value = task;
  // Normalize date string to strictly YYYY-MM-DD
  const rawDate = task.task_date ? String(task.task_date).split('T')[0] : new Date().toISOString().split('T')[0];
  
  taskForm.user_id = task.user_id;
  taskForm.title = task.title;
  taskForm.description = task.description || '';
  taskForm.progress = Number(task.progress) || 0;
  taskForm.status = task.status || (task.progress === 100 ? 'completed' : (task.progress > 0 ? 'in_progress' : 'pending'));
  taskForm.task_date = rawDate;
  isModalOpen.value = true;
}

function submitTask() {
  if (editingTask.value) {
    taskForm.put(`/tasks/${editingTask.value.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        isModalOpen.value = false;
        taskForm.reset();
      }
    });
  } else {
    taskForm.post('/tasks', {
      preserveScroll: true,
      onSuccess: () => {
        isModalOpen.value = false;
        taskForm.reset();
      }
    });
  }
}

function deleteTask(task) {
  if (confirm(t('confirmDeleteTask'))) {
    router.delete(`/tasks/${task.id}`, { preserveScroll: true });
  }
}
</script>

<template>
  <Head :title="t('navTasks')" />

  <AppLayout>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
          {{ t('navTasks') }}
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
          متابعة وتكليف المهام اليومية لكوادر الأقسام والكليات
        </p>
      </div>

      <button
        @click="openCreateModal"
        type="button"
        class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-sky-600 to-teal-500 hover:from-sky-500 hover:to-teal-400 active:scale-95 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-md shadow-sky-500/20 transition-all cursor-pointer"
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
          <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ t('department') }}</label>
          <select
            v-model="filterForm.department_id"
            @change="applyFilters"
            class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100"
          >
            <option value="">{{ t('allDepartments') }}</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
        </div>

        <!-- Employee Filter -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ t('assignTo') }}</label>
          <select
            v-model="filterForm.user_id"
            @change="applyFilters"
            class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100"
          >
            <option value="">{{ t('allEmployees') }}</option>
            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
              {{ emp.name }}
            </option>
          </select>
        </div>

        <!-- Status Filter -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ t('status') }}</label>
          <select
            v-model="filterForm.status"
            @change="applyFilters"
            class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100"
          >
            <option value="">{{ t('allStatuses') }}</option>
            <option value="pending">{{ t('statusPending') }}</option>
            <option value="in_progress">{{ t('statusInProgress') }}</option>
            <option value="completed">{{ t('statusCompleted') }}</option>
          </select>
        </div>

        <!-- Type Filter -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ t('taskType') }}</label>
          <select
            v-model="filterForm.task_type"
            @change="applyFilters"
            class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100"
          >
            <option value="">{{ t('allTypes') }}</option>
            <option value="assigned">{{ t('typeAssigned') }}</option>
            <option value="self_reported">{{ t('typeSelf') }}</option>
          </select>
        </div>

        <!-- Date Filter -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ t('taskDate') }}</label>
          <input
            v-model="filterForm.date"
            @change="applyFilters"
            type="date"
            class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100 font-mono"
          />
        </div>
      </div>
    </div>

    <!-- Tasks Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-start text-xs">
          <thead class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
            <tr>
              <th class="py-3.5 px-4 text-start">{{ t('taskTitle') }}</th>
              <th class="py-3.5 px-4 text-start">{{ t('assignTo') }}</th>
              <th class="py-3.5 px-4 text-start">{{ t('department') }}</th>
              <th class="py-3.5 px-4 text-center">{{ t('taskType') }}</th>
              <th class="py-3.5 px-4 text-center">{{ t('progress') }}</th>
              <th class="py-3.5 px-4 text-center">{{ t('status') }}</th>
              <th class="py-3.5 px-4 text-center">{{ t('taskDate') }}</th>
              <th class="py-3.5 px-4 text-center">{{ t('actions') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr 
              v-for="task in tasks.data" 
              :key="task.id"
              class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors"
            >
              <td class="py-3.5 px-4">
                <div class="font-bold text-slate-900 dark:text-white leading-snug">{{ task.title }}</div>
                <div v-if="task.description" class="text-[11px] text-slate-400 truncate max-w-xs mt-0.5">{{ task.description }}</div>
              </td>
              <td class="py-3.5 px-4 font-semibold text-slate-700 dark:text-slate-300">
                {{ task.user?.name }}
              </td>
              <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                {{ task.department?.name }}
              </td>
              <td class="py-3.5 px-4 text-center">
                <span 
                  v-if="task.task_type === 'assigned'" 
                  class="inline-flex items-center justify-center whitespace-nowrap text-[10px] font-bold px-2 py-0.5 rounded-md bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200 dark:border-sky-800/50"
                >
                  {{ t('typeAssigned') }}
                </span>
                <span 
                  v-else 
                  class="inline-flex items-center justify-center whitespace-nowrap text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50"
                >
                  {{ t('typeSelf') }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-center">
                <div class="inline-flex items-center gap-2">
                  <div class="w-14 bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                    <div 
                      :class="task.progress === 100 ? 'bg-emerald-500' : 'bg-sky-600'" 
                      class="h-full rounded-full transition-all" 
                      :style="{ width: `${task.progress}%` }"
                    ></div>
                  </div>
                  <span class="font-bold text-slate-700 dark:text-slate-300 font-mono text-[11px]">{{ task.progress }}%</span>
                </div>
              </td>
              
              <!-- Strict Unbreakable Status Badge -->
              <td class="py-3.5 px-4 text-center">
                <span 
                  :class="{
                    'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60': task.status === 'completed',
                    'bg-teal-50 text-teal-700 dark:bg-teal-950/70 dark:text-teal-300 border-teal-200 dark:border-teal-800/60': task.status === 'in_progress',
                    'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700': task.status === 'pending',
                  }"
                  class="inline-flex items-center justify-center whitespace-nowrap px-2.5 py-1 rounded-full text-[10px] font-bold border shrink-0"
                >
                  {{ task.status === 'completed' ? t('statusCompleted') : (task.status === 'in_progress' ? t('statusInProgress') : t('statusPending')) }}
                </span>
              </td>

              <td class="py-3.5 px-4 text-center text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                {{ task.task_date }}
              </td>

              <td class="py-3.5 px-4 text-center">
                <div class="inline-flex items-center gap-1">
                  <button
                    @click="openEditModal(task)"
                    type="button"
                    class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 transition-colors cursor-pointer"
                    :title="t('edit')"
                  >
                    <Edit2 class="w-3.5 h-3.5" />
                  </button>
                  <button
                    @click="deleteTask(task)"
                    type="button"
                    class="p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 transition-colors cursor-pointer"
                    :title="t('delete')"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="tasks.data.length === 0">
              <td colspan="8" class="py-12 text-center text-slate-400">
                {{ t('noTasksFound') }}
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
            {{ editingTask ? t('editTask') : t('assignNewTask') }}
          </h3>
          <button @click="isModalOpen = false" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitTask" class="space-y-4 text-xs">
          <!-- Assignee -->
          <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('assignTo') }} *</label>
            <select
              v-model="taskForm.user_id"
              required
              :disabled="!!editingTask"
              class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none disabled:opacity-60"
            >
              <option value="" disabled>{{ t('selectEmployeePlaceholder') }}</option>
              <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                {{ emp.name }} ({{ emp.department?.name || t('department') }})
              </option>
            </select>
          </div>

          <!-- Title -->
          <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('taskTitle') }} *</label>
            <input
              v-model="taskForm.title"
              type="text"
              required
              :placeholder="t('taskTitlePlaceholder')"
              class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500"
            />
          </div>

          <!-- Description -->
          <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('taskDesc') }}</label>
            <textarea
              v-model="taskForm.description"
              rows="3"
              :placeholder="t('taskDescPlaceholder')"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500"
            ></textarea>
          </div>

          <!-- Date & Progress Row -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('taskDate') }} *</label>
              <input
                v-model="taskForm.task_date"
                type="date"
                required
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none font-mono"
              />
            </div>

            <div v-if="editingTask">
              <div class="flex items-center justify-between mb-1">
                <label class="font-semibold text-slate-700 dark:text-slate-300">{{ t('progress') }}:</label>
                <span class="font-bold text-sky-600 dark:text-sky-400 font-mono">{{ taskForm.progress }}%</span>
              </div>
              <input
                v-model.number="taskForm.progress"
                type="range"
                min="0"
                max="100"
                step="5"
                class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-sky-600 mt-2"
              />
              <div class="text-[10px] text-slate-400 mt-1 flex items-center justify-between">
                <span>{{ t('status') }}:</span>
                <span class="font-bold text-slate-600 dark:text-slate-300">
                  {{ taskForm.progress === 100 ? t('statusCompleted') : (taskForm.progress > 0 ? t('statusInProgress') : t('statusPending')) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Modal Action Buttons -->
          <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
            <button
              @click="isModalOpen = false"
              type="button"
              class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 font-semibold text-slate-600 dark:text-slate-300 cursor-pointer"
            >
              {{ t('cancel') }}
            </button>
            <button
              :disabled="taskForm.processing"
              type="submit"
              class="px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-bold shadow-sm disabled:opacity-50 cursor-pointer transition-all"
            >
              {{ taskForm.processing ? 'جاري الحفظ...' : t('save') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
