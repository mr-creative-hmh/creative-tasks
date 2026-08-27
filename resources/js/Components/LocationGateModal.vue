<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { gpsState, syncCurrentGpsLocation } from '@/Services/gpsTracker';
import { t } from '@/i18n';
import {
  MapPin,
  Radio,
  RefreshCw,
  AlertTriangle,
  Clock,
  ShieldAlert,
  Smartphone
} from 'lucide-vue-next';

const page = usePage();
const authUser = computed(() => page.props.auth?.user);
const dept = computed(() => page.props.auth?.department);
const todayAttendance = computed(() => page.props.auth?.todayAttendance);

const isAdmin = computed(() => authUser.value?.role === 'admin');
const isFixedMode = computed(() => authUser.value?.attendance_mode === 'fixed');

// GPS is strictly locked/required if:
// 1. User is non-admin AND not assigned to an Admin-approved Fixed Location Mode
// 2. Either GPS is errored/disabled OR (not verified yet AND no attendance logged today)
const isGpsLocked = computed(() => {
  if (isAdmin.value || isFixedMode.value) return false;
  
  // If GPS permission denied or hardware unavailable
  if (gpsState.error) return true;

  // If unverified and no attendance recorded for today
  if (!todayAttendance.value && !gpsState.isVerified) {
    return true;
  }

  return false;
});

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
    isOutsideShift.value = currentMinutes < startMinutes && currentMinutes > endMinutes;
  }
}

function handleRetryGps() {
  syncCurrentGpsLocation();
}

onMounted(() => {
  checkShift();
});
</script>

<template>
  <div>
    <!-- ========================================================= -->
    <!-- 1. STRICT REAL GPS ENFORCEMENT MODAL (NO MANUAL BYPASS)    -->
    <!-- ========================================================= -->
    <div
      v-if="isGpsLocked"
      class="fixed inset-0 z-50 bg-slate-950/90 backdrop-blur-xl flex items-center justify-center p-4 select-none overflow-y-auto animate-fade-in"
    >
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl text-center relative overflow-hidden my-auto space-y-5">
        
        <!-- Ambient Glow -->
        <div class="absolute -top-16 -left-16 w-44 h-44 bg-sky-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-16 -right-16 w-44 h-44 bg-teal-500/15 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Radar Animation & Lock Icon -->
        <div class="relative w-18 h-18 mx-auto flex items-center justify-center">
          <div
            v-if="gpsState.isSyncing"
            class="absolute inset-0 rounded-full bg-sky-500/25 animate-ping"
          ></div>
          <div
            :class="gpsState.isSyncing ? 'bg-gradient-to-tr from-sky-600 to-teal-500 shadow-sky-500/30' : 'bg-gradient-to-tr from-sky-600 to-teal-600 shadow-sky-500/30'"
            class="relative w-16 h-16 rounded-3xl flex items-center justify-center text-white shadow-xl transition-all"
          >
            <Radio v-if="gpsState.isSyncing" class="w-8 h-8 animate-pulse text-white" />
            <MapPin v-else class="w-8 h-8 text-white" />
          </div>
        </div>

        <!-- Title & Instruction -->
        <div>
          <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white leading-snug">
            {{ gpsState.isSyncing ? t('gpsGateScanning') : t('gpsGateTitle') }}
          </h2>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed font-medium">
            {{ gpsState.isSyncing ? t('gpsGateScanningDesc') : 'يتطلب النظام التحقق المباشر من إحداثيات موقعك عبر نظام التموضع العالمي (GPS) لتسجيل الحضور وتفعيل الوصول للمهام.' }}
          </p>
        </div>

        <!-- Hardware GPS Advice Box -->
        <div class="p-3.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/70 rounded-2xl text-[11px] text-slate-600 dark:text-slate-300 space-y-2 text-start">
          <div class="flex items-center gap-2 font-bold text-slate-900 dark:text-white">
            <Smartphone class="w-4 h-4 text-sky-500" />
            <span>خطوات تفعيل الموقع:</span>
          </div>
          <ul class="list-disc list-inside space-y-1 text-[11px] text-slate-500 dark:text-slate-400">
            <li>تأكد من تشغيل خدمة الموقع (Location / GPS) في هاتفك أو جهازك.</li>
            <li>امنح المتصفح صلاحية الوصول إلى الموقع (Allow Location).</li>
            <li>اضغط على زر الفحص بالأسفل للتحقق الفوري.</li>
          </ul>
        </div>

        <!-- Current Error Status Alert if any -->
        <div
          v-if="gpsState.error"
          class="p-3 bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/60 rounded-2xl text-xs text-rose-800 dark:text-rose-300 flex items-center gap-2.5 text-start font-medium animate-fade-in"
        >
          <AlertTriangle class="w-4 h-4 shrink-0 text-rose-600 dark:text-rose-400" />
          <span>{{ gpsState.error }}</span>
        </div>

        <!-- Action: Live GPS Verification ONLY -->
        <div class="pt-1">
          <button
            @click="handleRetryGps"
            :disabled="gpsState.isSyncing"
            type="button"
            class="w-full py-3.5 px-4 rounded-2xl bg-accent bg-accent-hover active:scale-95 text-white font-bold text-xs shadow-accent transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
          >
            <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': gpsState.isSyncing }" />
            <span>{{ gpsState.isSyncing ? t('gpsScanningBtn') : 'التحقق من موقع GPS وتحديث الحضور' }}</span>
          </button>
        </div>

      </div>
    </div>

    <!-- ========================================================= -->
    <!-- 2. OUTSIDE SHIFT WARNING (Subtle Banner)                  -->
    <!-- ========================================================= -->
    <div
      v-if="isOutsideShift && !isGpsLocked"
      class="mb-4 p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-amber-800 dark:text-amber-300 text-xs flex items-center justify-between gap-3 shadow-xs"
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
  </div>
</template>
