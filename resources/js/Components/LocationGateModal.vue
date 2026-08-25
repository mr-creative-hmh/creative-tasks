<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { t } from '@/i18n';
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
import axios from 'axios';

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

const isScanning = ref(false);
const isLocationLocked = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const coords = ref(null);
const isOutsideShift = ref(false);
let autoSyncTimer = null;
let geoWatcherId = null;

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

async function sendAutoAttendanceLog(lat, lng, isSilent = false) {
  if (!isSilent) isScanning.value = true;
  errorMessage.value = '';

  try {
    const response = await axios.post('/attendance/log', {
      latitude: lat,
      longitude: lng,
    });

    if (!isSilent) {
      successMessage.value = t('locationAccessGranted');
      isLocationLocked.value = false;
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    }
  } catch (err) {
    if (!isSilent) {
      errorMessage.value = err.response?.data?.message || t('locationDenied');
      isLocationLocked.value = true;
    }
  } finally {
    if (!isSilent) isScanning.value = false;
  }
}

function startAutomaticLocationDetection() {
  if (!navigator.geolocation) {
    errorMessage.value = t('locationDenied');
    isLocationLocked.value = true;
    return;
  }

  isScanning.value = true;
  errorMessage.value = '';

  navigator.geolocation.getCurrentPosition(
    (position) => {
      coords.value = {
        latitude: position.coords.latitude,
        longitude: position.coords.longitude,
      };
      sendAutoAttendanceLog(position.coords.latitude, position.coords.longitude, false);
    },
    (error) => {
      isScanning.value = false;
      isLocationLocked.value = true;
      switch (error.code) {
        case error.PERMISSION_DENIED:
          errorMessage.value = t('locationDenied');
          break;
        case error.POSITION_UNAVAILABLE:
        case error.TIMEOUT:
        default:
          errorMessage.value = t('locationRequiredMsg');
      }
    },
    { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
  );
}

function useCampusFallback() {
  // Baghdad Al-Ma'moon University Coordinates
  const campusLat = 33.31524;
  const campusLng = 44.36612;
  sendAutoAttendanceLog(campusLat, campusLng, false);
}

function initBackgroundTracker() {
  // Silent auto-update every 3 minutes while using the app
  autoSyncTimer = setInterval(() => {
    if (navigator.geolocation && todayAttendance.value) {
      navigator.geolocation.getCurrentPosition(
        (pos) => {
          sendAutoAttendanceLog(pos.coords.latitude, pos.coords.longitude, true);
        },
        () => {},
        { enableHighAccuracy: false, timeout: 8000 }
      );
    }
  }, 180000);
}

onMounted(() => {
  checkShift();

  // If attendance not logged today, automatically trigger GPS scan on mount
  if (!todayAttendance.value) {
    startAutomaticLocationDetection();
  } else {
    initBackgroundTracker();
  }
});

onUnmounted(() => {
  if (autoSyncTimer) clearInterval(autoSyncTimer);
  if (geoWatcherId && navigator.geolocation) navigator.geolocation.clearWatch(geoWatcherId);
});
</script>

<template>
  <div class="mb-5">
    <!-- 1. STRICT MANDATORY GPS ENFORCEMENT LOCK OVERLAY (If not checked in and location failed/scanning) -->
    <div 
      v-if="!todayAttendance && (isScanning || isLocationLocked)" 
      class="fixed inset-0 z-50 bg-slate-950/90 backdrop-blur-lg flex items-center justify-center p-4"
    >
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl text-center relative overflow-hidden">
        <!-- Ambient Radar Glow -->
        <div class="absolute -top-12 -left-12 w-40 h-40 bg-sky-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Animated Radar Scanning Icon -->
        <div class="relative w-20 h-20 mx-auto mb-5 flex items-center justify-center">
          <div class="absolute inset-0 rounded-full bg-sky-500/20 animate-ping"></div>
          <div class="relative w-16 h-16 rounded-2xl bg-gradient-to-tr from-sky-600 to-teal-500 flex items-center justify-center text-white shadow-xl shadow-sky-500/30">
            <Radio v-if="isScanning" class="w-8 h-8 animate-pulse text-white" />
            <Lock v-else class="w-8 h-8 text-amber-300" />
          </div>
        </div>

        <h2 class="text-lg font-black text-white mb-1">
          {{ isScanning ? t('gpsScanningTitle') : t('gpsEnforcedTitle') }}
        </h2>
        <p class="text-xs text-slate-400 mb-5 leading-relaxed">
          {{ isScanning ? t('gpsScanningDesc') : t('gpsLockDesc') }}
        </p>

        <!-- Error Alert -->
        <div v-if="errorMessage" class="mb-5 p-3.5 bg-rose-950/50 border border-rose-900/60 rounded-2xl text-xs text-rose-300 flex items-start gap-2 text-start">
          <AlertTriangle class="w-4 h-4 shrink-0 text-rose-400 mt-0.5" />
          <span>{{ errorMessage }}</span>
        </div>

        <!-- Success Alert -->
        <div v-if="successMessage" class="mb-5 p-3.5 bg-emerald-950/50 border border-emerald-900/60 rounded-2xl text-xs text-emerald-300 flex items-center gap-2">
          <CheckCircle2 class="w-4 h-4 shrink-0 text-emerald-400" />
          <span>{{ successMessage }}</span>
        </div>

        <!-- Actions -->
        <div class="space-y-2.5">
          <button
            @click="startAutomaticLocationDetection"
            :disabled="isScanning"
            type="button"
            class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-sky-600 to-teal-500 hover:from-sky-500 hover:to-teal-400 active:scale-95 text-white font-bold text-xs shadow-lg shadow-sky-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
          >
            <Navigation class="w-4 h-4" :class="{'animate-spin': isScanning}" />
            <span>{{ isScanning ? t('connectingGps') : t('retryGps') }}</span>
          </button>

          <!-- Fallback simulation for localhost/demo -->
          <button
            @click="useCampusFallback"
            :disabled="isScanning"
            type="button"
            class="w-full py-2.5 px-4 rounded-xl bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 text-slate-300 font-semibold text-xs transition-colors flex items-center justify-center gap-2"
          >
            <Sparkles class="w-3.5 h-3.5 text-amber-400" />
            <span>{{ t('demoSimCampusBtn') }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- 2. VERIFIED STATUS BAR (When attendance is recorded) -->
    <div v-if="todayAttendance" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 shadow-xs">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold border border-emerald-200 dark:border-emerald-800/60 shrink-0">
            <CheckCircle2 class="w-5 h-5" />
          </div>
          <div>
            <div class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase">{{ t('gpsVerifiedBadge') }}</div>
            <div class="text-xs font-extrabold text-slate-900 dark:text-white flex items-center gap-1.5 mt-0.5">
              <span class="text-emerald-600 dark:text-emerald-400">
                {{ t('gpsVerifiedDesc') }} ({{ todayAttendance.log_time }})
              </span>
            </div>
          </div>
        </div>

        <!-- Shift Info & Background Sync Pill -->
        <div class="flex items-center gap-2 text-xs bg-slate-50 dark:bg-slate-800/70 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700">
          <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
          <span class="text-[11px] text-slate-600 dark:text-slate-300">{{ t('autoTrackingActive') }}</span>
          <span class="text-slate-400">•</span>
          <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ t('shiftHours') }}: {{ shiftStartTime }} - {{ shiftEndTime }}</span>
        </div>
      </div>

      <!-- Warning if outside shift hours -->
      <div v-if="isOutsideShift" class="mt-3 p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 rounded-2xl text-xs text-amber-800 dark:text-amber-300 flex items-center gap-2">
        <AlertTriangle class="w-4 h-4 shrink-0" />
        <span>{{ t('outsideShiftWarning') }} ({{ shiftStartTime }} - {{ shiftEndTime }}).</span>
      </div>
    </div>
  </div>
</template>
