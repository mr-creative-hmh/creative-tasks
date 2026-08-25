<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { t } from '@/i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import AttendanceMap from '@/Components/AttendanceMap.vue';
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
  Sparkles
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
  }
});

const filterForm = ref({
  department_id: props.filters.department_id || '',
  user_id: props.filters.user_id || '',
  date: props.filters.date || new Date().toISOString().split('T')[0],
});

// Admin Manual Update State
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
  router.get('/attendance', filterForm.value, { preserveState: true, replace: true });
}

function onMapCoordinateSelected(coords) {
  manualForm.value.latitude = Number(coords.latitude.toFixed(6));
  manualForm.value.longitude = Number(coords.longitude.toFixed(6));
}

async function saveManualAttendance() {
  if (!manualForm.value.user_id) {
    manualErrorMsg.value = 'يرجى اختيار الموظف أولاً.';
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

    manualSuccessMsg.value = res.data.message || 'تم تحديث موقع الموظف يدوياً بنجاح!';
    setTimeout(() => {
      router.reload({ preserveScroll: true });
    }, 1000);
  } catch (err) {
    manualErrorMsg.value = err.response?.data?.message || 'فشل تحديث الموقع يدوياً.';
  } finally {
    isUpdatingManual.value = false;
  }
}
</script>

<template>
  <Head :title="t('navAttendance')" />

  <AppLayout>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
          {{ t('navAttendance') }}
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
          خريطة التتبع الميداني الحي وإدارة إحداثيات ومواقع حضور الموظفين بالـ GPS
        </p>
      </div>
    </div>

    <!-- Admin Manual Location Control Panel -->
    <div class="bg-gradient-to-br from-sky-50 to-teal-50 dark:from-slate-900 dark:to-slate-800/80 p-5 rounded-3xl border border-sky-200/80 dark:border-slate-700 shadow-sm mb-6">
      <div class="flex items-center gap-2 mb-3 pb-2 border-b border-sky-100 dark:border-slate-700">
        <Edit3 class="w-4 h-4 text-sky-600 dark:text-sky-400" />
        <h2 class="text-xs font-bold text-slate-900 dark:text-white">
          أداة تعديل موقع الموظف يدوياً عبر الخريطة (خاص بالإدارة):
        </h2>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
        <!-- Target Employee -->
        <div>
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">الموظف المراد تعديل موقعه</label>
          <select
            v-model="manualForm.user_id"
            class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100 font-medium"
          >
            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
              {{ emp.name }} ({{ emp.department?.name || 'بدون قسم' }})
            </option>
          </select>
        </div>

        <!-- Latitude -->
        <div>
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">خط العرض (Latitude)</label>
          <input
            v-model.number="manualForm.latitude"
            type="number"
            step="0.000001"
            class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100 font-mono"
          />
        </div>

        <!-- Longitude -->
        <div>
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">خط الطول (Longitude)</label>
          <input
            v-model.number="manualForm.longitude"
            type="number"
            step="0.000001"
            class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100 font-mono"
          />
        </div>

        <!-- Action Button -->
        <div class="flex items-end">
          <button
            @click="saveManualAttendance"
            :disabled="isUpdatingManual"
            type="button"
            class="w-full py-2.5 px-4 rounded-xl bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-bold shadow-md shadow-sky-500/20 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
          >
            <MapPin class="w-4 h-4" />
            <span>{{ isUpdatingManual ? 'جاري الحفظ...' : 'تثبيت الموقع الميداني' }}</span>
          </button>
        </div>
      </div>

      <!-- Alerts -->
      <div v-if="manualSuccessMsg" class="mt-3 p-3 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 text-xs flex items-center gap-2 border border-emerald-200">
        <CheckCircle2 class="w-4 h-4 shrink-0" />
        <span>{{ manualSuccessMsg }}</span>
      </div>
      <div v-if="manualErrorMsg" class="mt-3 p-3 rounded-xl bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 text-xs flex items-center gap-2 border border-rose-200">
        <AlertCircle class="w-4 h-4 shrink-0" />
        <span>{{ manualErrorMsg }}</span>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <!-- Department Filter -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-500 mb-1">القسم</label>
          <select
            v-model="filterForm.department_id"
            @change="applyFilters"
            class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100"
          >
            <option value="">{{ t('all') }} (كافة الأقسام)</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
        </div>

        <!-- Employee Filter -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-500 mb-1">الموظف</label>
          <select
            v-model="filterForm.user_id"
            @change="applyFilters"
            class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100"
          >
            <option value="">{{ t('all') }} (كافة الموظفين)</option>
            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
              {{ emp.name }}
            </option>
          </select>
        </div>

        <!-- Date -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-500 mb-1">التاريخ</label>
          <input
            v-model="filterForm.date"
            @change="applyFilters"
            type="date"
            class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100"
          />
        </div>
      </div>
    </div>

    <!-- Interactive Map Visualizer with Click to Pin -->
    <div class="mb-6 bg-white dark:bg-slate-900 rounded-3xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
          <Navigation class="w-4 h-4 text-sky-600" />
          <span>خريطة التوزيع الجغرافي للموظفين الميدانيين ({{ mapPoints.length }} تسجيل)</span>
        </h2>
      </div>

      <AttendanceMap 
        :points="mapPoints" 
        :editable="true"
        :selected-coords="{ latitude: manualForm.latitude, longitude: manualForm.longitude }"
        @select-coordinates="onMapCoordinateSelected"
      />
    </div>

    <!-- Attendance Logs Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
      <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <h3 class="text-xs font-bold text-slate-900 dark:text-white">سجل الحضور والإحداثيات</h3>
        <span class="text-xs text-slate-400">إجمالي السجلات: {{ logs.total || logs.data.length }}</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-start text-xs">
          <thead class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 text-slate-500 font-bold">
            <tr>
              <th class="py-3 px-4 text-start">الموظف</th>
              <th class="py-3 px-4 text-start">القسم</th>
              <th class="py-3 px-4 text-start">إحداثيات الـ GPS</th>
              <th class="py-3 px-4 text-start">وقت التسجيل</th>
              <th class="py-3 px-4 text-start">التاريخ</th>
              <th class="py-3 px-4 text-center">عرض بالخريطة</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr 
              v-for="log in logs.data" 
              :key="log.id"
              class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors"
            >
              <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                {{ log.user?.name }}
              </td>
              <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                {{ log.user?.department?.name }}
              </td>
              <td class="py-3 px-4 font-mono text-[11px] text-slate-700 dark:text-slate-300">
                {{ Number(log.latitude).toFixed(6) }}, {{ Number(log.longitude).toFixed(6) }}
              </td>
              <td class="py-3 px-4 font-mono text-slate-600 dark:text-slate-400">
                ⏰ {{ log.log_time }}
              </td>
              <td class="py-3 px-4 text-slate-500">
                {{ log.log_date }}
              </td>
              <td class="py-3 px-4 text-center">
                <a
                  :href="`https://www.google.com/maps?q=${log.latitude},${log.longitude}`"
                  target="_blank"
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 font-bold hover:bg-sky-100 transition-colors text-[11px]"
                >
                  <ExternalLink class="w-3 h-3" />
                  <span>Google Maps</span>
                </a>
              </td>
            </tr>

            <tr v-if="logs.data.length === 0">
              <td colspan="6" class="py-12 text-center text-slate-400">
                لا توجد سجلات حضور مسجلة في هذا التاريخ.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
