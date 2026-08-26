<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { t } from '@/i18n';
import { gpsState, syncCurrentGpsLocation, sendCoordinates } from '@/Services/gpsTracker';
import LocationPickerMap from '@/Components/LocationPickerMap.vue';
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
  Building2,
  Check
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

// Baghdad Al-Ma'moon University Default Campus Coordinates
const campusDefaultLat = 33.31524;
const campusDefaultLng = 44.36612;

const selectedLat = ref(campusDefaultLat);
const selectedLng = ref(campusDefaultLng);
const isConfirming = ref(false);

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

function handleConfirmMapLocation() {
  isConfirming.value = true;
  sendCoordinates(selectedLat.value, selectedLng.value, 15);
  setTimeout(() => {
    isConfirming.value = false;
  }, 1000);
}

onMounted(() => {
  checkShift();
});
</script>

<template>
  <div>
    <!-- ========================================================= -->
    <!-- 1. STRICT MANDATORY GPS / CAMPUS LOCATION ENFORCEMENT     -->
    <!-- (Shown whenever GPS is disabled / denied / unverified)    -->
    <!-- ========================================================= -->
    <div
      v-if="isGpsLocked"
      class="fixed inset-0 z-50 bg-slate-950/95 backdrop-blur-xl flex items-center justify-center p-3 sm:p-4 select-none overflow-y-auto animate-fade-in"
    >
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-7 max-w-lg w-full shadow-2xl text-center relative overflow-hidden my-auto space-y-4">
        
        <!-- Ambient Glow -->
        <div class="absolute -top-16 -left-16 w-44 h-44 bg-rose-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-16 -right-16 w-44 h-44 bg-sky-500/15 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Radar Animation & Lock Icon -->
        <div class="relative w-16 h-16 mx-auto flex items-center justify-center">
          <div
            v-if="gpsState.isSyncing"
            class="absolute inset-0 rounded-full bg-sky-500/20 animate-ping"
          ></div>
          <div
            :class="gpsState.isSyncing ? 'bg-gradient-to-tr from-sky-600 to-teal-500 shadow-sky-500/30' : 'bg-gradient-to-tr from-sky-600 to-teal-600 shadow-sky-500/30'"
            class="relative w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-xl transition-all"
          >
            <Radio v-if="gpsState.isSyncing" class="w-7 h-7 animate-pulse text-white" />
            <MapPin v-else class="w-7 h-7 text-white" />
          </div>
        </div>

        <!-- Title -->
        <div>
          <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white leading-snug">
            {{ gpsState.isSyncing ? t('gpsGateScanning') : t('gpsGateTitle') }}
          </h2>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed font-medium">
            {{ gpsState.isSyncing ? t('gpsGateScanningDesc') : 'خدمة الـ GPS معطلة أو غير متوفرة. يمكنك تحديد وتأكيد موقعك بالحرم الجامعي على الخريطة لتسجيل الحضور وتفعيل النظام.' }}
          </p>
        </div>

        <!-- Interactive Map Picker (Defaults to Baghdad Al-Ma'moon University) -->
        <div class="space-y-2 text-start">
          <div class="flex items-center justify-between text-[11px] font-bold text-slate-700 dark:text-slate-300">
            <span class="flex items-center gap-1.5">
              <Building2 class="w-3.5 h-3.5 text-sky-600 dark:text-sky-400" />
              <span>موقع الحرم الجامعي الافتراضي (انقر أو اسحب لتحديد النقطة)</span>
            </span>
            <span class="font-mono text-[10px] text-slate-400">
              {{ Number(selectedLat).toFixed(4) }}, {{ Number(selectedLng).toFixed(4) }}
            </span>
          </div>

          <!-- Leaflet Interactive Map -->
          <LocationPickerMap
            v-model:lat="selectedLat"
            v-model:lng="selectedLng"
            height="210px"
            :zoom="16"
          />
        </div>

        <!-- Current Error Status Alert if any -->
        <div
          v-if="gpsState.error"
          class="p-2.5 bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/60 rounded-xl text-[11px] text-amber-800 dark:text-amber-300 flex items-center gap-2 text-start font-medium"
        >
          <AlertTriangle class="w-4 h-4 shrink-0 text-amber-600 dark:text-amber-400" />
          <span>{{ gpsState.error }} (تم تفعيل خريطة الحرم الجامعي كبديل معتمد).</span>
        </div>

        <!-- Actions -->
        <div class="space-y-2 pt-1">
          <!-- Confirm Location on Map -->
          <button
            @click="handleConfirmMapLocation"
            :disabled="isConfirming || gpsState.isSyncing"
            type="button"
            class="w-full py-3 px-4 rounded-2xl bg-accent bg-accent-hover active:scale-95 text-white font-bold text-xs shadow-accent transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
          >
            <CheckCircle2 class="w-4 h-4" />
            <span>تأكيد التواجد بالحرم الجامعي وفتح المهام</span>
          </button>

          <!-- Retry GPS Hardware -->
          <button
            @click="handleRetryGps"
            :disabled="gpsState.isSyncing"
            type="button"
            class="w-full py-2.5 px-4 rounded-2xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs transition-colors flex items-center justify-center gap-2 cursor-pointer"
          >
            <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': gpsState.isSyncing }" />
            <span>{{ gpsState.isSyncing ? t('gpsScanningBtn') : 'إعادة محاولة تشغيل الـ GPS' }}</span>
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