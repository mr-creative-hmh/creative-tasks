<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { t } from '@/i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageBanner from '@/Components/PageBanner.vue';

import TaskCard from '@/Components/TaskCard.vue';
import {
  CheckSquare,
  Sparkles,
  Plus,
  Send,
  Calendar,
  BarChart,
  CheckCircle2,
  ListTodo,
  TrendingUp,
  Clock
} from 'lucide-vue-next';

const props = defineProps({
  assignedTasks: {
    type: Array,
    default: () => []
  },
  selfReportedTasks: {
    type: Array,
    default: () => []
  },
  summary: {
    type: Object,
    default: () => ({ total: 0, completed: 0, avg_progress: 0, today_date: '' })
  },
  department: {
    type: Object,
    default: null
  }
});

const activeTab = ref('assigned'); // 'assigned' | 'self_reported'

// Quick Self-Reported Form
const selfReportForm = useForm({
  title: '',
  description: '',
  completion_rate: 100,
});

function addSelfTask() {
  if (!selfReportForm.title.trim()) return;

  selfReportForm.transform(data => ({
    title: data.title,
    description: data.description,
    progress: data.completion_rate,
  })).post('/employee/tasks/self-reported', {
    preserveScroll: true,
    onSuccess: () => {
      selfReportForm.reset('title', 'description');
    }
  });
}
</script>

<template>
  <Head :title="t('employeeTitle')" />

  <AppLayout>
    <div class="w-full space-y-6">
      

      <!-- Unified Page Banner -->
      <PageBanner
        :title="t('employeeTitle')"
        :subtitle="(department?.name ? department.name + ' • ' : '') + t('employeeTasksSubtitle')"
        :badge="t('today') + ': ' + summary.today_date"
        :icon="ListTodo"
      >
        <template #actions>
          <div class="flex items-center gap-2">
            <!-- Progress Counter Badge -->
            <div class="h-10 px-4 rounded-xl bg-accent-light text-accent border border-accent/20 flex items-center gap-2 text-xs font-bold shadow-xs">
              <TrendingUp class="w-4 h-4" />
              <span>{{ t('todayCompletion') }}: <strong class="font-mono text-sm">{{ summary.avg_progress }}%</strong></span>
            </div>
          </div>
        </template>
      </PageBanner>

      <!-- Daily Performance KPI Card -->
      <div class="bg-accent-gradient rounded-3xl p-5 sm:p-6 text-white shadow-xl shadow-accent/20 relative overflow-hidden">
        <div class="absolute -end-8 -top-8 w-32 h-32 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
        
        <div class="flex items-center justify-between gap-3 relative z-10">
          <div>
            <div class="text-[11px] font-semibold text-sky-100 flex items-center gap-1.5 mb-1">
              <Calendar class="w-3.5 h-3.5" />
              <span>{{ t('today') }}: {{ summary.today_date }}</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black">{{ t('myTasks') }}</h2>
            <p class="text-xs text-sky-100/90 mt-1">
              {{ t('tasksCompletionSummary', { completed: summary.completed, total: summary.total }) }}
            </p>
          </div>

          <div class="text-end">
            <div class="text-3xl sm:text-4xl font-black font-mono tracking-tight">{{ summary.avg_progress }}%</div>
            <div class="text-[10px] text-sky-200 uppercase font-bold">{{ t('todayCompletion') }}</div>
          </div>
        </div>

        <!-- Progress Track Bar -->
        <div class="w-full bg-black/20 rounded-full h-2.5 mt-4 p-0.5 overflow-hidden backdrop-blur-xs">
          <div
            class="bg-white h-full rounded-full transition-all duration-500 shadow-sm"
            :style="{ width: `${summary.avg_progress}%` }"
          ></div>
        </div>
      </div>

      <!-- Quick Add Self-Reported Task -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
        <div class="flex items-center gap-2 mb-3">
          <div class="w-7 h-7 rounded-xl bg-accent-light text-accent flex items-center justify-center font-bold">
            <Sparkles class="w-4 h-4" />
          </div>
          <div>
            <h3 class="text-xs font-bold text-slate-900 dark:text-white">{{ t('quickSelfTitle') }}</h3>
            <p class="text-[11px] text-slate-400">{{ t('quickSelfSubtitle') }}</p>
          </div>
        </div>

        <form @submit.prevent="addSelfTask" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
          <input
            v-model="selfReportForm.title"
            type="text"
            required
            :placeholder="t('quickAddPlaceholder')"
            class="flex-1 h-10 px-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white placeholder-slate-400 outline-none focus:border-sky-500 font-medium"
          />
          <button
            :disabled="selfReportForm.processing || !selfReportForm.title.trim()"
            type="submit"
            class="h-10 px-5 rounded-xl bg-accent bg-accent-hover text-white text-xs font-bold shadow-accent active:scale-95 disabled:opacity-50 transition-all flex items-center justify-center gap-2 cursor-pointer shrink-0"
          >
            <Plus class="w-4 h-4" />
            <span>{{ selfReportForm.processing ? t('saving') : t('addQuickTask') }}</span>
          </button>
        </form>
      </div>

      <!-- Tasks Section with Tabs -->
      <div class="space-y-4">
        <!-- Tabs Header -->
        <div class="flex items-center gap-2 p-1.5 bg-slate-100 dark:bg-slate-800/80 rounded-2xl w-fit">
          <button
            @click="activeTab = 'assigned'"
            :class="activeTab === 'assigned' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-500 dark:text-slate-400 font-semibold hover:text-slate-700'"
            class="px-4 py-2 rounded-xl text-xs transition-all flex items-center gap-2 cursor-pointer"
          >
            <span>{{ t('tabAssigned') }}</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-300 font-mono">
              {{ assignedTasks.length }}
            </span>
          </button>

          <button
            @click="activeTab = 'self_reported'"
            :class="activeTab === 'self_reported' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-500 dark:text-slate-400 font-semibold hover:text-slate-700'"
            class="px-4 py-2 rounded-xl text-xs transition-all flex items-center gap-2 cursor-pointer"
          >
            <span>{{ t('tabSelfReported') }}</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-teal-100 dark:bg-teal-950 text-teal-700 dark:text-teal-300 font-mono">
              {{ selfReportedTasks.length }}
            </span>
          </button>
        </div>

        <!-- Assigned Tasks Tab Content -->
        <div v-if="activeTab === 'assigned'" class="space-y-3">
          <div v-if="assignedTasks.length === 0" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-8 text-center">
            <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-3">
              <CheckSquare class="w-6 h-6" />
            </div>
            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ t('noTasksAssigned') }}</h4>
            <p class="text-xs text-slate-400 mt-1">{{ t('noTasksAssignedDesc') }}</p>
          </div>

          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <TaskCard
              v-for="task in assignedTasks"
              :key="task.id"
              :task="task"
            />
          </div>
        </div>

        <!-- Self-Reported Tasks Tab Content -->
        <div v-if="activeTab === 'self_reported'" class="space-y-3">
          <div v-if="selfReportedTasks.length === 0" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-8 text-center">
            <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-3">
              <Sparkles class="w-6 h-6" />
            </div>
            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ t('noSelfTasks') }}</h4>
            <p class="text-xs text-slate-400 mt-1">{{ t('quickSelfSubtitle') }}</p>
          </div>

          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <TaskCard
              v-for="task in selfReportedTasks"
              :key="task.id"
              :task="task"
            />
          </div>
        </div>

      </div>

    </div>
  </AppLayout>
</template>
