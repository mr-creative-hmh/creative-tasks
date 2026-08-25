<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { t } from '@/i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
  FileBarChart,
  Download,
  Calendar,
  Filter,
  Users,
  Award,
  TrendingUp,
  Building2,
  FileSpreadsheet,
  PieChart,
  BarChart3,
  CheckCircle2,
  Clock,
  AlertCircle,
  Sparkles,
  ArrowUpRight,
  ListTodo,
  Printer
} from 'lucide-vue-next';

const props = defineProps({
  tasks: {
    type: Array,
    default: () => []
  },
  employeePerformance: {
    type: Array,
    default: () => []
  },
  departmentPerformance: {
    type: Array,
    default: () => []
  },
  summary: {
    type: Object,
    default: () => ({
      total: 0,
      completed: 0,
      in_progress: 0,
      pending: 0,
      assigned_type: 0,
      self_type: 0,
      avg_progress: 0,
    })
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
  date_from: props.filters.date_from ? String(props.filters.date_from).split('T')[0] : '',
  date_to: props.filters.date_to ? String(props.filters.date_to).split('T')[0] : '',
});

// Helper for local YYYY-MM-DD format (avoids UTC timezone shift)
function formatLocalDate(d) {
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

// Track active preset state dynamically
const activePreset = ref('month');

function getCleanFilterParams() {
  const clean = {};
  for (const [key, val] of Object.entries(filterForm.value)) {
    if (val !== '' && val !== null && val !== undefined) {
      clean[key] = val;
    }
  }
  return clean;
}

function applyFilters() {
  router.get('/reports', getCleanFilterParams(), { preserveState: true, replace: true });
}

function onManualDateChange() {
  activePreset.value = 'custom';
  applyFilters();
}

function setDatePreset(type) {
  activePreset.value = type;
  const now = new Date();
  const todayStr = formatLocalDate(now);

  if (type === 'today') {
    filterForm.value.date_from = todayStr;
    filterForm.value.date_to = todayStr;
  } else if (type === 'week') {
    const day = now.getDay(); // 0 is Sunday, 6 is Saturday
    const diff = (day + 1) % 7; // distance from Saturday
    const startOfWeek = new Date(now);
    startOfWeek.setDate(now.getDate() - diff);
    filterForm.value.date_from = formatLocalDate(startOfWeek);
    filterForm.value.date_to = todayStr;
  } else if (type === 'month') {
    const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
    filterForm.value.date_from = formatLocalDate(startOfMonth);
    filterForm.value.date_to = todayStr;
  } else if (type === 'all') {
    filterForm.value.date_from = '';
    filterForm.value.date_to = '';
  }
  applyFilters();
}

function exportPdf() {
  const clean = getCleanFilterParams();
  const queryString = new URLSearchParams(clean).toString();
  window.open(`/reports/pdf?${queryString}`, '_blank');
}

function exportExcel() {
  const clean = getCleanFilterParams();
  const queryString = new URLSearchParams(clean).toString();
  window.location.href = `/reports/excel?${queryString}`;
}
</script>

<template>
  <Head :title="t('navReports')" />

  <AppLayout>
    <!-- Header with PDF & Excel Export Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
          {{ t('navReports') }}
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
          تحليل مؤشرات الإنجاز الدورية، الرسوم البيانية، وتصدير التقارير الرسمية المعتمدة (PDF & XLSX)
        </p>
      </div>

      <!-- Export Action Group -->
      <div class="flex items-center gap-2">
        <!-- Excel Export Button -->
        <button
          @click="exportExcel"
          type="button"
          class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all cursor-pointer"
        >
          <FileSpreadsheet class="w-4 h-4" />
          <span>تصدير إكسل (XLSX)</span>
        </button>

        <!-- PDF Export Button -->
        <button
          @click="exportPdf"
          type="button"
          class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 active:scale-95 text-white font-bold text-xs shadow-md shadow-rose-600/25 transition-all cursor-pointer"
        >
          <Printer class="w-4 h-4" />
          <span>طباعة التقرير / PDF</span>
        </button>
      </div>
    </div>

    <!-- Quick Date Presets Bar with Dynamic Highlight Selection -->
    <div class="flex flex-wrap items-center justify-between gap-2 mb-4 bg-white dark:bg-slate-900 p-2.5 rounded-2xl border border-slate-200 dark:border-slate-800 text-xs">
      <div class="flex items-center gap-1.5 font-bold text-slate-700 dark:text-slate-300">
        <Calendar class="w-4 h-4 text-sky-600" />
        <span>الفترات الزمنية السريعة:</span>
      </div>
      <div class="flex items-center gap-1.5">
        <button
          @click="setDatePreset('today')"
          type="button"
          :class="activePreset === 'today' 
            ? 'bg-sky-600 text-white font-bold shadow-md shadow-sky-600/25' 
            : 'bg-slate-100 dark:bg-slate-800 hover:bg-sky-50 dark:hover:bg-sky-950/60 text-slate-700 dark:text-slate-300 font-medium'"
          class="px-3.5 py-1.5 rounded-xl transition-all active:scale-95 cursor-pointer text-xs"
        >
          {{ t('today') }}
        </button>

        <button
          @click="setDatePreset('week')"
          type="button"
          :class="activePreset === 'week' 
            ? 'bg-sky-600 text-white font-bold shadow-md shadow-sky-600/25' 
            : 'bg-slate-100 dark:bg-slate-800 hover:bg-sky-50 dark:hover:bg-sky-950/60 text-slate-700 dark:text-slate-300 font-medium'"
          class="px-3.5 py-1.5 rounded-xl transition-all active:scale-95 cursor-pointer text-xs"
        >
          {{ t('thisWeek') }}
        </button>

        <button
          @click="setDatePreset('month')"
          type="button"
          :class="activePreset === 'month' 
            ? 'bg-sky-600 text-white font-bold shadow-md shadow-sky-600/25' 
            : 'bg-slate-100 dark:bg-slate-800 hover:bg-sky-50 dark:hover:bg-sky-950/60 text-slate-700 dark:text-slate-300 font-medium'"
          class="px-3.5 py-1.5 rounded-xl transition-all active:scale-95 cursor-pointer text-xs"
        >
          {{ t('thisMonth') }}
        </button>

        <button
          @click="setDatePreset('all')"
          type="button"
          :class="activePreset === 'all' 
            ? 'bg-sky-600 text-white font-bold shadow-md shadow-sky-600/25' 
            : 'bg-slate-100 dark:bg-slate-800 hover:bg-sky-50 dark:hover:bg-sky-950/60 text-slate-700 dark:text-slate-300 font-medium'"
          class="px-3.5 py-1.5 rounded-xl transition-all active:scale-95 cursor-pointer text-xs"
        >
          {{ t('all') }} (كافة الفترات)
        </button>
      </div>
    </div>

    <!-- Multi-criteria Filter Panel -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
        <!-- Date From -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ t('dateFrom') }}</label>
          <input
            v-model="filterForm.date_from"
            @change="onManualDateChange"
            type="date"
            class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100 font-mono"
          />
        </div>

        <!-- Date To -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ t('dateTo') }}</label>
          <input
            v-model="filterForm.date_to"
            @change="onManualDateChange"
            type="date"
            class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100 font-mono"
          />
        </div>

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
          <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ t('tableEmployee') }}</label>
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
            <option value="completed">{{ t('statusCompleted') }}</option>
            <option value="in_progress">{{ t('statusInProgress') }}</option>
            <option value="pending">{{ t('statusPending') }}</option>
          </select>
        </div>

        <!-- Task Type Filter -->
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

    <!-- Summary KPI Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
      <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
        <div class="text-[10px] font-bold text-slate-400 mb-1 uppercase">{{ t('totalTasks') }}</div>
        <div class="text-2xl font-black text-slate-900 dark:text-white font-mono">{{ summary.total }}</div>
      </div>

      <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
        <div class="text-[10px] font-bold text-slate-400 mb-1 uppercase">{{ t('completedTasks') }}</div>
        <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">{{ summary.completed }}</div>
      </div>

      <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
        <div class="text-[10px] font-bold text-slate-400 mb-1 uppercase">{{ t('inProgressTasks') }}</div>
        <div class="text-2xl font-black text-teal-600 dark:text-teal-400 font-mono">{{ summary.in_progress }}</div>
      </div>

      <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
        <div class="text-[10px] font-bold text-slate-400 mb-1 uppercase">{{ t('statusPending') }}</div>
        <div class="text-2xl font-black text-slate-500 font-mono">{{ summary.pending }}</div>
      </div>

      <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
        <div class="text-[10px] font-bold text-slate-400 mb-1 uppercase">تكليف / ذاتي</div>
        <div class="text-base font-extrabold text-slate-700 dark:text-slate-300 font-mono mt-1">
          {{ summary.assigned_type }} <span class="text-xs text-slate-400">/</span> {{ summary.self_type }}
        </div>
      </div>

      <div class="bg-gradient-to-br from-sky-600 to-teal-700 text-white rounded-3xl p-4 shadow-md shadow-sky-600/20">
        <div class="text-[10px] font-bold text-sky-100 mb-1 uppercase">{{ t('avgRate') }}</div>
        <div class="text-2xl font-black font-mono">{{ summary.avg_progress }}%</div>
      </div>
    </div>

    <!-- Charts Row (Visual Analytics) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      
      <!-- 1. Task Status Donut Chart / Breakdown -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
        <div>
          <div class="flex items-center gap-2 mb-4 pb-2 border-b border-slate-100 dark:border-slate-800">
            <PieChart class="w-4 h-4 text-sky-600" />
            <h2 class="text-xs font-bold text-slate-900 dark:text-white">توزيع حالات المهام (Status Breakdown)</h2>
          </div>

          <!-- Donut Graphic -->
          <div class="flex items-center justify-center my-4">
            <div class="relative w-36 h-36 flex items-center justify-center">
              <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                <!-- Background Circle -->
                <path
                  class="text-slate-100 dark:text-slate-800"
                  stroke-width="3.8"
                  stroke="currentColor"
                  fill="none"
                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                />
                <!-- Completed Arc -->
                <path
                  class="text-emerald-500"
                  stroke-dasharray="100, 100"
                  :stroke-dashoffset="100 - (summary.total > 0 ? (summary.completed / summary.total) * 100 : 0)"
                  stroke-width="3.8"
                  stroke-linecap="round"
                  stroke="currentColor"
                  fill="none"
                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                />
              </svg>
              <div class="absolute flex flex-col items-center justify-center text-center">
                <span class="text-xl font-black text-slate-900 dark:text-white font-mono">{{ summary.avg_progress }}%</span>
                <span class="text-[9px] text-slate-400 font-semibold">معدل الإنجاز</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Legend Bars -->
        <div class="space-y-2 text-xs pt-2 border-t border-slate-100 dark:border-slate-800">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
              <span class="text-slate-600 dark:text-slate-300 font-medium">{{ t('statusCompleted') }}</span>
            </div>
            <span class="font-bold text-slate-900 dark:text-white font-mono">{{ summary.completed }} ({{ Math.round((summary.completed / (summary.total || 1)) * 100) }}%)</span>
          </div>

          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
              <span class="text-slate-600 dark:text-slate-300 font-medium">{{ t('statusInProgress') }}</span>
            </div>
            <span class="font-bold text-slate-900 dark:text-white font-mono">{{ summary.in_progress }} ({{ Math.round((summary.in_progress / (summary.total || 1)) * 100) }}%)</span>
          </div>

          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
              <span class="text-slate-600 dark:text-slate-300 font-medium">{{ t('statusPending') }}</span>
            </div>
            <span class="font-bold text-slate-900 dark:text-white font-mono">{{ summary.pending }} ({{ Math.round((summary.pending / (summary.total || 1)) * 100) }}%)</span>
          </div>
        </div>
      </div>

      <!-- 2. Department Performance Comparison Chart -->
      <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100 dark:border-slate-800">
          <div class="flex items-center gap-2">
            <BarChart3 class="w-4 h-4 text-sky-600" />
            <h2 class="text-xs font-bold text-slate-900 dark:text-white">مقارنة أداء الكليات والأقسام (Department Performance)</h2>
          </div>
          <span class="text-[11px] text-slate-400 font-mono">{{ departmentPerformance.length }} قسم</span>
        </div>

        <div class="space-y-4">
          <div 
            v-for="dept in departmentPerformance" 
            :key="dept.department_id"
            class="space-y-1.5"
          >
            <div class="flex items-center justify-between text-xs">
              <div class="flex items-center gap-2">
                <Building2 class="w-3.5 h-3.5 text-sky-600" />
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ dept.department_name }}</span>
              </div>
              <div class="flex items-center gap-3">
                <span class="text-[11px] text-slate-400 font-mono">{{ dept.completed_tasks }} / {{ dept.total_tasks }} مهمة</span>
                <span class="font-black text-sky-600 dark:text-sky-400 font-mono text-xs">{{ dept.avg_progress }}%</span>
              </div>
            </div>

            <!-- Double-layered Progress Bar -->
            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-3 overflow-hidden">
              <div 
                :class="dept.avg_progress >= 80 ? 'bg-gradient-to-r from-emerald-500 to-teal-400' : 'bg-gradient-to-r from-sky-600 to-teal-500'" 
                class="h-full rounded-full transition-all duration-700 shadow-sm"
                :style="{ width: `${dept.avg_progress}%` }"
              ></div>
            </div>
          </div>

          <div v-if="departmentPerformance.length === 0" class="py-12 text-center text-slate-400 text-xs">
            {{ t('noTasksFound') }}
          </div>
        </div>
      </div>
    </div>

    <!-- 3. Detailed Tasks Records Table (Showing exact viewed data) -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden mb-6">
      <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <h3 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
          <ListTodo class="w-4 h-4 text-sky-600" />
          <span>السجل التفصيلي للمهام المصفاة (Filtered Detailed Tasks)</span>
        </h3>
        <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
          {{ tasks.length }} مهمة ظاهرة
        </span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-start text-xs">
          <thead class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
            <tr>
              <th class="py-3.5 px-4 text-start">#</th>
              <th class="py-3.5 px-4 text-start">{{ t('taskTitle') }}</th>
              <th class="py-3.5 px-4 text-start">{{ t('tableEmployee') }}</th>
              <th class="py-3.5 px-4 text-start">{{ t('tableDepartment') }}</th>
              <th class="py-3.5 px-4 text-center">{{ t('taskType') }}</th>
              <th class="py-3.5 px-4 text-center">{{ t('progress') }}</th>
              <th class="py-3.5 px-4 text-center">{{ t('status') }}</th>
              <th class="py-3.5 px-4 text-center">{{ t('taskDate') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr 
              v-for="(task, index) in tasks" 
              :key="task.id"
              class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors"
            >
              <td class="py-3 px-4 font-mono text-slate-400 text-center">
                {{ index + 1 }}
              </td>

              <td class="py-3 px-4">
                <div class="font-bold text-slate-900 dark:text-white">{{ task.title }}</div>
                <div v-if="task.description" class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">
                  {{ task.description }}
                </div>
              </td>

              <td class="py-3 px-4">
                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ task.user?.name || '-' }}</span>
              </td>

              <td class="py-3 px-4 text-slate-500 dark:text-slate-400">
                {{ task.department?.name || '-' }}
              </td>

              <td class="py-3 px-4 text-center">
                <span 
                  :class="task.task_type === 'assigned' ? 'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300'"
                  class="inline-flex items-center justify-center whitespace-nowrap px-2 py-0.5 rounded-md text-[10px] font-bold shrink-0"
                >
                  {{ task.task_type === 'assigned' ? t('typeAssigned') : t('typeSelf') }}
                </span>
              </td>

              <td class="py-3 px-4 text-center">
                <div class="inline-flex items-center gap-2">
                  <div class="w-16 bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-sky-600 h-full rounded-full" :style="{ width: `${task.progress}%` }"></div>
                  </div>
                  <span class="font-bold text-slate-700 dark:text-slate-300 font-mono text-[11px]">{{ task.progress }}%</span>
                </div>
              </td>

              <td class="py-3 px-4 text-center">
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

              <td class="py-3 px-4 text-center text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                {{ task.task_date }}
              </td>
            </tr>

            <tr v-if="tasks.length === 0">
              <td colspan="8" class="py-12 text-center text-slate-400">
                {{ t('noTasksFound') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 4. Employee Performance Leaderboard Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden mb-6">
      <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <h3 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
          <Award class="w-4 h-4 text-amber-500" />
          <span>لوحة شرف إنجاز الكوادر (Staff Performance Leaderboard)</span>
        </h3>
        <span class="text-xs text-slate-400 font-mono">{{ employeePerformance.length }} موظف</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-start text-xs">
          <thead class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
            <tr>
              <th class="py-3 px-4 text-start">{{ t('tableEmployee') }}</th>
              <th class="py-3 px-4 text-start">{{ t('tableDepartment') }}</th>
              <th class="py-3 px-4 text-center">{{ t('totalTasks') }}</th>
              <th class="py-3 px-4 text-center">{{ t('completedTasks') }}</th>
              <th class="py-3 px-4 text-center">{{ t('inProgressTasks') }}</th>
              <th class="py-3 px-4 text-center">{{ t('avgRate') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr 
              v-for="(emp, idx) in employeePerformance" 
              :key="emp.user_id"
              class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors"
            >
              <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                <div class="flex items-center gap-2">
                  <span 
                    :class="idx === 0 ? 'bg-amber-400 text-amber-950 font-black' : (idx === 1 ? 'bg-slate-300 text-slate-800 font-bold' : (idx === 2 ? 'bg-amber-700 text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-500'))" 
                    class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] shrink-0"
                  >
                    {{ idx + 1 }}
                  </span>
                  <span>{{ emp.user_name }}</span>
                </div>
              </td>
              <td class="py-3 px-4 text-slate-500 dark:text-slate-400">
                {{ emp.department_name }}
              </td>
              <td class="py-3 px-4 text-center font-bold font-mono">
                {{ emp.total_tasks }}
              </td>
              <td class="py-3 px-4 text-center font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                {{ emp.completed_tasks }}
              </td>
              <td class="py-3 px-4 text-center font-bold text-teal-600 dark:text-teal-400 font-mono">
                {{ emp.in_progress_tasks }}
              </td>
              <td class="py-3 px-4 text-center">
                <div class="inline-flex items-center gap-2">
                  <div class="w-20 bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                    <div 
                      :class="emp.avg_progress === 100 ? 'bg-emerald-500' : 'bg-sky-600'" 
                      class="h-full rounded-full transition-all" 
                      :style="{ width: `${emp.avg_progress}%` }"
                    ></div>
                  </div>
                  <span class="font-bold text-slate-800 dark:text-slate-200 font-mono text-[11px]">{{ emp.avg_progress }}%</span>
                </div>
              </td>
            </tr>

            <tr v-if="employeePerformance.length === 0">
              <td colspan="6" class="py-10 text-center text-slate-400">
                {{ t('noTasksFound') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
