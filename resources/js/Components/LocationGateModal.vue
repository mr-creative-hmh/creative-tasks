<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { t } from '@/i18n';
import { gpsState } from '@/Services/gpsTracker';
import {
  MapPin,
  AlertTriangle,
  CheckCircle2,
  Clock,
  Navigation,
  RefreshCw,
  Lock,
  Sparkles,
  Radio
} from 'lucide-vue-next';

const props = defineProps({
  required: {
    type: Boolean,
    default: true
  }
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user);
const dept = computed(() => page.props.activeDepartment || authUser.value?.department);
const todayAttendance = computed(() => page.props.todayAttendance);

const isOutsideShift = ref(false);

const shiftStartTime = computed(() => dept.value?.work_start_time?.substring(0,5) || '08:00');
const shiftEndTime = computed(() => dept.value?.work_end_time?.substring(0,5) || '15:30');

function checkShift() {
  if (!dept.value) return;
  const now = new Date();
  const currentMinutes = now.getHours() * 60 + now.getMinutes();

  const [startH, startM] = shiftStartTime.value.split(':').map(Number);
  const [endH, endM] = shiftEndTime.value.split(':').map(Number);
  const startMinutes = startH * 60 + (startM || 0);
  const endMinutes = endH * 60 + (endM || 0);

  if (startMinutes <= endMinutes) {
    isOutsideShift.value = currentMinutes < startMinutes || currentMinutes > endMinutes;
  } else {
    // Overnight shift
    isOutsideShift.value = currentMinutes < startMinutes && currentMinutes > endMinutes;
  }
}

onMounted(() => {
  checkShift();
});
</script>

<template>
  <div class="space-y-3">
    <!-- 1. Outside Shift Warning Banner (Non-blocking alert) -->
    <div
      v-if="isOutsideShift"
      class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-amber-800 dark:text-amber-300 text-xs flex items-center justify-between gap-3 shadow-xs"
    >
      <div class="flex items-center gap-2.5">
        <Clock class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" />
        <div>
          <strong class="font-bold">{{ t('outsideShiftWarning') }}</strong>
          <p class="text-[11px] text-amber-700/80 dark:text-amber-400/80 mt-0.5">
            {{ t('shiftHours') }}: <span class="font-mono font-bold">{{ shiftStartTime }} - {{ shiftEndTime }}</span>
          </p>
        </div>
      </div>
    </div>

    <!-- 2. Subtle GPS Background Sync Status Pill (If error occurred) -->
    <div
      v-if="gpsState.error"
      class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-rose-800 dark:text-rose-300 text-xs flex items-center justify-between gap-3 shadow-xs"
    >
      <div class="flex items-center gap-2.5">
        <AlertTriangle class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0" />
        <span>{{ gpsState.error }}</span>
      </div>
    </div>
  </div>
</template>
