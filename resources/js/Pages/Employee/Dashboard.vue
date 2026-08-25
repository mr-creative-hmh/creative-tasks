<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { t } from '@/i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import LocationGateModal from '@/Components/LocationGateModal.vue';
import TaskCard from '@/Components/TaskCard.vue';
import {
  CheckSquare,
  Sparkles,
  Plus,
  Send,
  Calendar,
  BarChart,
  CheckCircle2,
  ListTodo
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
    default: () => ({ total: 0, completed: 0, avg_progress: 0 })
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
    <div class="w-full max-w-5xl mx-auto space-y-5">
      <!-- GPS & Shift Gate Banner -->
      <LocationGateModal :required="true" />

      <!-- Daily Performance KPI Card -->
      <div class="bg-accent-gradient rounded-3xl p-5 sm:p-6 text-white shadow-xl shadow-sky-900/20 relative overflow-hidden">
        <div class="absolute -end-8 -top-8 w-32 h-32 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
        
        <div class="flex items-center justify-between gap-3 relative z-10">
          <div>
            <div class="text-[11px] font-semibold text-sky-200 flex items-center gap-1.5 mb-1">
              <Calendar class="w-3.5 h-3.5" />
              <span>{{ t('today') }}: {{ summary.today_date }}</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight">{{ t('todayCompletion') }}</h2>
            <p class="text-xs text-sky-100/80 mt-0.5">
              {{ t('tasksCompletionSummary', { completed: summary.completed, total: summary.total }) }}
            </p>
          </div>

          <!-- Progress Percentage Circle -->
          <div class="shrink-0 flex flex-col items-center justify-center w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/15 backdrop-blur-md border border-white/20">
            <span class="text-lg sm:text-2xl font-black text-white font-mono">{{ summary.avg_progress }}%</span>
            <span class="text-[9px] sm:text-[10px] font-bold text-sky-100">{{ t('avgProgress') }}</span>
          </div>
        </div>

        <!-- Linear progress bar -->
        <div class="mt-4 w-full bg-black/20 rounded-full h-2.5 overflow-hidden">
          <div 
            class="bg-gradient-to-r from-teal-300 to-emerald-400 h-full rounded-full transition-all duration-500" 
            :style="{ width: `${summary.avg_progress}%` }"
          ></div>
        </div>
      </div>

      <!-- Segmented Tab Switcher (Assigned vs Self-Reported) -->
      <div class="bg-slate-200/80 dark:bg-slate-900/80 p-1 rounded-2xl flex items-center gap-1 border border-slate-300/60 dark:border-slate-800">
        <button
          @click="activeTab = 'assigned'"
          type="button"
          :class="activeTab === 'assigned' ? 'bg-white dark:bg-slate-800 text-sky-700 dark:text-sky-300 font-bold shadow-sm' : 'text-slate-600 dark:text-slate-400 font-medium'"
          class="flex-1 py-2.5 px-3 rounded-xl text-xs flex items-center justify-center gap-2 transition-all active:scale-98 cursor-pointer"
        >
          <CheckSquare class="w-4 h-4 text-sky-600" />
          <span>{{ t('tabAssigned') }} ({{ assignedTasks.length }})</span>
        </button>

        <button
          @click="activeTab = 'self_reported'"
          type="button"
          :class="activeTab === 'self_reported' ? 'bg-white dark:bg-slate-800 text-sky-700 dark:text-sky-300 font-bold shadow-sm' : 'text-slate-600 dark:text-slate-400 font-medium'"
          class="flex-1 py-2.5 px-3 rounded-xl text-xs flex items-center justify-center gap-2 transition-all active:scale-98 cursor-pointer"
        >
          <Sparkles class="w-4 h-4 text-amber-500" />
          <span>{{ t('tabSelfReported') }} ({{ selfReportedTasks.length }})</span>
        </button>
      </div>

      <!-- TAB 1: Assigned Tasks List -->
      <div v-if="activeTab === 'assigned'" class="space-y-4">
        <div v-if="assignedTasks.length === 0" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-8 text-center">
          <div class="w-12 h-12 rounded-2xl bg-sky-50 dark:bg-sky-950 text-sky-600 mx-auto flex items-center justify-center mb-3">
            <ListTodo class="w-6 h-6" />
          </div>
          <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ t('noAssignedTasks') }}</h3>
        </div>

        <TaskCard 
          v-for="task in assignedTasks" 
          :key="task.id" 
          :task="task" 
        />
      </div>

      <!-- TAB 2: Self-Reported Daily Activity & Fast Entry -->
      <div v-if="activeTab === 'self_reported'" class="space-y-4">
        <!-- One-line fast input card -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-4 sm:p-5 shadow-sm">
          <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
              <Sparkles class="w-4 h-4" />
            </div>
            <div>
              <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100">{{ t('quickSelfTitle') }}</h3>
              <p class="text-[10px] text-slate-500">{{ t('quickSelfSubtitle') }}</p>
            </div>
          </div>

          <form @submit.prevent="addSelfTask" class="space-y-3">
            <div class="flex items-center gap-2">
              <input
                v-model="selfReportForm.title"
                type="text"
                required
                :placeholder="t('quickAddPlaceholder')"
                class="flex-1 h-10 px-4 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white placeholder-slate-400 outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all font-medium"
              />
              <button
                :disabled="selfReportForm.processing || !selfReportForm.title.trim()"
                type="submit"
                class="h-10 px-4 rounded-xl bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-bold text-xs flex items-center gap-1.5 transition-all shadow-md shadow-sky-600/25 disabled:opacity-50 shrink-0 cursor-pointer"
              >
                <Plus class="w-4 h-4" />
                <span>{{ t('add') }}</span>
              </button>
            </div>

            <div v-if="selfReportForm.errors.title" class="text-xs text-rose-600 ps-1">
              {{ selfReportForm.errors.title }}
            </div>
          </form>
        </div>

        <!-- Self-Reported Tasks List -->
        <div v-if="selfReportedTasks.length === 0" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-8 text-center">
          <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 mx-auto flex items-center justify-center mb-3">
            <Sparkles class="w-6 h-6" />
          </div>
          <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ t('noSelfTasks') }}</h3>
        </div>

        <TaskCard 
          v-for="task in selfReportedTasks" 
          :key="task.id" 
          :task="task" 
        />
      </div>
    </div>
  </AppLayout>
</template>
