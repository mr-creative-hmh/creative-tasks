<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { t } from '@/i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import AttendanceMap from '@/Components/AttendanceMap.vue';
import Pagination from '@/Components/Pagination.vue';
import PageBanner from '@/Components/PageBanner.vue';
import axios from 'axios';
import {
  MapPin,
  Calendar,
  Filter,
  UserCheck,
  Building2,
  ExternalLink,
  ChevronDown,
  ChevronUp,
  Save,
  Edit3,
  Clock,
  Sparkles,
  Users,
  CheckCircle2,
  AlertCircle,
  Radio,
  RefreshCw,
  Route,
  Navigation,
  X,
  Flag,
  Activity
} from 'lucide-vue-next';

const props = defineProps({
  logs: {
    type: Object,
    default: () => ({ data: [], links: [] })
  },
  departments: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({ total_present_today: 0, total_employees: 0 })
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  allEmployees: {
    type: Array,
    default: () => []
  },
  canManualEdit: {
    type: Boolean,
    default: false
  }
});

const filterForm = ref({
  date: props.filters.date || new Date().toISOString().substring(0, 10),
  department_id: props.filters.department_id || '',
});

// Admin/Head Manual Pin State
const showManualPanel = ref(false);
const manualForm = ref({
  user_id: '',
  latitude: null,
  longitude: null,
  date: props.filters.date || new Date().toISOString().substring(0, 10),
});
const isUpdatingManual = ref(false);
const manualSuccessMsg = ref('');
const manualErrorMsg = ref('');

// ==========================================
// 1. LIVE RADAR SYNC STATE
// ==========================================
const isRadarScanning = ref(false);
const livePoints = ref(null);
const liveActiveCount = ref(0);
const autoRefreshEnabled = ref(false);
let autoRefreshTimer = null;

// ==========================================
// 2. LOCATION TRAIL / BREADCRUMBS STATE
// ==========================================
const selectedTrailEmployee = ref(null);
const trailPoints = ref([]);
const isTrailLoading = ref(false);
const trailError = ref('');

// Haversine distance calculator between multiple points in km
const trailTotalDistanceKm = computed(() => {
  if (!trailPoints.value || trailPoints.value.length < 2) return '0.00 ' + t('meters');
  let totalMeters = 0;
  for (let i = 0; i < trailPoints.value.length - 1; i++) {
    const p1 = trailPoints.value[i];
    const p2 = trailPoints.value[i + 1];
    const lat1 = (p1.latitude * Math.PI) / 180;
    const lon1 = (p1.longitude * Math.PI) / 180;
    const lat2 = (p2.latitude * Math.PI) / 180;
    const lon2 = (p2.longitude * Math.PI) / 180;
    const dLat = lat2 - lat1;
    const dLon = lon2 - lon1;
    const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1) * Math.cos(lat2) * (Math.sin(dLon / 2) ** 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    totalMeters += 6371000 * c;
  }
  if (totalMeters < 1000) {
    return `${Math.round(totalMeters)} ${t('meters')}`;
  }
  return `${(totalMeters / 1000).toFixed(2)} ${t('kilometers')}`;
});

function applyFilters() {
  const clean = {};
  for (const [k, v] of Object.entries(filterForm.value)) {
    if (v !== '' && v !== null && v !== undefined) {
      clean[k] = v;
    }
  }
  router.get('/attendance', clean, { preserveState: true, replace: true });
}

// Convert logs or livePoints to map points
const mapPoints = computed(() => {
  if (livePoints.value && livePoints.value.length > 0) {
    return livePoints.value;
  }
  return props.logs.data.map(log => ({
    id: log.id,
    user_id: log.user_id,
    latitude: Number(log.latitude),
    longitude: Number(log.longitude),
    user_name: log.user?.name || '',
    job_title: log.user?.job_title || '',
    department_name: log.user?.department?.name || '',
    log_time: log.log_time,
    log_date: log.log_date,
    status: 'active',
  }));
});

// Fetch Live Locations from Server (Live Radar)
async function triggerLiveRadarSync() {
  isRadarScanning.value = true;
  try {
    const res = await axios.get('/attendance/live', {
      params: { date: filterForm.value.date }
    });
    if (res.data && res.data.points) {
      livePoints.value = res.data.points;
      liveActiveCount.value = res.data.active_count || 0;
    }
  } catch (err) {
    console.error('Live radar sync error:', err);
  } finally {
    isRadarScanning.value = false;
  }
}

function toggleAutoRefresh() {
  autoRefreshEnabled.value = !autoRefreshEnabled.value;
  if (autoRefreshEnabled.value) {
    triggerLiveRadarSync();
    autoRefreshTimer = setInterval(triggerLiveRadarSync, 30000);
  } else {
    if (autoRefreshTimer) {
      clearInterval(autoRefreshTimer);
      autoRefreshTimer = null;
    }
  }
}

// Fetch Trail for Specific Employee
async function viewEmployeeTrail(user) {
  if (!user || !user.id) return;
  selectedTrailEmployee.value = user;
  isTrailLoading.value = true;
  trailError.value = '';
  trailPoints.value = [];

  try {
    const res = await axios.get(`/attendance/trail/${user.id}`, {
      params: { date: filterForm.value.date }
    });
    if (res.data && res.data.points && res.data.points.length > 0) {
      trailPoints.value = res.data.points;
    } else {
      const currentLog = props.logs.data.find(l => l.user_id === user.id);
      if (currentLog && currentLog.latitude && currentLog.longitude) {
        trailPoints.value = [{
          id: currentLog.id,
          latitude: Number(currentLog.latitude),
          longitude: Number(currentLog.longitude),
          time: currentLog.log_time || '--:--',
          time_human: t('currentPoint'),
        }];
      } else {
        trailError.value = t('noTrailPoints');
      }
    }
  } catch (err) {
    trailError.value = err.response?.data?.message || t('connectionFailed');
  } finally {
    isTrailLoading.value = false;
  }
}

function closeTrailView() {
  selectedTrailEmployee.value = null;
  trailPoints.value = [];
  trailError.value = '';
}

function handleMapCoordinateSelect(coords) {
  manualForm.value.latitude = Number(coords.latitude.toFixed(6));
  manualForm.value.longitude = Number(coords.longitude.toFixed(6));
}

async function submitManualLocation() {
  if (!manualForm.value.user_id || !manualForm.value.latitude || !manualForm.value.longitude) {
    manualErrorMsg.value = t('selectEmployeeAndPinOnMap');
    return;
  }

  isUpdatingManual.value = true;
  manualSuccessMsg.value = '';
  manualErrorMsg.value = '';

  try {
    const res = await axios.post('/attendance/manual-update', {
      user_id: manualForm.value.user_id,
      latitude: manualForm.value.latitude,
      longitude: manualForm.value.longitude,
      date: manualForm.value.date,
    });

    manualSuccessMsg.value = res.data.message || t('savedSuccess');
    setTimeout(() => {
      router.reload({ preserveScroll: true });
    }, 800);
  } catch (err) {
    manualErrorMsg.value = err.response?.data?.message || t('saveFailed');
  } finally {
    isUpdatingManual.value = false;
  }
}

onUnmounted(() => {
  if (autoRefreshTimer) {
    clearInterval(autoRefreshTimer);
  }
});
</script>

<template>
  <Head :title="t('navAttendance')" />

  <AppLayout>
    <div class="space-y-6 animate-fade-in pb-12">
      <!-- Page Header Banner -->
      <PageBanner
        :title="t('navAttendance')"
        :subtitle="t('attendanceSubtitle')"
      >
        <template #actions>
          <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap">
            <!-- Live Radar Sync Button (Adaptive Light/Dark styling) -->
            <button
              @click="triggerLiveRadarSync"
              :disabled="isRadarScanning"
              type="button"
              class="h-10 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700 font-bold text-xs shadow-xs active:scale-95 disabled:opacity-50 transition-all flex items-center gap-2 cursor-pointer"
              :title="t('liveRadar')"
            >
              <Radio class="w-4 h-4 text-sky-600 dark:text-sky-400 shrink-0" :class="{ 'animate-pulse text-emerald-500': isRadarScanning }" />
              <span>{{ isRadarScanning ? t('liveRadarScanning') : t('liveRadar') }}</span>
              <span v-if="liveActiveCount > 0" class="px-2 py-0.5 rounded-full bg-emerald-500 text-white text-[10px] font-black shadow-xs">
                {{ liveActiveCount }}
              </span>
            </button>

            <!-- Auto Refresh Toggle (Adaptive Light/Dark styling) -->
            <button
              @click="toggleAutoRefresh"
              type="button"
              :class="autoRefreshEnabled 
                ? 'bg-emerald-600 text-white shadow-emerald-600/30' 
                : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700'"
              class="h-10 px-3.5 rounded-2xl font-bold text-xs transition-all flex items-center gap-1.5 cursor-pointer shadow-xs active:scale-95"
            >
              <RefreshCw class="w-3.5 h-3.5 shrink-0" :class="{ 'animate-spin': autoRefreshEnabled }" />
              <span>{{ t('autoRefresh') }}</span>
            </button>

            <!-- Admin Manual Pin Button -->
            <button
              v-if="canManualEdit"
              @click="showManualPanel = !showManualPanel"
              type="button"
              class="h-10 px-4 rounded-2xl bg-accent bg-accent-hover text-white font-bold text-xs shadow-accent hover:shadow-lg active:scale-95 transition-all flex items-center gap-2 cursor-pointer"
            >
              <Edit3 class="w-4 h-4" />
              <span>{{ showManualPanel ? t('hideManualAdjustment') : t('manualLocationPin') }}</span>
            </button>
          </div>
        </template>
      </PageBanner>

      <!-- Stats Counters -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
          <div class="space-y-1">
            <p class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ t('statTotalEmployees') }}</p>
            <h3 class="text-2xl font-black text-slate-900 dark:text-white">{{ stats.total_employees }}</h3>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center">
            <Users class="w-6 h-6" />
          </div>
        </div>

        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
          <div class="space-y-1">
            <p class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ t('statPresentToday') }}</p>
            <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ stats.total_present_today }}</h3>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
            <CheckCircle2 class="w-6 h-6" />
          </div>
        </div>

        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
          <div class="space-y-1">
            <p class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ t('statAbsentToday') }}</p>
            <h3 class="text-2xl font-black text-rose-600 dark:text-rose-400">{{ Math.max(0, stats.total_employees - stats.total_present_today) }}</h3>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center">
            <AlertCircle class="w-6 h-6" />
          </div>
        </div>
      </div>

      <!-- Filters Toolbar -->
      <div class="p-4 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3 flex-wrap flex-1">
          <!-- Date Filter -->
          <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-800/80 px-3.5 py-2 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs">
            <Calendar class="w-4 h-4 text-slate-400 shrink-0" />
            <input
              type="date"
              v-model="filterForm.date"
              @change="applyFilters"
              class="bg-transparent border-0 p-0 text-slate-700 dark:text-slate-200 font-bold focus:ring-0 cursor-pointer"
            />
          </div>

          <!-- Department Filter -->
          <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-800/80 px-3.5 py-2 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs min-w-[200px]">
            <Building2 class="w-4 h-4 text-slate-400 shrink-0" />
            <select
              v-model="filterForm.department_id"
              @change="applyFilters"
              class="bg-transparent border-0 p-0 text-slate-700 dark:text-slate-200 font-bold focus:ring-0 w-full cursor-pointer"
            >
              <option value="">{{ t('allDepartments') }}</option>
              <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
          </div>
        </div>

        <button
          @click="applyFilters"
          type="button"
          class="h-9 px-4 rounded-xl bg-accent bg-accent-hover text-white font-bold text-xs shadow-accent active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer"
        >
          <Filter class="w-3.5 h-3.5" />
          <span>{{ t('filter') }}</span>
        </button>
      </div>

      <!-- ========================================================= -->
      <!-- MAP VIEW & INTERACTIVE TRAIL VIEWER                       -->
      <!-- ========================================================= -->
      <div class="relative space-y-3">
        <!-- Trail Floating Information Banner (Adaptive Dark/Light Contrast) -->
        <div
          v-if="selectedTrailEmployee"
          class="p-4 sm:p-5 rounded-3xl bg-slate-900 dark:bg-slate-800 text-white border border-slate-700 shadow-2xl flex items-center justify-between flex-wrap gap-4 animate-fade-in"
        >
          <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-sky-500/20 text-sky-400 border border-sky-400/30 flex items-center justify-center font-black shrink-0">
              <Route class="w-6 h-6" />
            </div>
            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-bold text-slate-400">{{ t('trailTitle') }}:</span>
                <span class="font-black text-sm text-white">{{ selectedTrailEmployee.name }}</span>
                <span v-if="selectedTrailEmployee.department" class="px-2.5 py-0.5 rounded-full bg-sky-500/20 text-sky-300 border border-sky-500/30 text-[10px] font-bold">
                  {{ selectedTrailEmployee.department }}
                </span>
              </div>
              <div class="flex items-center gap-4 text-xs text-slate-300 mt-1 font-medium flex-wrap">
                <span>📍 {{ t('trailPointsCount') }}: <strong class="font-mono font-bold text-white">{{ trailPoints.length }}</strong></span>
                <span>🛣️ {{ t('approxDistance') }}: <strong class="font-mono font-bold text-emerald-400">{{ trailTotalDistanceKm }}</strong></span>
                <span v-if="trailPoints.length > 0">⏱️ {{ t('startPoint') }}: <strong class="font-mono font-bold text-sky-400">{{ trailPoints[0].time }}</strong></span>
                <span v-if="trailPoints.length > 1">🏁 {{ t('currentPoint') }}: <strong class="font-mono font-bold text-teal-400">{{ trailPoints[trailPoints.length - 1].time }}</strong></span>
              </div>
            </div>
          </div>

          <button
            @click="closeTrailView"
            type="button"
            class="h-9 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer active:scale-95"
          >
            <X class="w-3.5 h-3.5" />
            <span>{{ t('hideTrail') }}</span>
          </button>
        </div>

        <!-- Leaflet Map Component -->
        <AttendanceMap
          :points="mapPoints"
          :trail-points="trailPoints"
          :selected-employee="selectedTrailEmployee"
          :editable="showManualPanel"
          :selected-coords="manualForm"
          @select-coordinates="handleMapCoordinateSelect"
          @select-employee="viewEmployeeTrail"
        />
      </div>

      <!-- Admin Manual Adjustment Floating Panel -->
      <div
        v-if="canManualEdit && showManualPanel"
        class="p-5 rounded-3xl bg-white dark:bg-slate-900 border-2 border-rose-500/30 shadow-xl space-y-4 animate-fade-in"
      >
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
          <div class="flex items-center gap-2">
            <Edit3 class="w-5 h-5 text-rose-600" />
            <h4 class="font-black text-sm text-slate-900 dark:text-white">{{ t('manualLocationPin') }}</h4>
          </div>
          <span class="text-xs text-slate-400">{{ t('manualPinInstructions') }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <!-- Employee Picker -->
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate-600 dark:text-slate-300">{{ t('employee') }} *</label>
            <select
              v-model="manualForm.user_id"
              class="w-full h-11 px-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold focus:ring-2 focus:ring-accent"
            >
              <option value="">-- {{ t('selectEmployee') }} --</option>
              <option v-for="emp in allEmployees" :key="emp.id" :value="emp.id">
                {{ emp.name }} ({{ emp.department?.name || t('noDepartment') }})
              </option>
            </select>
          </div>

          <!-- Latitude -->
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate-600 dark:text-slate-300">{{ t('fixedLat') }} *</label>
            <input
              type="number"
              step="0.000001"
              v-model="manualForm.latitude"
              placeholder="33.31524"
              class="w-full h-11 px-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold focus:ring-2 focus:ring-accent"
            />
          </div>

          <!-- Longitude -->
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate-600 dark:text-slate-300">{{ t('fixedLng') }} *</label>
            <input
              type="number"
              step="0.000001"
              v-model="manualForm.longitude"
              placeholder="44.36612"
              class="w-full h-11 px-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold focus:ring-2 focus:ring-accent"
            />
          </div>
        </div>

        <!-- Success / Error messages -->
        <div v-if="manualSuccessMsg" class="p-3 rounded-2xl bg-emerald-50 text-emerald-800 text-xs font-bold flex items-center gap-2">
          <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
          <span>{{ manualSuccessMsg }}</span>
        </div>
        <div v-if="manualErrorMsg" class="p-3 rounded-2xl bg-rose-50 text-rose-800 text-xs font-bold flex items-center gap-2">
          <AlertCircle class="w-4 h-4 text-rose-600 shrink-0" />
          <span>{{ manualErrorMsg }}</span>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button
            @click="submitManualLocation"
            :disabled="isUpdatingManual"
            type="button"
            class="h-10 px-5 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-lg shadow-rose-600/30 active:scale-95 disabled:opacity-50 transition-all flex items-center gap-2 cursor-pointer"
          >
            <Save class="w-4 h-4" />
            <span>{{ isUpdatingManual ? t('saving') : t('saveManualPin') }}</span>
          </button>
        </div>
      </div>

      <!-- Attendance Table -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-2xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center">
              <UserCheck class="w-5 h-5" />
            </div>
            <div>
              <h3 class="font-black text-sm text-slate-900 dark:text-white">{{ t('tableAttendanceLog') }}</h3>
              <p class="text-[11px] text-slate-400 mt-0.5">{{ t('attendanceSubtitle') }}</p>
            </div>
          </div>
          <span class="text-xs font-bold text-slate-500 dark:text-slate-400">
            {{ props.logs.data.length }} {{ t('records') }}
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-start text-xs">
            <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-bold border-b border-slate-100 dark:border-slate-800">
              <tr>
                <th class="px-5 py-3.5 text-start">{{ t('employee') }}</th>
                <th class="px-5 py-3.5 text-start">{{ t('department') }}</th>
                <th class="px-5 py-3.5 text-start">{{ t('tableLogTime') }}</th>
                <th class="px-5 py-3.5 text-start">{{ t('gpsCoordinates') }}</th>
                <th class="px-5 py-3.5 text-start">{{ t('notes') }}</th>
                <th class="px-5 py-3.5 text-end">{{ t('actions') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
              <tr
                v-for="log in logs.data"
                :key="log.id"
                class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors"
                :class="{ 'bg-sky-50/50 dark:bg-sky-950/30': selectedTrailEmployee?.id === log.user_id }"
              >
                <!-- Employee -->
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-sky-600 to-teal-600 text-white font-black text-xs flex items-center justify-center">
                      {{ log.user?.name ? log.user.name.charAt(0) : 'U' }}
                    </div>
                    <div>
                      <div class="font-bold text-slate-900 dark:text-white">{{ log.user?.name }}</div>
                      <div class="text-[10px] text-slate-400">{{ log.user?.job_title || log.user?.email }}</div>
                    </div>
                  </div>
                </td>

                <!-- Department -->
                <td class="px-5 py-3.5 text-slate-600 dark:text-slate-300 font-semibold">
                  {{ log.user?.department?.name || t('noDepartment') }}
                </td>

                <!-- Time -->
                <td class="px-5 py-3.5 font-mono text-slate-700 dark:text-slate-300 font-bold">
                  {{ log.log_time || '--:--' }}
                </td>

                <!-- Coordinates -->
                <td class="px-5 py-3.5 font-mono text-[11px] text-slate-500 dark:text-slate-400">
                  <span v-if="log.latitude && log.longitude" class="flex items-center gap-1">
                    <MapPin class="w-3.5 h-3.5 text-sky-500 shrink-0" />
                    <span>{{ Number(log.latitude).toFixed(5) }}, {{ Number(log.longitude).toFixed(5) }}</span>
                  </span>
                  <span v-else class="text-slate-400">—</span>
                </td>

                <!-- Notes / Status -->
                <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 text-[11px]">
                  {{ log.notes || '—' }}
                </td>

                <!-- Actions: View Trail -->
                <td class="px-5 py-3.5 text-end">
                  <button
                    @click="viewEmployeeTrail(log.user)"
                    type="button"
                    class="h-8 px-3 rounded-xl bg-sky-50 dark:bg-sky-950/60 hover:bg-sky-100 dark:hover:bg-sky-900/60 text-sky-700 dark:text-sky-300 font-bold text-[11px] transition-all flex items-center gap-1.5 ms-auto cursor-pointer active:scale-95 shadow-2xs"
                    :title="t('viewTrail')"
                  >
                    <Route class="w-3.5 h-3.5 text-sky-600 dark:text-sky-400" />
                    <span>{{ t('viewTrail') }}</span>
                  </button>
                </td>
              </tr>

              <tr v-if="logs.data.length === 0">
                <td colspan="6" class="px-5 py-12 text-center text-slate-400 text-xs font-bold">
                  {{ t('noRecordsFound') }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="logs.links && logs.links.length > 3" class="p-4 border-t border-slate-100 dark:border-slate-800">
          <Pagination :links="logs.links" />
        </div>
      </div>

    </div>
  </AppLayout>
</template>
