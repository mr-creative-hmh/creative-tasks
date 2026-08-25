<script setup>
import { ref, computed } from 'vue';
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
  AlertCircle
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

function applyFilters() {
  const clean = {};
  for (const [k, v] of Object.entries(filterForm.value)) {
    if (v !== '' && v !== null && v !== undefined) {
      clean[k] = v;
    }
  }
  router.get('/attendance', clean, { preserveState: true, replace: true });
}

// Convert logs to map points
const mapPoints = computed(() => {
  return props.logs.data.map(log => ({
    id: log.id,
    latitude: Number(log.latitude),
    longitude: Number(log.longitude),
    user_name: log.user?.name || '',
    department_name: log.user?.department?.name || '',
    log_time: log.log_time,
    log_date: log.log_date,
  }));
});

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
    }, 1000);
  } catch (err) {
    manualErrorMsg.value = err.response?.data?.message || 'Error updating location';
  } finally {
    isUpdatingManual.value = false;
  }
}
</script>

<template>
  <Head :title="t('navAttendance')" />

  <AppLayout>
    <div class="w-full space-y-6">
      
      <!-- Unified Page Banner -->
      <PageBanner
        :title="t('attendanceMapTitle')"
        :subtitle="t('attendanceSubtitle')"
        :badge="t('attendanceTodayLabel') + ': ' + stats.total_present_today + ' / ' + stats.total_employees"
        :icon="MapPin"
      >
        <template #actions>
          <button
            v-if="canManualEdit"
            @click="showManualPanel = !showManualPanel"
            type="button"
            class="h-10 px-4 rounded-xl bg-accent bg-accent-hover text-white font-bold text-xs shadow-accent active:scale-95 transition-all flex items-center gap-2 cursor-pointer"
          >
            <Edit3 class="w-4 h-4" />
            <span>{{ t('manualPinBtn') }}</span>
            <ChevronUp v-if="showManualPanel" class="w-3.5 h-3.5" />
            <ChevronDown v-else class="w-3.5 h-3.5" />
          </button>
        </template>
      </PageBanner>

      <!-- Admin / Head Manual Pinning Drawer Card -->
      <div 
        v-if="canManualEdit && showManualPanel" 
        class="bg-gradient-to-r from-sky-50/90 to-slate-50/90 dark:from-slate-900 dark:to-slate-850 p-4 sm:p-5 rounded-3xl border border-sky-200/80 dark:border-sky-900/50 shadow-xs animate-fade-in"
      >
        <div class="flex items-center justify-between mb-3 pb-2 border-b border-sky-100 dark:border-slate-800">
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-accent animate-pulse"></span>
            <h3 class="text-xs font-bold text-slate-900 dark:text-white">
              {{ t('adminManualLocationTitle') }}
            </h3>
          </div>
          <span class="text-[10px] text-slate-500 dark:text-slate-400 hidden sm:inline">
            {{ t('manualPinHint') }}
          </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          <!-- Target Employee -->
          <div>
            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ t('targetEmployee') }} *</label>
            <select
              v-model="manualForm.user_id"
              class="w-full h-10 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 outline-none text-slate-800 dark:text-slate-100 focus:border-sky-500 font-medium"
            >
              <option value="">{{ t('selectEmployee') }}</option>
              <option v-for="emp in allEmployees" :key="emp.id" :value="emp.id">
                {{ emp.name }} ({{ emp.department?.name || t('department') }})
              </option>
            </select>
          </div>

          <!-- Date -->
          <div>
            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ t('attendanceDate') }} *</label>
            <input
              v-model="manualForm.date"
              type="date"
              class="w-full h-10 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 outline-none text-slate-800 dark:text-slate-100 font-mono focus:border-sky-500"
            />
          </div>

          <!-- Coordinates Display -->
          <div>
            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ t('selectedCoordinates') }}</label>
            <div class="h-10 px-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs font-mono">
              <span v-if="manualForm.latitude && manualForm.longitude" class="text-slate-800 dark:text-slate-100 font-bold">
                {{ manualForm.latitude }}, {{ manualForm.longitude }}
              </span>
              <span v-else class="text-slate-400 italic text-[11px]">
                {{ t('clickOnMapToPin') }}
              </span>
              <MapPin class="w-3.5 h-3.5 text-accent shrink-0" />
            </div>
          </div>

          <!-- Action Button -->
          <div class="flex items-end">
            <button
              @click="submitManualLocation"
              :disabled="isUpdatingManual || !manualForm.user_id || !manualForm.latitude"
              type="button"
              class="w-full h-10 rounded-xl bg-accent bg-accent-hover text-white font-bold text-xs shadow-accent active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2 cursor-pointer"
            >
              <Save class="w-4 h-4" />
              <span>{{ isUpdatingManual ? t('saving') : t('saveLocationBtn') }}</span>
            </button>
          </div>
        </div>

        <!-- Success / Error Feedback Alerts -->
        <div v-if="manualSuccessMsg" class="mt-3 p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-xs font-bold flex items-center gap-2">
          <CheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0" />
          <span>{{ manualSuccessMsg }}</span>
        </div>
        <div v-if="manualErrorMsg" class="mt-3 p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs font-bold flex items-center gap-2">
          <AlertCircle class="w-4 h-4 text-rose-500 shrink-0" />
          <span>{{ manualErrorMsg }}</span>
        </div>
      </div>

      <!-- Live Interactive Map Section -->
      <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
          <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></div>
            <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
              {{ t('attendanceMapTitle') }}
            </h2>
          </div>
          <span class="text-[11px] text-slate-500 dark:text-slate-400">
            {{ canManualEdit ? t('mapHintAdmin') : t('autoTrackingActive') }}
          </span>
        </div>

        <AttendanceMap
          :points="mapPoints"
          :editable="canManualEdit && showManualPanel"
          :selected-coords="manualForm.latitude ? { latitude: manualForm.latitude, longitude: manualForm.longitude } : null"
          @select-coordinates="handleMapCoordinateSelect"
        />
      </div>

      <!-- Filters & Daily Log Table -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        
        <!-- Table Filters Header -->
        <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <!-- Date Filter -->
            <div>
              <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ t('date') }}</label>
              <div class="relative">
                <input
                  v-model="filterForm.date"
                  @change="applyFilters"
                  type="date"
                  class="w-full h-10 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 outline-none text-slate-800 dark:text-slate-100 font-mono focus:border-sky-500"
                />
              </div>
            </div>

            <!-- Department Filter -->
            <div>
              <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ t('department') }}</label>
              <select
                v-model="filterForm.department_id"
                @change="applyFilters"
                class="w-full h-10 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 outline-none text-slate-800 dark:text-slate-100 focus:border-sky-500 font-medium"
              >
                <option value="">{{ t('allDepartments') }}</option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                  {{ dept.name }}
                </option>
              </select>
            </div>

            <!-- Stat Info Badge -->
            <div class="h-10 flex items-center justify-between bg-slate-50 dark:bg-slate-800/60 px-4 rounded-xl border border-slate-100 dark:border-slate-800 self-end">
              <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">{{ t('attendanceTodayLabel') }}:</span>
              <span class="text-sm font-black text-emerald-600 dark:text-emerald-400 font-mono">
                {{ logs.total || logs.data.length }}
              </span>
            </div>
          </div>
        </div>

        <!-- Attendance Logs Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-start text-xs">
            <thead class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
              <tr>
                <th class="px-5 py-4 text-start">{{ t('employeeName') }}</th>
                <th class="px-5 py-4 text-start">{{ t('department') }}</th>
                <th class="px-5 py-4 text-start">{{ t('tableLogDate') }}</th>
                <th class="px-5 py-4 text-start">{{ t('tableLogTime') }}</th>
                <th class="px-5 py-4 text-start">{{ t('locationCoords') }}</th>
                <th class="px-5 py-4 text-center">{{ t('googleMaps') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr
                v-for="log in logs.data"
                :key="log.id"
                class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors"
              >
                <!-- Name -->
                <td class="px-5 py-4 font-bold text-slate-900 dark:text-white">
                  {{ log.user?.name }}
                </td>

                <!-- Department -->
                <td class="px-5 py-4 text-slate-600 dark:text-slate-400 font-medium">
                  {{ log.user?.department?.name || '-' }}
                </td>

                <!-- Date -->
                <td class="px-5 py-4 text-slate-600 dark:text-slate-400 font-mono text-[11px]">
                  {{ log.log_date }}
                </td>

                <!-- Time -->
                <td class="px-5 py-4 text-slate-900 dark:text-white font-mono font-bold">
                  {{ log.log_time }}
                </td>

                <!-- Coordinates -->
                <td class="px-5 py-4 text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                  {{ Number(log.latitude).toFixed(5) }}, {{ Number(log.longitude).toFixed(5) }}
                </td>

                <!-- Google Maps Link -->
                <td class="px-5 py-4 text-center">
                  <a
                    :href="`https://maps.google.com/?q=${log.latitude},${log.longitude}`"
                    target="_blank"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800 text-[11px] font-bold hover:bg-sky-100 dark:hover:bg-sky-900 transition-colors"
                  >
                    <ExternalLink class="w-3 h-3" />
                    <span>{{ t('viewOnGoogleMaps') }}</span>
                  </a>
                </td>
              </tr>

              <tr v-if="logs.data.length === 0">
                <td colspan="6" class="py-12 text-center text-slate-400">
                  {{ t('noLogsFound') }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <Pagination
          :links="logs.links"
          :from="logs.from"
          :to="logs.to"
          :total="logs.total"
        />
      </div>

    </div>
  </AppLayout>
</template>
