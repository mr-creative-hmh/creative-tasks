<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { t } from '@/i18n';
import { gpsState, syncCurrentGpsLocation, sendCoordinates } from '@/Services/gpsTracker';
import {
  MapPin,
  AlertTriangle,
  CheckCircle2,
  Clock,
  Navigation,
  RefreshCw,
  Lock,
  Sparkles,
  Radio,
  Building2
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

const isAdmin = computed(() => authUser.value?.role === 'admin');
const isFixedMode = computed(() => authUser.value?.attendance_mode === 'fixed');

// GPS is considered locked/blocked if:
// 1. User is non-admin AND not in fixed workplace mode AND
// 2. Either there is a GPS error OR (no attendance recorded today AND GPS not verified)
const isGpsLocked = computed(() => {
  if (isAdmin.value || isFixedMode.value) return false;
  
  // If GPS error occurred (e.g. turned off, permission revoked)
  if (gpsState.error) return true;

  // If not verified yet and no attendance recorded for today
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

function handleSimulateCampus() {
  // Baghdad Al-Ma'moon University Coordinates for Testing & Localhost Demo
  const campusLat = 33.31524;
  const campusLng = 44.36612;
  sendCoordinates(campusLat, campusLng, 15);
}

onMounted(() => {
  checkShift();
});
</script>

<template>
  <div>
    <!-- ========================================================= -->
    <!-- 1. STRICT MANDATORY GPS ENFORCEMENT LOCK OVERLAY          -->
    <!-- (Shown whenever GPS is disabled / denied / unverified)    -->
    <!-- ========================================================= -->
    <div
      v-if="isGpsLocked"
      class="fixed inset-0 z-50 bg-slate-950/95 backdrop-blur-xl flex items-center justify-center p-4 select-none animate-fade-in"
    >
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl text-center relative overflow-hidden">
        
        <!-- Ambient Glow -->
        <div class="absolute -top-16 -left-16 w-44 h-44 bg-rose-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-16 -right-16 w-44 h-44 bg-sky-500/15 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Radar Animation & Lock Icon -->
        <div class="relative w-20 h-20 mx-auto mb-5 flex items-center justify-center">
          <div
            v-if="gpsState.isSyncing"
            class="absolute inset-0 rounded-full bg-sky-500/20 animate-ping"
          ></div>
          <div
            :class="gpsState.isSyncing ? 'bg-gradient-to-tr from-sky-600 to-teal-500 shadow-sky-500/30' : 'bg-gradient-to-tr from-rose-600 to-amber-600 shadow-rose-500/30'"
            class="relative w-16 h-16 rounded-2xl flex items-center justify-center text-white shadow-xl transition-all"
          >
            <Radio v-if="gpsState.isSyncing" class="w-8 h-8 animate-pulse text-white" />
            <Lock v-else class="w-8 h-8 text-white" />
          </div>
        </div>

        <!-- Title -->
        <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white mb-1.5 leading-snug">
          {{ gpsState.isSyncing ? t('gpsGateScanning') : t('gpsGateTitle') }}
        </h2>

        <!-- Description -->
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-5 leading-relaxed font-medium">
          {{ gpsState.isSyncing ? t('gpsGateScanningDesc') : t('gpsGateDesc') }}
        </p>

        <!-- Current Error Message Box -->
        <div
          v-if="gpsState.error"
          class="mb-5 p-3.5 bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-900/60 rounded-2xl text-xs text-rose-800 dark:text-rose-300 flex items-start gap-2.5 text-start font-medium"
        >
          <AlertTriangle class="w-4 h-4 shrink-0 text-rose-600 dark:text-rose-400 mt-0.5" />
          <span>{{ gpsState.error }}</span>
        </div>

        <!-- Actions -->
        <div class="space-y-2.5">
          <!-- Primary Retry Button -->
          <button
            @click="handleRetryGps"
            :disabled="gpsState.isSyncing"
            type="button"
            class="w-full py-3 px-4 rounded-2xl bg-accent bg-accent-hover active:scale-95 text-white font-bold text-xs shadow-accent transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
          >
            <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': gpsState.isSyncing }" />
            <span>{{ gpsState.isSyncing ? t('gpsScanningBtn') : t('gpsRetryBtn') }}</span>
          </button>

          <!-- Fallback simulation for Localhost & Demo testing -->
          <button
            @click="handleSimulateCampus"
            :disabled="gpsState.isSyncing"
            type="button"
            class="w-full py-2.5 px-4 rounded-2xl bg-slate-100 dark:bg-slate-800/80 hover:bg-slate-200 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700/80 text-slate-700 dark:text-slate-300 font-semibold text-xs transition-colors flex items-center justify-center gap-2 cursor-pointer"
          >
            <Sparkles class="w-3.5 h-3.5 text-amber-500" />
            <span>{{ t('gpsDemoSimulation') }}</span>
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
