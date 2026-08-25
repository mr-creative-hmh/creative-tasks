<script setup>
import { ref, computed, watch } from 'vue';
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

const progress = ref(Number(props.task.progress) || 0);
const isUpdating = ref(false);

const quickValues = [0, 25, 50, 75, 100];

watch(() => props.task.progress, (newVal) => {
  progress.value = Number(newVal) || 0;
});

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
  if (progress.value === 100) return t('statusCompleted');
  if (progress.value > 0) return t('statusInProgress');
  return t('statusPending');
});
</script>

<template>
  <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 sm:p-5 shadow-sm hover:shadow-md transition-all">
    <div class="flex items-start justify-between gap-3 mb-3">
      <div class="flex-1 min-w-0">
        <div class="flex flex-wrap items-center gap-1.5 mb-1.5">
          <span 
            v-if="task.task_type === 'assigned'" 
            class="whitespace-nowrap inline-flex items-center justify-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-lg bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200 dark:border-sky-800/60 shrink-0"
          >
            <User class="w-3 h-3" />
            {{ t('typeAssigned') }}
          </span>
          <span 
            v-else 
            class="whitespace-nowrap inline-flex items-center justify-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-lg bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 shrink-0"
          >
            <Sparkles class="w-3 h-3" />
            {{ t('typeSelf') }}
          </span>

          <!-- Strict Unbreakable Status Badge -->
          <span 
            :class="[statusColor]" 
            class="whitespace-nowrap inline-flex items-center justify-center text-[10px] font-extrabold px-2 py-0.5 rounded-full transition-colors shrink-0 shadow-xs"
          >
            {{ statusLabel }}
          </span>
        </div>

        <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-slate-100 leading-snug">
          {{ task.title }}
        </h3>
        <p v-if="task.description" class="text-xs text-slate-600 dark:text-slate-400 mt-1 line-clamp-2 leading-relaxed">
          {{ task.description }}
        </p>
      </div>

      <!-- Live percentage circle -->
      <div class="shrink-0 flex flex-col items-center justify-center w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/60 shadow-xs">
        <span class="text-sm sm:text-base font-black text-sky-600 dark:text-sky-400 font-mono">{{ progress }}%</span>
        <span class="text-[9px] font-bold text-slate-400">{{ t('progress') }}</span>
      </div>
    </div>

    <!-- Progress Bar & Slider Controls -->
    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
      <div class="flex items-center justify-between text-xs mb-1.5 text-slate-500 dark:text-slate-400">
        <span class="font-bold text-[11px]">{{ t('quickSlidePrompt') }}</span>
        <span v-if="isUpdating" class="text-sky-600 dark:text-sky-400 font-bold animate-pulse text-[11px]">{{ t('savingProgress') }}</span>
      </div>

      <!-- Quick chips -->
      <div v-if="!readonly" class="grid grid-cols-5 gap-1.5 mb-3">
        <button 
          v-for="val in quickValues" 
          :key="val"
          @click="setProgress(val)"
          type="button"
          :class="progress === val ? 'bg-sky-600 text-white font-black shadow-sm' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold'"
          class="py-1 rounded-lg text-xs transition-all active:scale-95 text-center font-mono cursor-pointer"
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
        class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-sky-600"
      />
      <div v-else class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
        <div class="bg-sky-600 h-full transition-all duration-300" :style="{ width: `${progress}%` }"></div>
      </div>
    </div>

    <!-- Assignee / Date Footer -->
    <div class="mt-3 flex flex-wrap items-center justify-between gap-1 text-[11px] text-slate-500 dark:text-slate-400">
      <span v-if="task.assigner" class="flex items-center gap-1">
        <User class="w-3 h-3 text-slate-400" />
        <span>{{ t('assignedBy') }}: <strong class="text-slate-700 dark:text-slate-300">{{ task.assigner.name }}</strong></span>
      </span>
      <span v-else class="text-slate-400">{{ t('typeSelf') }}</span>
      
      <span class="font-mono text-slate-500 dark:text-slate-400">{{ task.task_date }}</span>
    </div>
  </div>
</template>
