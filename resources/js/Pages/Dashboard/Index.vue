<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { t } from '@/i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
  CheckSquare,
  CheckCircle2,
  Clock,
  AlertCircle,
  TrendingUp,
  MapPin,
  Building,
  Users,
  ArrowRight,
  Filter
} from 'lucide-vue-next';

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({})
  },
  departments: {
    type: Array,
    default: () => []
  },
  departmentStats: {
    type: Array,
    default: () => []
  },
  recentTasks: {
    type: Array,
    default: () => []
  },
  recentAttendanceLogs: {
    type: Array,
    default: () => []
  },
  selectedDepartmentId: {
    type: [String, Number],
    default: ''
  }
});

function onDepartmentFilterChange(e) {
  const deptId = e.target.value;
  router.get('/dashboard', { department_id: deptId }, { preserveState: true, replace: true });
}
</script>

<template>
  <Head :title="t('navDashboard')" />

  <AppLayout>
    <!-- Page Header & Filter -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
          {{ t('navDashboard') }}
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
          متابعة مؤشرات أداء الأقسام والمهام الميدانية وسجلات الحضور المباشرة
        </p>
      </div>

      <!-- Department Filter if Admin -->
      <div v-if="departments.length > 1" class="flex items-center gap-2 bg-white dark:bg-slate-900 p-1.5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
        <Filter class="w-4 h-4 text-slate-400 ms-2" />
        <select
          :value="selectedDepartmentId || ''"
          @change="onDepartmentFilterChange"
          class="bg-transparent text-xs font-semibold text-slate-700 dark:text-slate-200 py-1.5 pe-4 outline-none cursor-pointer"
        >
          <option value="">{{ t('all') }} (جميع الأقسام)</option>
          <option v-for="dept in departments" :key="dept.id" :value="dept.id">
            {{ dept.name }}
          </option>
        </select>
      </div>
    </div>

    <!-- KPI Widgets Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
      <!-- Total Tasks -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs hover:border-brand-500/50 transition-all">
        <div class="flex items-center justify-between gap-2 mb-3">
          <span class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ t('totalTasks') }}</span>
          <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 flex items-center justify-center">
            <CheckSquare class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl font-black text-slate-900 dark:text-white">{{ stats.total_tasks }}</div>
        <div class="text-[11px] text-slate-400 mt-1">مهام اليوم المسجلة</div>
      </div>

      <!-- Completed -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs hover:border-emerald-500/50 transition-all">
        <div class="flex items-center justify-between gap-2 mb-3">
          <span class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ t('completedTasks') }}</span>
          <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center">
            <CheckCircle2 class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ stats.completed_tasks }}</div>
        <div class="text-[11px] text-slate-400 mt-1">نسبة الإنجاز 100%</div>
      </div>

      <!-- In Progress / Pending -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs hover:border-amber-500/50 transition-all">
        <div class="flex items-center justify-between gap-2 mb-3">
          <span class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ t('inProgressTasks') }}</span>
          <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center">
            <Clock class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ stats.in_progress_tasks }}</div>
        <div class="text-[11px] text-slate-400 mt-1">+ {{ stats.pending_tasks }} معلقة</div>
      </div>

      <!-- Field Attendance / Rate -->
      <div class="bg-gradient-to-br from-brand-600 to-teal-700 text-white rounded-3xl p-4 sm:p-5 shadow-lg shadow-brand-600/20">
        <div class="flex items-center justify-between gap-2 mb-3">
          <span class="text-xs font-bold text-brand-100">{{ t('avgRate') }}</span>
          <div class="w-8 h-8 rounded-xl bg-white/15 backdrop-blur-md text-white flex items-center justify-center">
            <TrendingUp class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl font-black">{{ stats.avg_progress }}%</div>
        <div class="text-[11px] text-brand-100/90 mt-1">
          {{ stats.today_attendance_count }} موظف سجلوا حضوراً بالـ GPS
        </div>
      </div>
    </div>

    <!-- Two Columns: Department Breakdown & Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      <!-- Department Progress Overview -->
      <div class="lg:col-span-1 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <Building class="w-4 h-4 text-brand-600" />
            <span>أداء الأقسام المؤسسية</span>
          </h2>
          <Link href="/departments" class="text-xs font-bold text-brand-600 hover:underline">
            إدارة
          </Link>
        </div>

        <div class="space-y-4">
          <div 
            v-for="dept in departmentStats" 
            :key="dept.id"
            class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800"
          >
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="font-bold text-slate-800 dark:text-slate-200">{{ dept.name }}</span>
              <span class="text-slate-500 font-semibold">{{ dept.completed_tasks_count }} / {{ dept.today_tasks_count }} مهمة</span>
            </div>
            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
              <div 
                class="bg-brand-600 h-full rounded-full transition-all"
                :style="{ width: dept.today_tasks_count > 0 ? `${(dept.completed_tasks_count / dept.today_tasks_count) * 100}%` : '0%' }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Tasks Feed -->
      <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <CheckSquare class="w-4 h-4 text-brand-600" />
            <span>{{ t('recentActivity') }}</span>
          </h2>
          <Link href="/tasks" class="text-xs font-bold text-brand-600 hover:underline flex items-center gap-1">
            <span>عرض كافة المهام</span>
            <ArrowRight class="w-3 h-3 rtl:rotate-180" />
          </Link>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-800">
          <div 
            v-for="task in recentTasks" 
            :key="task.id"
            class="py-3 flex items-center justify-between gap-3 text-xs"
          >
            <div class="flex-1 min-w-0">
              <div class="font-bold text-slate-800 dark:text-slate-200 truncate">{{ task.title }}</div>
              <div class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-2">
                <span>{{ task.user?.name }}</span>
                <span>•</span>
                <span>{{ task.department?.name }}</span>
              </div>
            </div>

            <!-- Progress chip -->
            <div class="flex items-center gap-2">
              <span class="font-bold text-slate-700 dark:text-slate-300">{{ task.progress }}%</span>
              <span 
                :class="{
                  'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300': task.status === 'completed',
                  'bg-teal-100 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300': task.status === 'in_progress',
                  'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300': task.status === 'pending',
                }"
                class="px-2 py-0.5 rounded-full text-[10px] font-bold"
              >
                {{ task.status === 'completed' ? 'مكتملة' : (task.status === 'in_progress' ? 'جارية' : 'معلقة') }}
              </span>
            </div>
          </div>

          <div v-if="recentTasks.length === 0" class="py-8 text-center text-slate-400 text-xs">
            لا توجد مهام مسجلة لهذا اليوم حتى الآن.
          </div>
        </div>
      </div>
    </div>

    <!-- Recent GPS Attendance Feed -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
          <MapPin class="w-4 h-4 text-brand-600" />
          <span>{{ t('recentLocations') }}</span>
        </h2>
        <Link href="/attendance" class="text-xs font-bold text-brand-600 hover:underline flex items-center gap-1">
          <span>خريطة المواقع التفاعلية</span>
          <ArrowRight class="w-3 h-3 rtl:rotate-180" />
        </Link>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <div 
          v-for="log in recentAttendanceLogs" 
          :key="log.id"
          class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 text-xs"
        >
          <div class="font-bold text-slate-900 dark:text-white">{{ log.user?.name }}</div>
          <div class="text-[10px] text-slate-400">{{ log.user?.department?.name }}</div>
          <div class="mt-2 flex items-center justify-between text-[10px] text-slate-500 border-t border-slate-200/60 dark:border-slate-700/60 pt-2">
            <span>⏰ {{ log.log_time }}</span>
            <a 
              :href="`https://www.google.com/maps?q=${log.latitude},${log.longitude}`" 
              target="_blank" 
              class="text-brand-600 font-semibold hover:underline"
            >
              عرض الخريطة ↗
            </a>
          </div>
        </div>

        <div v-if="recentAttendanceLogs.length === 0" class="col-span-4 py-8 text-center text-slate-400 text-xs">
          لم يتم تسجيل حضور ميداني اليوم بعد.
        </div>
      </div>
    </div>
  </AppLayout>
</template>
