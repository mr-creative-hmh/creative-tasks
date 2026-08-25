<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { t } from '@/i18n';
import { CheckCircle, Clock, AlertCircle, User, Sparkles } from 'lucide-vue-next';

const props = defineProps({
  task: {
    type: Object,
    required: true
  },
  readonly: {
    type: Boolean,
    default: false
  }
});

const progress = ref(props.task.progress || 0);
const isUpdating = ref(false);

const quickValues = [0, 25, 50, 75, 100];

function setProgress(val) {
  if (props.readonly) return;
  progress.value = val;
  saveProgress();
}

function onSliderChange() {
  if (props.readonly) return;
  saveProgress();
}

function saveProgress() {
  isUpdating.value = true;
  router.patch(`/employee/tasks/${props.task.id}/progress`, {
    progress: progress.value
  }, {
    preserveScroll: true,
    onFinish: () => {
      isUpdating.value = false;
    }
  });
}

const statusColor = computed(() => {
  if (progress.value === 100) return 'bg-emerald-500 text-white';
  if (progress.value > 0) return 'bg-teal-600 text-white';
  return 'bg-slate-400 text-white';
});

const statusLabel = computed(() => {
  if (progress.value === 100) return t('completedTasks');
  if (progress.value > 0) return t('inProgressTasks');
  return t('pendingTasks');
});
</script>

<template>
  <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 sm:p-5 shadow-sm hover:shadow-md transition-all">
    <div class="flex items-start justify-between gap-3 mb-3">
      <div class="flex-1">
        <div class="flex items-center gap-2 mb-1.5">
          <span 
            v-if="task.task_type === 'assigned'" 
            class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-900"
          >
            <User class="w-3 h-3" />
            {{ t('assignedType') }}
          </span>
          <span 
            v-else 
            class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-900"
          >
            <Sparkles class="w-3 h-3" />
            {{ t('selfType') }}
          </span>

          <span 
            :class="[statusColor]" 
            class="text-[11px] font-bold px-2 py-0.5 rounded-full transition-colors"
          >
            {{ statusLabel }}
          </span>
        </div>

        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 leading-snug">
          {{ task.title }}
        </h3>
        <p v-if="task.description" class="text-xs text-slate-600 dark:text-slate-400 mt-1 line-clamp-2">
          {{ task.description }}
        </p>
      </div>

      <!-- Live percentage circle -->
      <div class="shrink-0 flex flex-col items-center justify-center w-14 h-14 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-100 dark:border-slate-700/60">
        <span class="text-base font-black text-brand-600 dark:text-brand-400">{{ progress }}%</span>
        <span class="text-[9px] font-semibold text-slate-400">إنجاز</span>
      </div>
    </div>

    <!-- Progress Bar & Slider Controls -->
    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
      <div class="flex items-center justify-between text-xs mb-1.5 text-slate-500 dark:text-slate-400">
        <span class="font-medium">{{ t('quickSlidePrompt') }}</span>
        <span v-if="isUpdating" class="text-brand-600 font-medium animate-pulse text-[11px]">جاري الحفظ...</span>
      </div>

      <!-- Quick chips -->
      <div v-if="!readonly" class="grid grid-cols-5 gap-1.5 mb-3">
        <button 
          v-for="val in quickValues" 
          :key="val"
          @click="setProgress(val)"
          type="button"
          :class="progress === val ? 'bg-brand-600 text-white font-bold shadow-sm' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-medium'"
          class="py-1 rounded-lg text-xs transition-all active:scale-95 text-center"
        >
          {{ val }}%
        </button>
      </div>

      <!-- Range Slider -->
      <input 
        v-if="!readonly"
        v-model.number="progress" 
        @change="onSliderChange"
        type="range" 
        min="0" 
        max="100" 
        step="5"
        class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-brand-600"
      />
      <div v-else class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
        <div class="bg-brand-600 h-full transition-all duration-300" :style="{ width: `${progress}%` }"></div>
      </div>
    </div>

    <!-- Assignee / Date Footer -->
    <div class="mt-3 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
      <span v-if="task.assigner" class="flex items-center gap-1">
        <User class="w-3 h-3 text-slate-400" />
        {{ t('assignedBy') }}: <strong class="text-slate-700 dark:text-slate-300">{{ task.assigner.name }}</strong>
      </span>
      <span v-else class="text-slate-400">عمل يومي مسجل ذاتياً</span>
      <span>{{ task.task_date }}</span>
    </div>
  </div>
</template>
