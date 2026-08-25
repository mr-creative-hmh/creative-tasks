<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { t } from '@/i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
  FileBarChart,
  Download,
  Calendar,
  Filter,
  CheckCircle2,
  TrendingUp,
  Award,
  Users,
  Building
} from 'lucide-vue-next';

const props = defineProps({
  tasks: {
    type: Array,
    default: () => []
  },
  summary: {
    type: Object,
    default: () => ({ total: 0, completed: 0, in_progress: 0, pending: 0, avg_progress: 0 })
  },
  employeePerformance: {
    type: Array,
    default: () => []
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
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
});

function applyFilters() {
  router.get('/reports', filterForm.value, { preserveState: true, replace: true });
}

function exportPdf() {
  const params = new URLSearchParams(filterForm.value).toString();
  window.open(`/reports/pdf?${params}`, '_blank');
}
</script>

<template>
  <Head :title="t('navReports')" />

  <AppLayout>
    <!-- Header with PDF Export Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
          {{ t('navReports') }}
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
          تحليل مؤشرات الإنجاز الدورية وسحب تقارير الأداء الميداني والمؤسسي بصيغة PDF
        </p>
      </div>

      <button
        @click="exportPdf"
        type="button"
        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 active:scale-95 text-white font-bold text-xs shadow-md shadow-rose-600/25 transition-all cursor-pointer"
      >
        <Download class="w-4 h-4" />
        <span>{{ t('exportPdf') }}</span>
      </button>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Date From -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-500 mb-1">{{ t('dateFrom') }}</label>
          <input
            v-model="filterForm.date_from"
            @change="applyFilters"
            type="date"
            class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100"
          />
        </div>

        <!-- Date To -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-500 mb-1">{{ t('dateTo') }}</label>
          <input
            v-model="filterForm.date_to"
            @change="applyFilters"
            type="date"
            class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100"
          />
        </div>

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
      </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
      <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
        <div class="text-[11px] font-semibold text-slate-500 mb-1">{{ t('totalTasks') }}</div>
        <div class="text-2xl font-black text-slate-900 dark:text-white">{{ summary.total }}</div>
      </div>

      <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
        <div class="text-[11px] font-semibold text-slate-500 mb-1">{{ t('completedTasks') }}</div>
        <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ summary.completed }}</div>
      </div>

      <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs">
        <div class="text-[11px] font-semibold text-slate-500 mb-1">قيد التنفيذ / معلقة</div>
        <div class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ summary.in_progress + summary.pending }}</div>
      </div>

      <div class="bg-gradient-to-br from-brand-600 to-teal-700 text-white rounded-3xl p-4 shadow-md shadow-brand-600/20">
        <div class="text-[11px] font-semibold text-brand-100 mb-1">{{ t('avgRate') }}</div>
        <div class="text-2xl font-black">{{ summary.avg_progress }}%</div>
      </div>
    </div>

    <!-- Employee Performance Leaderboard Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden mb-6">
      <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <h3 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
          <Award class="w-4 h-4 text-brand-600" />
          <span>ملخص أداء الموظفين خلال الفترة المحددة</span>
        </h3>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-start text-xs">
          <thead class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 text-slate-500 font-bold">
            <tr>
              <th class="py-3 px-4 text-start">الموظف</th>
              <th class="py-3 px-4 text-start">القسم</th>
              <th class="py-3 px-4 text-center">إجمالي المهام</th>
              <th class="py-3 px-4 text-center">المهام المكتملة</th>
              <th class="py-3 px-4 text-center">متوسط نسبة الإنجاز</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr 
              v-for="emp in employeePerformance" 
              :key="emp.user_id"
              class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors"
            >
              <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                {{ emp.user_name }}
              </td>
              <td class="py-3 px-4 text-slate-500">
                {{ emp.department_name }}
              </td>
              <td class="py-3 px-4 text-center font-bold">
                {{ emp.total_tasks }}
              </td>
              <td class="py-3 px-4 text-center font-bold text-emerald-600 dark:text-emerald-400">
                {{ emp.completed_tasks }}
              </td>
              <td class="py-3 px-4 text-center">
                <div class="inline-flex items-center gap-2">
                  <div class="w-20 bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-brand-600 h-full rounded-full" :style="{ width: `${emp.avg_progress}%` }"></div>
                  </div>
                  <span class="font-bold text-slate-800 dark:text-slate-200">{{ emp.avg_progress }}%</span>
                </div>
              </td>
            </tr>

            <tr v-if="employeePerformance.length === 0">
              <td colspan="5" class="py-10 text-center text-slate-400">
                لا توجد بيانات أداء مسجلة خلال هذه الفترة.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
