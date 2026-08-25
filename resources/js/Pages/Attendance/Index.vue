<script setup>
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
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
  ExternalLink,
  Navigation,
  Clock,
  UserCheck,
  Edit3,
  CheckCircle2,
  AlertCircle,
  Sparkles,
  Search,
  Building2,
  User,
  Layers,
  ChevronDown,
  ChevronUp
} from 'lucide-vue-next';

const props = defineProps({
  logs: {
    type: Object,
    default: () => ({ data: [] })
  },
  mapPoints: {
    type: Array,
    default: () => []
  },
  departments: {
    type: Array,
    default: () => []
  },
  employees: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  stats: {
    type: Object,
    default: () => ({ total_present_today: 0, total_employees: 0 })
  }
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user || {});
const canManualEdit = computed(() => ['admin', 'head'].includes(authUser.value.role));

const filterForm = ref({
  department_id: props.filters.department_id || '',
  user_id: props.filters.user_id || '',
  date: props.filters.date ? props.filters.date.split('T')[0] : new Date().toISOString().split('T')[0],
});

// Admin Manual Update State
const showManualPanel = ref(false);
const manualForm = ref({
  user_id: props.employees[0]?.id || '',
  latitude: 33.31524,
  longitude: 44.36612,
  date: filterForm.value.date,
});
const isUpdatingManual = ref(false);
const manualSuccessMsg = ref('');
const manualErrorMsg = ref('');

function applyFilters() {
  const cleanParams = {};
  for (const [k, v] of Object.entries(filterForm.value)) {
    if (v !== '' && v !== null && v !== undefined) {
      cleanParams[k] = v;
    }
  }
  router.get('/attendance', cleanParams, { preserveState: true, replace: true });
}

function onMapCoordinateSelected(coords) {
  manualForm.value.latitude = Number(coords.latitude.toFixed(6));
  manualForm.value.longitude = Number(coords.longitude.toFixed(6));
  showManualPanel.value = true;
}

async function saveManualAttendance() {
  if (!manualForm.value.user_id) {
    manualErrorMsg.value = t('selectEmployeePlaceholder');
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
    <div class="space-y-5">
      
      <!-- 1. Page Header & Stats Summary -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
            <MapPin class="w-6 h-6 text-sky-600 dark:text-sky-400" />
            <span>{{ t('attendanceMapTitle') }}</span>
          </h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            {{ t('attendanceSubtitle') }}
          </p>
        </div>

        <!-- Quick Counters Strip -->
        <div class="flex items-center gap-2.5">
          <div class="px-3.5 py-2 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">
              <UserCheck class="w-4 h-4" />
            </div>
            <div>
              <div class="text-[10px] text-slate-400 font-semibold leading-tight">{{ t('attendanceTodayLabel') }}</div>
              <div class="text-xs font-black text-slate-800 dark:text-slate-100 font-mono">
                {{ stats.total_present_today }} / {{ stats.total_employees }}
              </div>
            </div>
          </div>

          <button
            v-if="canManualEdit"
            @click="showManualPanel = !showManualPanel"
            type="button"
            class="px-3.5 py-2.5 rounded-2xl bg-sky-50 dark:bg-sky-950/60 hover:bg-sky-100 dark:hover:bg-sky-900/60 text-sky-700 dark:text-sky-300 font-bold text-xs border border-sky-200 dark:border-sky-800/60 transition-all flex items-center gap-2 cursor-pointer"
          >
            <Edit3 class="w-4 h-4" />
            <span>{{ t('manualPinBtn') }}</span>
            <ChevronUp v-if="showManualPanel" class="w-3.5 h-3.5" />
            <ChevronDown v-else class="w-3.5 h-3.5" />
          </button>
        </div>

        <!-- Pagination Bar -->
        <Pagination
          :links="logs.links"
          :from="logs.from"
          :to="logs.to"
          :total="logs.total"
        />
      </div>

      <!-- 2. Admin / Head Manual Pinning Drawer Card -->
      <div 
        v-if="canManualEdit && showManualPanel" 
        class="bg-gradient-to-r from-sky-50/90 to-slate-50/90 dark:from-slate-900 dark:to-slate-850 p-4 sm:p-5 rounded-3xl border border-sky-200/80 dark:border-sky-900/50 shadow-xs animate-fade-in"
      >
        <div class="flex items-center justify-between mb-3 pb-2 border-b border-sky-100 dark:border-slate-800">
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-sky-500 animate-pulse"></span>
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
              class="w-full h-10 px-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 font-medium"
            >
              <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                {{ emp.name }}{{ emp.job_title ? ` (${emp.job_title})` : '' }}
              </option>
            </select>
          </div>

          <!-- Latitude -->
          <div>
            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ t('latitude') }} *</label>
            <input
              v-model.number="manualForm.latitude"
              type="number"
              step="0.000001"
              class="w-full h-10 px-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 font-mono"
            />
          </div>

          <!-- Longitude -->
          <div>
            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ t('longitude') }} *</label>
            <input
              v-model.number="manualForm.longitude"
              type="number"
              step="0.000001"
              class="w-full h-10 px-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 font-mono"
            />
          </div>

          <!-- Action Button -->
          <div class="flex items-end">
            <button
              @click="saveManualAttendance"
              :disabled="isUpdatingManual"
              type="button"
              class="w-full h-10 px-4 rounded-xl bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-bold text-xs shadow-md shadow-sky-600/20 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
            >
              <CheckCircle2 class="w-4 h-4" />
              <span>{{ isUpdatingManual ? t('savingManualLocation') : t('pinLocationBtn') }}</span>
            </button>
          </div>
        </div>

        <!-- Feedback Alerts -->
        <div v-if="manualSuccessMsg" class="mt-3 p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/70 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-xs font-bold flex items-center gap-2">
          <CheckCircle2 class="w-4 h-4 shrink-0" />
          <span>{{ manualSuccessMsg }}</span>
        </div>
        <div v-if="manualErrorMsg" class="mt-3 p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/70 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs font-bold flex items-center gap-2">
          <AlertCircle class="w-4 h-4 shrink-0" />
          <span>{{ manualErrorMsg }}</span>
        </div>

        <!-- Pagination Bar -->
        <Pagination
          :links="logs.links"
          :from="logs.from"
          :to="logs.to"
          :total="logs.total"
        />
      </div>

      <!-- 3. Dynamic Filter Bar -->
      <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <!-- Department Filter -->
          <div>
            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ t('department') }}</label>
            <select
              v-model="filterForm.department_id"
              @change="applyFilters"
              class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500"
            >
              <option value="">{{ t('allDepartments') }}</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                {{ dept.name }}
              </option>
            </select>
          </div>

          <!-- Employee Filter -->
          <div>
            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ t('tableEmployee') }}</label>
            <select
              v-model="filterForm.user_id"
              @change="applyFilters"
              class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500"
            >
              <option value="">{{ t('allEmployees') }}</option>
              <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                {{ emp.name }}{{ emp.job_title ? ` (${emp.job_title})` : '' }}
              </option>
            </select>
          </div>

          <!-- Date Filter -->
          <div>
            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ t('tableLogDate') }}</label>
            <input
              v-model="filterForm.date"
              @change="applyFilters"
              type="date"
              class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 font-mono"
            />
          </div>
        </div>

        <!-- Pagination Bar -->
        <Pagination
          :links="logs.links"
          :from="logs.from"
          :to="logs.to"
          :total="logs.total"
        />
      </div>

      <!-- 4. Interactive Live Map Section -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-4 sm:p-5 shadow-xs">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-950 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
              <Navigation class="w-4 h-4" />
            </div>
            <div>
              <h2 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
                {{ t('campusMapTitle') }}
              </h2>
              <span class="text-[10px] text-slate-400">{{ t('totalPinsCount', { count: mapPoints.length }) }}</span>
            </div>
          </div>

          <span class="text-[11px] font-bold text-sky-600 dark:text-sky-400 bg-sky-50 dark:bg-sky-950/60 px-2.5 py-1 rounded-xl border border-sky-200/60 dark:border-sky-800/60">
            {{ t('directCoordsCount', { count: mapPoints.length }) }}
          </span>
        </div>

        <!-- Leaflet Map Frame -->
        <div class="relative w-full h-[360px] sm:h-[440px] md:h-[500px] rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
          <AttendanceMap 
            :points="mapPoints" 
            :editable="canManualEdit"
            :selected-coords="{ latitude: manualForm.latitude, longitude: manualForm.longitude }"
            @select-coordinates="onMapCoordinateSelected"
          />
        </div>

        <!-- Pagination Bar -->
        <Pagination
          :links="logs.links"
          :from="logs.from"
          :to="logs.to"
          :total="logs.total"
        />
      </div>

      <!-- 5. Attendance Records Table (Desktop) & Cards (Mobile) -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
          <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <Clock class="w-4 h-4 text-sky-600" />
            <span>{{ t('attendanceTableTitle') }}</span>
          </h3>
          <span class="text-xs text-slate-400 font-mono bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-xl">
            {{ t('totalRecords') }} {{ logs.total || logs.data.length }}
          </span>
        </div>

        <!-- Desktop View Table -->
        <div class="hidden md:block overflow-x-auto">
          <table class="w-full text-start text-xs">
            <thead class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
              <tr>
                <th class="py-3.5 px-4 text-start">{{ t('tableEmployee') }}</th>
                <th class="py-3.5 px-4 text-start">{{ t('tableDepartment') }}</th>
                <th class="py-3.5 px-4 text-start">{{ t('tableCoordinates') }}</th>
                <th class="py-3.5 px-4 text-center">{{ t('tableLogTime') }}</th>
                <th class="py-3.5 px-4 text-center">{{ t('tableLogDate') }}</th>
                <th class="py-3.5 px-4 text-center">{{ t('tableMapAction') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr 
                v-for="log in logs.data" 
                :key="log.id"
                class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors"
              >
                <!-- Employee + Job Title -->
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900 dark:text-white">{{ log.user?.name }}</div>
                  <div v-if="log.user?.job_title" class="text-[10px] text-sky-600 dark:text-sky-400 font-semibold mt-0.5">
                    {{ log.user.job_title }}
                  </div>
                </td>

                <!-- Department -->
                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 font-medium">
                  {{ log.user?.department?.name || '-' }}
                </td>

                <!-- Coordinates -->
                <td class="py-3.5 px-4 font-mono text-[11px] text-slate-700 dark:text-slate-300">
                  <span class="bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-md">
                    {{ Number(log.latitude).toFixed(5) }}, {{ Number(log.longitude).toFixed(5) }}
                  </span>
                </td>

                <!-- Time -->
                <td class="py-3.5 px-4 text-center font-mono text-slate-700 dark:text-slate-300">
                  <span class="inline-flex items-center gap-1 font-bold">
                    ⏰ {{ log.log_time }}
                  </span>
                </td>

                <!-- Date -->
                <td class="py-3.5 px-4 text-center text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                  {{ log.log_date }}
                </td>

                <!-- Action Button -->
                <td class="py-3.5 px-4 text-center">
                  <a
                    :href="`https://www.google.com/maps?q=${log.latitude},${log.longitude}`"
                    target="_blank"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 font-bold hover:bg-sky-100 dark:hover:bg-sky-900 transition-colors text-[11px] border border-sky-200/60 dark:border-sky-800/60 cursor-pointer shadow-xs"
                  >
                    <ExternalLink class="w-3 h-3" />
                    <span>{{ t('googleMaps') }}</span>
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

        <!-- Mobile View Cards -->
        <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
          <div 
            v-for="log in logs.data" 
            :key="log.id"
            class="p-4 space-y-2.5 hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors"
          >
            <div class="flex items-start justify-between gap-2">
              <div>
                <div class="font-bold text-slate-900 dark:text-white text-xs">{{ log.user?.name }}</div>
                <div v-if="log.user?.job_title" class="text-[10px] text-sky-600 dark:text-sky-400 font-semibold mt-0.5">
                  {{ log.user.job_title }}
                </div>
                <div class="text-[10px] text-slate-400 mt-0.5">{{ log.user?.department?.name }}</div>
              </div>

              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-mono font-bold text-[10px] shrink-0 border border-emerald-200 dark:border-emerald-800/50">
                ⏰ {{ log.log_time }}
              </span>
            </div>

            <div class="flex items-center justify-between pt-1 text-[11px]">
              <span class="font-mono text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-md text-[10px]">
                📍 {{ Number(log.latitude).toFixed(5) }}, {{ Number(log.longitude).toFixed(5) }}
              </span>

              <a
                :href="`https://www.google.com/maps?q=${log.latitude},${log.longitude}`"
                target="_blank"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 font-bold text-[10px] border border-sky-200/60 dark:border-sky-800/60"
              >
                <ExternalLink class="w-3 h-3" />
                <span>{{ t('googleMaps') }}</span>
              </a>
            </div>
          </div>

          <div v-if="logs.data.length === 0" class="py-10 text-center text-slate-400 text-xs">
            {{ t('noLogsFound') }}
          </div>
        </div>

        <!-- Pagination Bar -->
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
