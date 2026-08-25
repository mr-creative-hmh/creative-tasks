<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { t } from '@/i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
  Building2,
  Plus,
  Clock,
  UserCheck,
  Users,
  Edit2,
  Trash2,
  X,
  ShieldAlert
} from 'lucide-vue-next';

const props = defineProps({
  departments: {
    type: Array,
    default: () => []
  },
  heads: {
    type: Array,
    default: () => []
  }
});

const isModalOpen = ref(false);
const editingDept = ref(null);

const deptForm = useForm({
  name: '',
  manager_id: '',
  work_start_time: '08:00',
  work_end_time: '16:00',
});

function openCreateModal() {
  editingDept.value = null;
  deptForm.reset();
  deptForm.work_start_time = '08:00';
  deptForm.work_end_time = '16:00';
  isModalOpen.value = true;
}

function openEditModal(dept) {
  editingDept.value = dept;
  deptForm.name = dept.name;
  deptForm.manager_id = dept.manager_id || '';
  deptForm.work_start_time = dept.work_start_time ? dept.work_start_time.substring(0, 5) : '08:00';
  deptForm.work_end_time = dept.work_end_time ? dept.work_end_time.substring(0, 5) : '16:00';
  isModalOpen.value = true;
}

function submitDept() {
  if (editingDept.value) {
    deptForm.put(`/departments/${editingDept.value.id}`, {
      onSuccess: () => {
        isModalOpen.value = false;
      }
    });
  } else {
    deptForm.post('/departments', {
      onSuccess: () => {
        isModalOpen.value = false;
      }
    });
  }
}

function deleteDept(dept) {
  if (confirm(`هل أنت متأكد من حذف قسم "${dept.name}"؟`)) {
    router.delete(`/departments/${dept.id}`);
  }
}
</script>

<template>
  <Head :title="t('navDepartments')" />

  <AppLayout>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
          {{ t('navDepartments') }}
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
          إدارة الهيكل التنظيمي، تعيين رؤساء الأقسام، وضبط أوقات الدوام الرسمي
        </p>
      </div>

      <button
        @click="openCreateModal"
        type="button"
        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 active:scale-95 text-white font-bold text-xs shadow-md shadow-brand-500/20 transition-all cursor-pointer"
      >
        <Plus class="w-4 h-4" />
        <span>{{ t('addDepartment') }}</span>
      </button>
    </div>

    <!-- Departments Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div 
        v-for="dept in departments" 
        :key="dept.id"
        class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs hover:shadow-md transition-all flex flex-col justify-between"
      >
        <div>
          <div class="flex items-start justify-between gap-3 mb-3">
            <div class="w-10 h-10 rounded-2xl bg-brand-50 dark:bg-brand-950 text-brand-600 dark:text-brand-400 flex items-center justify-center font-bold">
              <Building2 class="w-5 h-5" />
            </div>

            <div class="flex items-center gap-1">
              <button 
                @click="openEditModal(dept)" 
                class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300"
              >
                <Edit2 class="w-4 h-4" />
              </button>
              <button 
                @click="deleteDept(dept)" 
                class="p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>

          <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug mb-1">
            {{ dept.name }}
          </h3>

          <div class="space-y-2 mt-4 text-xs text-slate-600 dark:text-slate-400">
            <!-- Manager -->
            <div class="flex items-center gap-2">
              <UserCheck class="w-4 h-4 text-brand-600 shrink-0" />
              <span>{{ t('deptManager') }}:</span>
              <strong class="text-slate-800 dark:text-slate-200">
                {{ dept.manager?.name || 'لم يُعيّن بعد' }}
              </strong>
            </div>

            <!-- Shift Hours -->
            <div class="flex items-center gap-2">
              <Clock class="w-4 h-4 text-amber-500 shrink-0" />
              <span>{{ t('shiftHours') }}:</span>
              <strong class="text-slate-800 dark:text-slate-200 font-mono">
                {{ dept.work_start_time?.substring(0, 5) }} - {{ dept.work_end_time?.substring(0, 5) }}
              </strong>
            </div>

            <!-- Employees Count -->
            <div class="flex items-center gap-2">
              <Users class="w-4 h-4 text-blue-500 shrink-0" />
              <span>{{ t('employeeCount') }}:</span>
              <strong class="text-slate-800 dark:text-slate-200">
                {{ dept.users_count }} موظف
              </strong>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create / Edit Modal -->
    <div v-if="isModalOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="isModalOpen = false">
      <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 max-w-md w-full p-6 shadow-2xl relative" @click.stop>
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
          <h3 class="text-sm font-bold text-slate-900 dark:text-white">
            {{ editingDept ? 'تعديل بيانات القسم وساعات العمل' : t('addDepartment') }}
          </h3>
          <button @click="isModalOpen = false" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitDept" class="space-y-4 text-xs">
          <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('deptName') }} *</label>
            <input
              v-model="deptForm.name"
              type="text"
              required
              placeholder="مثال: قسم المتابعة الميدانية"
              class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-brand-500"
            />
          </div>

          <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('deptManager') }}</label>
            <select
              v-model="deptForm.manager_id"
              class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none"
            >
              <option value="">{{ t('selectManager') }}</option>
              <option v-for="head in heads" :key="head.id" :value="head.id">
                {{ head.name }} ({{ head.email }})
              </option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('workStartTime') }} *</label>
              <input
                v-model="deptForm.work_start_time"
                type="time"
                required
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none"
              />
            </div>

            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('workEndTime') }} *</label>
              <input
                v-model="deptForm.work_end_time"
                type="time"
                required
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none"
              />
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
            <button
              @click="isModalOpen = false"
              type="button"
              class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 font-semibold text-slate-600 dark:text-slate-300"
            >
              {{ t('cancel') }}
            </button>
            <button
              :disabled="deptForm.processing"
              type="submit"
              class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold shadow-sm disabled:opacity-50"
            >
              {{ deptForm.processing ? 'جاري الحفظ...' : t('save') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
