<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
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
  AlertCircle,
  Briefcase
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

const page = usePage();
const authUser = computed(() => page.props.auth?.user || {});
const isHead = computed(() => authUser.value.role === 'head');

const filterForm = ref({
  department_id: props.filters.department_id || '',
  user_id: props.filters.user_id || '',
  status: props.filters.status || '',
  task_type: props.filters.task_type || '',
  date: props.filters.date ? props.filters.date.split('T')[0] : new Date().toISOString().split('T')[0],
});

const isModalOpen = ref(false);
const editingTask = ref(null);
const modalDepartmentId = ref('');

const taskForm = useForm({
  user_id: '',
  title: '',
  description: '',
  completion_rate: 0,
  status: 'pending',
  task_date: new Date().toISOString().split('T')[0],
});

// Dynamic filtered employees for assignment based on selected department in modal
const filteredModalEmployees = computed(() => {
  if (isHead.value) {
    return props.employees.filter(e => e.department_id === authUser.value.department_id);
  }
  if (!modalDepartmentId.value) {
    return props.employees;
  }
  return props.employees.filter(e => String(e.department_id) === String(modalDepartmentId.value));
});

// Watch completion_rate change inside edit modal to auto-update status field
watch(() => taskForm.completion_rate, (newVal) => {
  if (newVal === 100) {
    taskForm.status = 'completed';
  } else if (newVal > 0) {
    taskForm.status = 'in_progress';
  } else {
    taskForm.status = 'pending';
  }
});

function applyFilters() {
  const cleanParams = {};
  for (const [k, v] of Object.entries(filterForm.value)) {
    if (v !== '' && v !== null && v !== undefined) {
      cleanParams[k] = v;
    }
  }
  router.get('/tasks', cleanParams, { preserveState: true, replace: true });
}

function openCreateModal() {
  editingTask.value = null;
  taskForm.reset();
  
  if (isHead.value) {
    modalDepartmentId.value = authUser.value.department_id || '';
  } else {
    modalDepartmentId.value = filterForm.value.department_id || '';
  }

  const initialAssignee = filteredModalEmployees.value[0]?.id || '';
  taskForm.user_id = initialAssignee;
  taskForm.completion_rate = 0;
  taskForm.status = 'pending';
  taskForm.task_date = filterForm.value.date || new Date().toISOString().split('T')[0];
  isModalOpen.value = true;
}

function openEditModal(task) {
  editingTask.value = task;
  const rawDate = task.task_date ? String(task.task_date).split('T')[0] : new Date().toISOString().split('T')[0];
  
  modalDepartmentId.value = task.department_id || '';
  taskForm.user_id = task.user_id;
  taskForm.title = task.title;
  taskForm.description = task.description || '';
  taskForm.completion_rate = Number(task.progress) || 0;
  taskForm.status = task.status || (task.progress === 100 ? 'completed' : (task.progress > 0 ? 'in_progress' : 'pending'));
  taskForm.task_date = rawDate;
  isModalOpen.value = true;
}

function onModalDepartmentChange() {
  // Reset assignee to first employee in the selected department
  const firstInDept = filteredModalEmployees.value[0]?.id || '';
  taskForm.user_id = firstInDept;
}

function submitTask() {
  const submitData = {
    user_id: taskForm.user_id,
    title: taskForm.title,
    description: taskForm.description,
    progress: taskForm.completion_rate,
    status: taskForm.status,
    task_date: taskForm.task_date,
  };

  if (editingTask.value) {
    taskForm.transform(() => submitData).put(`/tasks/${editingTask.value.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        isModalOpen.value = false;
        taskForm.reset();
      }
    });
  } else {
    taskForm.transform(() => submitData).post('/tasks', {
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
          {{ t('tasksTitle') }}
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
          {{ t('tasksDesc') }}
        </p>
      </div>

      <button
        @click="openCreateModal"
        type="button"
        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-bold text-xs shadow-md shadow-sky-600/25 transition-all cursor-pointer"
      >
        <Plus class="w-4 h-4" />
        <span>{{ t('assignNewTask') }}</span>
      </button>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <!-- Date -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ t('taskDate') }}</label>
          <input
            v-model="filterForm.date"
            @change="applyFilters"
            type="date"
            class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100 font-mono"
          />
        </div>

        <!-- Department (Hidden or locked if Head) -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ t('department') }}</label>
          <select
            v-model="filterForm.department_id"
            @change="applyFilters"
            :disabled="isHead"
            class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100 disabled:opacity-60"
          >
            <option value="">{{ t('allDepartments') }}</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
        </div>

        <!-- Employee -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ t('tableEmployee') }}</label>
          <select
            v-model="filterForm.user_id"
            @change="applyFilters"
            class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100"
          >
            <option value="">{{ t('allEmployees') }}</option>
            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
              {{ emp.name }}{{ emp.job_title ? ` (${emp.job_title})` : '' }}
            </option>
          </select>
        </div>

        <!-- Status -->
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

        <!-- Task Type -->
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
      </div>
    </div>

    <!-- Tasks List Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-start text-xs">
          <thead class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
            <tr>
              <th class="py-3.5 px-4 text-start">{{ t('taskTitle') }}</th>
              <th class="py-3.5 px-4 text-start">{{ t('tableEmployee') }}</th>
              <th class="py-3.5 px-4 text-start">{{ t('tableDepartment') }}</th>
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
              class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors"
            >
              <td class="py-3.5 px-4">
                <div class="font-bold text-slate-900 dark:text-white">{{ task.title }}</div>
                <div v-if="task.description" class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">
                  {{ task.description }}
                </div>
              </td>

              <td class="py-3.5 px-4">
                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ task.user?.name }}</div>
                <div v-if="task.user?.job_title" class="text-[10px] text-sky-600 dark:text-sky-400 font-semibold">
                  {{ task.user.job_title }}
                </div>
              </td>

              <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                {{ task.department?.name }}
              </td>

              <td class="py-3.5 px-4 text-center">
                <span 
                  :class="task.task_type === 'assigned' ? 'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300'"
                  class="inline-flex items-center justify-center whitespace-nowrap px-2 py-0.5 rounded-md text-[10px] font-bold shrink-0"
                >
                  {{ task.task_type === 'assigned' ? t('typeAssigned') : t('typeSelf') }}
                </span>
              </td>

              <td class="py-3.5 px-4 text-center">
                <div class="inline-flex items-center gap-2">
                  <div class="w-16 bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-sky-600 h-full rounded-full" :style="{ width: `${task.progress}%` }"></div>
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

    <!-- Create / Edit Modal with Cascading Department Filter -->
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
          <!-- Department Selector Filter (For Admin to narrow down Staff, or locked for Head) -->
          <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1 flex items-center justify-between">
              <span>{{ t('selectDepartmentFirst') }}</span>
              <span v-if="isHead" class="text-[10px] text-sky-600 font-bold bg-sky-50 dark:bg-sky-950 px-2 py-0.5 rounded">
                {{ t('yourDepartment') }}
              </span>
            </label>
            <select
              v-model="modalDepartmentId"
              @change="onModalDepartmentChange"
              :disabled="isHead || !!editingTask"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none disabled:opacity-60"
            >
              <option value="">{{ t('allDeptsLabel') }}</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                {{ dept.name }}
              </option>
            </select>
          </div>

          <!-- Assignee Selector (Shows filtered staff with Job Title ONLY when present) -->
          <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('assignTo') }} *</label>
            <select
              v-model="taskForm.user_id"
              required
              :disabled="!!editingTask"
              class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none disabled:opacity-60 font-medium"
            >
              <option value="" disabled>{{ t('selectEmployeePlaceholder') }}</option>
              <option v-for="emp in filteredModalEmployees" :key="emp.id" :value="emp.id">
                {{ emp.name }}{{ emp.job_title ? ` (${emp.job_title})` : '' }}
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
                <span class="font-bold text-sky-600 dark:text-sky-400 font-mono">{{ taskForm.completion_rate }}%</span>
              </div>
              <input
                v-model.number="taskForm.completion_rate"
                type="range"
                min="0"
                max="100"
                step="5"
                class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-sky-600 mt-2"
              />
              <div class="text-[10px] text-slate-400 mt-1 flex items-center justify-between">
                <span>{{ t('status') }}:</span>
                <span class="font-bold text-slate-600 dark:text-slate-300">
                  {{ taskForm.completion_rate === 100 ? t('statusCompleted') : (taskForm.completion_rate > 0 ? t('statusInProgress') : t('statusPending')) }}
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
              {{ taskForm.processing ? t('saving') : t('save') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
