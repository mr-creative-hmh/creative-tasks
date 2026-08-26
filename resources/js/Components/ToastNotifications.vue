<script setup>
import { ref, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircle2, AlertCircle, Info, AlertTriangle, X } from 'lucide-vue-next';

const page = usePage();
const toasts = ref([]);
let toastIdCounter = 0;

function addToast(message, type = 'success', duration = 3000) {
  if (!message) return;

  const id = ++toastIdCounter;
  const newToast = {
    id,
    message,
    type,
    duration,
    visible: true,
  };

  toasts.value.push(newToast);

  // Auto dismiss after exactly duration (default 3 seconds)
  setTimeout(() => {
    dismissToast(id);
  }, duration);
}

function dismissToast(id) {
  const index = toasts.value.findIndex(t => t.id === id);
  if (index !== -1) {
    toasts.value[index].visible = false;
    setTimeout(() => {
      toasts.value = toasts.value.filter(t => t.id !== id);
    }, 300);
  }
}

// Watch Inertia flash messages
watch(
  () => page.props.flash,
  (flash) => {
    if (!flash) return;
    if (flash.success) {
      addToast(flash.success, 'success', 3000);
      // Clear to prevent replay
      page.props.flash.success = null;
    }
    if (flash.error) {
      addToast(flash.error, 'error', 3500);
      page.props.flash.error = null;
    }
    if (flash.warning) {
      addToast(flash.warning, 'warning', 3000);
      page.props.flash.warning = null;
    }
    if (flash.message) {
      addToast(flash.message, 'info', 3000);
      page.props.flash.message = null;
    }
  },
  { deep: true, immediate: true }
);

// Global custom event listener
onMounted(() => {
  window.addEventListener('app-toast', (e) => {
    if (e.detail?.message) {
      addToast(e.detail.message, e.detail.type || 'success', e.detail.duration || 3000);
    }
  });
});
</script>

<template>
  <div class="fixed top-4 end-4 sm:top-6 sm:end-6 z-[9999] flex flex-col gap-2.5 max-w-sm w-[calc(100vw-2rem)] sm:w-96 pointer-events-none">
    <transition-group
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-[-10px] opacity-0 scale-95"
      enter-to-class="translate-y-0 opacity-100 scale-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95 translate-y-[-10px]"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="pointer-events-auto relative overflow-hidden rounded-2xl p-3.5 sm:p-4 shadow-xl backdrop-blur-md border flex items-center justify-between gap-3 text-xs font-bold transition-all"
        :class="{
          'bg-emerald-50/95 dark:bg-emerald-950/90 border-emerald-300 dark:border-emerald-700 text-emerald-900 dark:text-emerald-200 shadow-emerald-500/10': toast.type === 'success',
          'bg-rose-50/95 dark:bg-rose-950/90 border-rose-300 dark:border-rose-700 text-rose-900 dark:text-rose-200 shadow-rose-500/10': toast.type === 'error',
          'bg-amber-50/95 dark:bg-amber-950/90 border-amber-300 dark:border-amber-700 text-amber-900 dark:text-amber-200 shadow-amber-500/10': toast.type === 'warning',
          'bg-sky-50/95 dark:bg-sky-950/90 border-sky-300 dark:border-sky-700 text-sky-900 dark:text-sky-200 shadow-sky-500/10': toast.type === 'info'
        }"
      >
        <div class="flex items-center gap-2.5 min-w-0 flex-1">
          <!-- Icon -->
          <div
            class="w-7 h-7 rounded-xl flex items-center justify-center shrink-0 shadow-xs"
            :class="{
              'bg-emerald-500 text-white': toast.type === 'success',
              'bg-rose-500 text-white': toast.type === 'error',
              'bg-amber-500 text-white': toast.type === 'warning',
              'bg-sky-500 text-white': toast.type === 'info'
            }"
          >
            <CheckCircle2 v-if="toast.type === 'success'" class="w-4 h-4" />
            <AlertCircle v-else-if="toast.type === 'error'" class="w-4 h-4" />
            <AlertTriangle v-else-if="toast.type === 'warning'" class="w-4 h-4" />
            <Info v-else class="w-4 h-4" />
          </div>

          <!-- Message -->
          <p class="leading-snug truncate flex-1">{{ toast.message }}</p>
        </div>

        <!-- Dismiss Button -->
        <button
          @click="dismissToast(toast.id)"
          type="button"
          class="p-1 rounded-lg hover:bg-black/5 dark:hover:bg-white/10 text-current/70 hover:text-current transition-colors cursor-pointer shrink-0"
        >
          <X class="w-4 h-4" />
        </button>

        <!-- 3-Second Countdown Progress Bar -->
        <div
          class="absolute bottom-0 start-0 h-[3px] bg-current opacity-30 animate-toast-shrink"
          :style="{ animationDuration: (toast.duration || 3000) + 'ms' }"
        ></div>
      </div>
    </transition-group>
  </div>
</template>

<style scoped>
@keyframes toastShrink {
  from {
    width: 100%;
  }
  to {
    width: 0%;
  }
}

.animate-toast-shrink {
  animation: toastShrink linear forwards;
}
</style>
