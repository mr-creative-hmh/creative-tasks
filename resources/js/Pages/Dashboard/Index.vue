<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { t } from '@/i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageBanner from '@/Components/PageBanner.vue';
import {
  CheckSquare,
  Clock,
  CheckCircle2,
  AlertCircle,
  TrendingUp,
  MapPin,
  Building,
  ArrowRight,
  Sparkles
} from 'lucide-vue-next';

defineProps({
  stats: {
    type: Object,
    default: () => ({
      total_tasks: 0,
      completed_tasks: 0,
      in_progress_tasks: 0,
      pending_tasks: 0,
      avg_progress: 0,
      today_attendance_count: 0
    })
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
  }
});
</script>

<template>
  <Head :title="t('navDashboard')" />

  <AppLayout>
    <!-- Unified Page Banner -->
    <PageBanner
      :title="t('appName')"
      :subtitle="t('dashboardSubtitle')"
      :badge="t('appSubtitle')"
      :icon="TrendingUp"
    >
      <template #actions>
        <div class="flex items-center gap-2">
          <Link
            href="/tasks"
            class="h-10 inline-flex items-center justify-center gap-2 px-4 rounded-xl bg-accent bg-accent-hover text-white font-bold text-xs shadow-accent active:scale-95 transition-all cursor-pointer"
          >
            <CheckSquare class="w-4 h-4" />
            <span>{{ t('navTasks') }}</span>
          </Link>
          <Link
            href="/attendance"
            class="h-10 inline-flex items-center justify-center gap-2 px-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/80 text-slate-700 dark:text-slate-200 font-bold text-xs shadow-xs active:scale-95 transition-all cursor-pointer"
          >
            <MapPin class="w-4 h-4 text-accent" />
            <span>{{ t('navAttendance') }}</span>
          </Link>
        </div>
      </template>
    </PageBanner>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <!-- Total Tasks -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs hover:border-sky-500/50 transition-all">
        <div class="flex items-center justify-between gap-2 mb-3">
          <span class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ t('totalTasks') }}</span>
          <div class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center">
            <CheckSquare class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl font-black text-slate-900 dark:text-white font-mono">{{ stats.total_tasks }}</div>
        <div class="text-[11px] text-slate-400 mt-1">{{ t('today') }}</div>
      </div>

      <!-- Completed Tasks -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs hover:border-emerald-500/50 transition-all">
        <div class="flex items-center justify-between gap-2 mb-3">
          <span class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ t('completedTasks') }}</span>
          <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
            <CheckCircle2 class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">{{ stats.completed_tasks }}</div>
        <div class="text-[11px] text-slate-400 mt-1">{{ t('statusCompleted') }} (100%)</div>
      </div>

      <!-- In Progress / Pending -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs hover:border-amber-500/50 transition-all">
        <div class="flex items-center justify-between gap-2 mb-3">
          <span class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ t('inProgressTasks') }}</span>
          <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
            <Clock class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl font-black text-amber-600 dark:text-amber-400 font-mono">{{ stats.in_progress_tasks }}</div>
        <div class="text-[11px] text-slate-400 mt-1">+ {{ stats.pending_tasks }} {{ t('statusPending') }}</div>
      </div>

      <!-- Field Attendance / Rate -->
      <div class="bg-gradient-to-br from-sky-600 to-teal-700 text-white rounded-3xl p-4 sm:p-5 shadow-lg shadow-sky-600/20">
        <div class="flex items-center justify-between gap-2 mb-3">
          <span class="text-xs font-bold text-sky-100">{{ t('avgRate') }}</span>
          <div class="w-8 h-8 rounded-xl bg-white/15 backdrop-blur-md text-white flex items-center justify-center">
            <TrendingUp class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl font-black font-mono">{{ stats.avg_progress }}%</div>
        <div class="text-[11px] text-sky-100/90 mt-1">
          {{ stats.today_attendance_count }} {{ t('attendanceToday') }}
        </div>
      </div>
    </div>

    <!-- Two Columns: Department Breakdown & Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      <!-- Department Progress Overview -->
      <div class="lg:col-span-1 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <Building class="w-4 h-4 text-sky-600" />
            <span>{{ t('navDepartments') }}</span>
          </h2>
          <Link href="/departments" class="text-xs font-bold text-sky-600 dark:text-sky-400 hover:underline">
            {{ t('edit') }}
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
              <span class="text-slate-500 dark:text-slate-400 font-semibold font-mono">{{ dept.completed_tasks_count }} / {{ dept.today_tasks_count }}</span>
            </div>
            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
              <div 
                class="bg-sky-600 h-full rounded-full transition-all"
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
            <CheckSquare class="w-4 h-4 text-sky-600" />
            <span>{{ t('recentActivity') }}</span>
          </h2>
          <Link href="/tasks" class="text-xs font-bold text-sky-600 dark:text-sky-400 hover:underline flex items-center gap-1">
            <span>{{ t('navTasks') }}</span>
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
              <span class="font-bold text-slate-700 dark:text-slate-300 font-mono">{{ task.progress }}%</span>
              
              <!-- Strict Unbreakable Status Badge -->
              <span 
                :class="{
                  'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border-emerald-200': task.status === 'completed',
                  'bg-teal-50 text-teal-700 dark:bg-teal-950/70 dark:text-teal-300 border-teal-200': task.status === 'in_progress',
                  'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200': task.status === 'pending',
                }"
                class="whitespace-nowrap inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[10px] font-bold border shrink-0"
              >
                {{ task.status === 'completed' ? t('statusCompleted') : (task.status === 'in_progress' ? t('statusInProgress') : t('statusPending')) }}
              </span>
            </div>
          </div>

          <div v-if="recentTasks.length === 0" class="py-8 text-center text-slate-400 text-xs">
            {{ t('noTasksFound') }}
          </div>
        </div>
      </div>
    </div>

    <!-- Recent GPS Attendance Feed -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
          <MapPin class="w-4 h-4 text-sky-600" />
          <span>{{ t('recentLocations') }}</span>
        </h2>
        <Link href="/attendance" class="text-xs font-bold text-sky-600 dark:text-sky-400 hover:underline flex items-center gap-1">
          <span>{{ t('navAttendance') }}</span>
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
          <div class="mt-2 flex items-center justify-between text-[10px] text-slate-500 border-t border-slate-200/60 dark:border-slate-700/60 pt-2 font-mono">
            <span>⏰ {{ log.log_time }}</span>
            <a 
              :href="`https://www.google.com/maps?q=${log.latitude},${log.longitude}`" 
              target="_blank" 
              class="text-sky-600 dark:text-sky-400 font-semibold hover:underline"
            >
              {{ t('googleMaps') }} ↗
            </a>
          </div>
        </div>

        <div v-if="recentAttendanceLogs.length === 0" class="col-span-4 py-8 text-center text-slate-400 text-xs">
          {{ t('noLogsFound') }}
        </div>
      </div>
    </div>
  </AppLayout>
</template>
