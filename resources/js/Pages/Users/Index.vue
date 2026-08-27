<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { t } from '@/i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import PageBanner from '@/Components/PageBanner.vue';
import LocationPickerMap from '@/Components/LocationPickerMap.vue';
import {
  Users,
  UserPlus,
  Search,
  Filter,
  Shield,
  Building2,
  CheckCircle2,
  XCircle,
  Edit2,
  Trash2,
  X,
  Mail,
  Lock,
  Briefcase,
  FileSpreadsheet,
  Download,
  Upload,
  Sparkles,
  FileText,
  AlertCircle,
  KeyRound,
  Copy,
  Check,
  MapPin,
  Navigation,
  Crosshair,
  Radio
} from 'lucide-vue-next';

const props = defineProps({
  users: {
    type: Object,
    default: () => ({ data: [], links: [] })
  },
  departments: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Object,
    default: () => ({})
  }
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user || {});

const filterForm = ref({
  search: props.filters.search || '',
  role: props.filters.role || '',
  department_id: props.filters.department_id || '',
});

const isModalOpen = ref(false);
const editingUser = ref(null);

const userForm = useForm({
  name: '',
  job_title: '',
  email: '',
  password: '',
  role: 'employee',
  department_id: '',
  is_active: true,
  attendance_mode: 'gps',
  fixed_latitude: 33.31524,
  fixed_longitude: 44.36612,
  fixed_location_name: 'المقر الرئيسي للمؤسسة',
});

// Bulk Excel Import State
const isImportModalOpen = ref(false);
const importFileRef = ref(null);
const selectedFileName = ref('');

const importForm = useForm({
  file: null,
});

// Reset Password State
const isResetPasswordModalOpen = ref(false);
const resetPasswordUser = ref(null);
const passwordCopied = ref(false);

const resetPasswordForm = useForm({
  password: '',
});

// Location & GPS Mode Modal State
const isLocationModalOpen = ref(false);
const locationTargetUser = ref(null);

const locationForm = useForm({
  attendance_mode: 'gps',
  fixed_latitude: 33.31524,
  fixed_longitude: 44.36612,
  fixed_location_name: '',
});

const corporatePresets = [
  { name: 'المقر الرئيسي للمؤسسة (المركز)', lat: 33.31524, lng: 44.36612 },
  { name: 'مبنى الإدارة العامة والمكاتب التنفيذية', lat: 33.31550, lng: 44.36580 },
  { name: 'مجمع القاعات والمختبرات المركزية', lat: 33.31480, lng: 44.36650 },
  { name: 'مركز تكنولوجيا المعلومات والبرمجيات', lat: 33.31510, lng: 44.36680 },
  { name: 'مركز العمليات والمشاريع الميدانية', lat: 33.31570, lng: 44.36620 },
];

function openLocationModal(user) {
  locationTargetUser.value = user;
  locationForm.attendance_mode = user.attendance_mode || 'gps';
  locationForm.fixed_latitude = user.fixed_latitude || 33.31524;
  locationForm.fixed_longitude = user.fixed_longitude || 44.36612;
  locationForm.fixed_location_name = user.fixed_location_name || (user.department?.name ? 'مقر ' + user.department.name : 'المقر الرئيسي للمؤسسة');
  isLocationModalOpen.value = true;
}

function applyLocationPreset(preset) {
  locationForm.fixed_latitude = preset.lat;
  locationForm.fixed_longitude = preset.lng;
  locationForm.fixed_location_name = preset.name;
}

function captureCurrentBrowserLocation() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        locationForm.fixed_latitude = parseFloat(pos.coords.latitude.toFixed(6));
        locationForm.fixed_longitude = parseFloat(pos.coords.longitude.toFixed(6));
      },
      (err) => {
        alert('تعذر التقاط إشارة الـ GPS: ' + err.message);
      },
      { enableHighAccuracy: true, timeout: 8000 }
    );
  }
}

function submitLocationSettings() {
  if (!locationTargetUser.value) return;

  locationForm.put('/users/' + locationTargetUser.value.id + '/location-settings', {
    preserveScroll: true,
    onSuccess: () => {
      isLocationModalOpen.value = false;
      locationForm.reset();
    }
  });
}


function toggleUserAttendanceMode(user) {
  const newMode = user.attendance_mode === 'fixed' ? 'gps' : 'fixed';
  router.put('/users/' + user.id + '/location-settings', {
    attendance_mode: newMode,
    fixed_latitude: user.fixed_latitude || 33.31524,
    fixed_longitude: user.fixed_longitude || 44.36612,
    fixed_location_name: user.fixed_location_name || (user.department?.name ? 'مقر ' + user.department.name : 'المقر الرئيسي للمؤسسة')
  }, {
    preserveScroll: true
  });
}

function applyFilters() {
  const clean = {};
  for (const [k, v] of Object.entries(filterForm.value)) {
    if (v !== '' && v !== null && v !== undefined) {
      clean[k] = v;
    }
  }
  router.get('/users', clean, { preserveState: true, replace: true });
}

function openCreateModal() {
  editingUser.value = null;
  userForm.reset();
  userForm.role = 'employee';
  userForm.is_active = true;
  userForm.job_title = '';
  userForm.attendance_mode = 'gps';
  userForm.fixed_latitude = 33.31524;
  userForm.fixed_longitude = 44.36612;
  userForm.fixed_location_name = 'المقر الرئيسي للمؤسسة';
  userForm.attendance_mode = 'gps';
  userForm.fixed_latitude = 33.31524;
  userForm.fixed_longitude = 44.36612;
  userForm.fixed_location_name = '';
  isModalOpen.value = true;
}

function openEditModal(user) {
  editingUser.value = user;
  userForm.name = user.name;
  userForm.job_title = user.job_title || '';
  userForm.email = user.email;
  userForm.password = '';
  userForm.role = user.role;
  userForm.department_id = user.department_id || '';
  userForm.is_active = !!user.is_active;
  userForm.attendance_mode = user.attendance_mode || 'gps';
  userForm.fixed_latitude = user.fixed_latitude ? Number(user.fixed_latitude) : 33.31524;
  userForm.fixed_longitude = user.fixed_longitude ? Number(user.fixed_longitude) : 44.36612;
  userForm.fixed_location_name = user.fixed_location_name || (user.department?.name ? 'مقر ' + user.department.name : 'المقر الرئيسي للمؤسسة');
  userForm.attendance_mode = user.attendance_mode || 'gps';
  userForm.fixed_latitude = user.fixed_latitude || 33.31524;
  userForm.fixed_longitude = user.fixed_longitude || 44.36612;
  userForm.fixed_location_name = user.fixed_location_name || (user.department?.name ? 'مقر ' + user.department.name : 'المقر الرئيسي للمؤسسة');
  isModalOpen.value = true;
}

function openResetPasswordModal(user) {
  resetPasswordUser.value = user;
  resetPasswordForm.reset();
  generatePassword();
  passwordCopied.value = false;
  isResetPasswordModalOpen.value = true;
}

function generatePassword() {
  const randomDigits = Math.floor(1000 + Math.random() * 9000);
  resetPasswordForm.password = `Mamon@${randomDigits}`;
}

function copyPassword() {
  if (navigator.clipboard && resetPasswordForm.password) {
    navigator.clipboard.writeText(resetPasswordForm.password);
    passwordCopied.value = true;
    setTimeout(() => (passwordCopied.value = false), 2000);
  }
}

function submitResetPassword() {
  if (!resetPasswordUser.value) return;

  resetPasswordForm.put(`/users/${resetPasswordUser.value.id}/reset-password`, {
    preserveScroll: true,
    onSuccess: () => {
      isResetPasswordModalOpen.value = false;
      resetPasswordForm.reset();
    }
  });
}

function submitUser() {
  if (editingUser.value) {
    userForm.put(`/users/${editingUser.value.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        isModalOpen.value = false;
        userForm.reset();
      }
    });
  } else {
    userForm.post('/users', {
      preserveScroll: true,
      onSuccess: () => {
        isModalOpen.value = false;
        userForm.reset();
      }
    });
  }
}

function deleteUser(user) {
  if (user.id === authUser.value.id) return;
  if (confirm(t('confirmDeleteUser'))) {
    router.delete(`/users/${user.id}`, { preserveScroll: true });
  }
}

function toggleStatus(user) {
  router.post(`/users/${user.id}/toggle-status`, {}, { preserveScroll: true });
}

function handleFileChange(event) {
  const file = event.target.files[0];
  if (file) {
    importForm.file = file;
    selectedFileName.value = file.name;
  }
}

function triggerFileInput() {
  importFileRef.value?.click();
}

function submitImport() {
  if (!importForm.file) return;

  importForm.post('/users/import', {
    preserveScroll: true,
    onSuccess: () => {
      isImportModalOpen.value = false;
      importForm.reset();
      selectedFileName.value = '';
    }
  });
}
</script>

<template>
  <Head :title="t('navUsers')" />

  <AppLayout>
    <div class="w-full space-y-6">
      <!-- Unified Page Banner -->
      <PageBanner
        :title="t('navUsers')"
        :subtitle="t('usersSubtitle')"
        :badge="t('totalUsers') + ': ' + (users.total || users.data.length)"
        :icon="Users"
      >
        <template #actions>
          <!-- Download Template Button -->
          <a
            href="/users/template"
            target="_blank"
            class="h-10 inline-flex items-center justify-center gap-2 px-3.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/80 active:scale-95 text-slate-700 dark:text-slate-200 font-bold text-xs shadow-xs transition-all cursor-pointer"
            :title="t('downloadTemplate')"
          >
            <Download class="w-4 h-4 text-accent" />
            <span>{{ t('downloadTemplate') }}</span>
          </a>

          <!-- Import Excel Button -->
          <button
            @click="isImportModalOpen = true"
            type="button"
            class="h-10 inline-flex items-center justify-center gap-2 px-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all cursor-pointer"
          >
            <FileSpreadsheet class="w-4 h-4" />
            <span>{{ t('importExcel') }}</span>
          </button>

          <!-- Add Single User Button -->
          <button
            @click="openCreateModal"
            type="button"
            class="h-10 inline-flex items-center justify-center gap-2 px-4 rounded-xl bg-accent bg-accent-hover active:scale-95 text-white font-bold text-xs shadow-accent transition-all cursor-pointer"
          >
            <UserPlus class="w-4 h-4" />
            <span>{{ t('addUser') }}</span>
          </button>
        </template>
      </PageBanner>

      <!-- Filters Bar -->
      <div class="bg-white dark:bg-slate-900 p-4 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          <!-- Search -->
          <div class="relative">
            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ t('search') }}</label>
            <div class="relative">
              <input
                v-model="filterForm.search"
                @input="applyFilters"
                type="text"
                :placeholder="t('search')"
                class="w-full h-10 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl ps-9 pe-3 outline-none text-slate-800 dark:text-slate-100 focus:border-sky-500 font-medium"
              />
              <Search class="w-4 h-4 text-slate-400 absolute start-3 top-3" />
            </div>
          </div>

          <!-- Role Filter -->
          <div>
            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ t('role') }}</label>
            <select
              v-model="filterForm.role"
              @change="applyFilters"
              class="w-full h-10 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 outline-none text-slate-800 dark:text-slate-100 focus:border-sky-500 font-medium"
            >
              <option value="">{{ t('allRoles') }}</option>
              <option value="admin">{{ t('adminRole') }}</option>
              <option value="head">{{ t('headRole') }}</option>
              <option value="employee">{{ t('employeeRole') }}</option>
            </select>
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

          <!-- Stats Counter -->
          <div class="h-10 flex items-center justify-between bg-slate-50 dark:bg-slate-800/60 px-3 rounded-xl border border-slate-100 dark:border-slate-800 self-end">
            <div>
              <span class="text-[10px] text-slate-400 font-semibold">{{ t('totalUsers') }}:</span>
              <span class="text-sm font-black text-slate-800 dark:text-slate-200 font-mono ms-1">{{ users.total || users.data.length }}</span>
            </div>
            <div class="text-end">
              <span class="text-[10px] text-emerald-500 font-semibold">{{ t('activeUsers') }}:</span>
              <span class="text-sm font-black text-emerald-600 dark:text-emerald-400 font-mono ms-1">
                {{ users.data.filter(u => u.is_active).length }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Users Table Container -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-start text-xs border-collapse">
            <thead>
              <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 text-slate-400">
                <th class="px-5 py-3.5 text-start font-bold">{{ t('name') }}</th>
                <th class="px-5 py-3.5 text-start font-bold">{{ t('email') }}</th>
                <th class="px-5 py-3.5 text-start font-bold">{{ t('department') }}</th>
                <th class="px-5 py-3.5 text-start font-bold">{{ t('role') }}</th>
                <th class="px-5 py-3.5 text-start font-bold">حالة الـ GPS والموقع</th>
                <th class="px-5 py-3.5 text-center font-bold">{{ t('status') }}</th>
                <th class="px-5 py-3.5 text-center font-bold">{{ t('actions') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr
                v-for="u in users.data"
                :key="u.id"
                class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors"
              >
                <!-- Name & Job Title -->
                <td class="px-5 py-4">
                  <div class="font-bold text-slate-900 dark:text-white">{{ u.name }}</div>
                  <div v-if="u.job_title" class="text-[10px] text-accent font-semibold mt-0.5">
                    {{ u.job_title }}
                  </div>
                </td>

                <!-- Email -->
                <td class="px-5 py-4 text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                  {{ u.email }}
                </td>

                <!-- Department -->
                <td class="px-5 py-4 text-slate-600 dark:text-slate-400 font-medium">
                  {{ u.department?.name || '-' }}
                </td>

                <!-- Role Badge -->
                <td class="px-5 py-4">
                  <span
                    :class="{
                      'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60': u.role === 'admin',
                      'bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800/60': u.role === 'head',
                      'bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border-teal-200 dark:border-teal-800/60': u.role === 'employee',
                    }"
                    class="whitespace-nowrap inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                  >
                    {{ t(u.role + 'Role') }}
                  </span>
                </td>

                <!-- Clean Individual GPS / Location Status Toggle -->
                <td class="px-5 py-4">
                  <button
                    @click="toggleUserAttendanceMode(u)"
                    type="button"
                    class="cursor-pointer group text-start inline-flex items-center gap-1.5"
                    :title="'انقر للتبديل السريع بين GPS والموقع الثابت لهذا المستخدم'"
                  >
                    <div
                      v-if="u.attendance_mode === 'gps' || !u.attendance_mode"
                      class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-sky-50 dark:bg-sky-950/60 hover:bg-sky-100 dark:hover:bg-sky-900/60 border border-sky-200 dark:border-sky-800 text-sky-700 dark:text-sky-300 font-bold text-[10px] transition-all shadow-xs"
                    >
                      <Navigation class="w-3 h-3 text-sky-600 dark:text-sky-400" />
                      <span>GPS نشط (ميداني)</span>
                    </div>

                    <div
                      v-else
                      class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-purple-50 dark:bg-purple-950/60 hover:bg-purple-100 dark:hover:bg-purple-900/60 border border-purple-200 dark:border-purple-800 text-purple-700 dark:text-purple-300 font-bold text-[10px] transition-all shadow-xs"
                    >
                      <Building2 class="w-3 h-3 text-purple-600 dark:text-purple-400 shrink-0" />
                      <span class="truncate max-w-[130px]">
                        {{ u.fixed_location_name || 'المقر الرئيسي للمؤسسة' }}
                      </span>
                    </div>
                  </button>
                </td>

                <!-- Active Status -->
                <td class="px-5 py-4 text-center">
                  <button
                    @click="toggleStatus(u)"
                    type="button"
                    :class="u.is_active ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/50' : 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-800/50'"
                    class="whitespace-nowrap inline-flex items-center justify-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold border transition-colors cursor-pointer"
                  >
                    <CheckCircle2 v-if="u.is_active" class="w-3 h-3" />
                    <XCircle v-else class="w-3 h-3" />
                    <span>{{ u.is_active ? t('active') : t('inactive') }}</span>
                  </button>
                </td>

                <!-- Actions: Edit, Reset Password, Delete -->
                <td class="px-5 py-4 text-center">
                  <div class="inline-flex items-center gap-1.5">
                    <!-- Edit Button -->
                    <button
                      @click="openEditModal(u)"
                      type="button"
                      class="p-1.5 rounded-lg hover:bg-accent-light dark:hover:bg-accent-light/30 text-accent cursor-pointer transition-colors"
                      :title="t('edit')"
                    >
                      <Edit2 class="w-4 h-4" />
                    </button>

                    <!-- Reset Password Button -->
                    <button
                      @click="openResetPasswordModal(u)"
                      type="button"
                      class="p-1.5 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-950/40 text-amber-600 dark:text-amber-400 cursor-pointer transition-colors"
                      :title="t('resetPassword')"
                    >
                      <KeyRound class="w-4 h-4" />
                    </button>

                    <!-- Delete Button ONLY if not current logged-in user -->
                    <button
                      v-if="u.id !== authUser.id"
                      @click="deleteUser(u)"
                      type="button"
                      class="p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 cursor-pointer transition-colors"
                      :title="t('delete')"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>

                    <span
                      v-else
                      class="px-2 py-0.5 rounded-md bg-accent-light border border-accent/20 text-[10px] font-bold text-accent select-none"
                      :title="t('cannotDeleteSelf')"
                    >
                      {{ t('cannotDeleteSelf') }}
                    </span>
                  </div>
                </td>
              </tr>

              <tr v-if="users.data.length === 0">
                <td colspan="7" class="py-12 text-center text-slate-400">
                  {{ t('noTasksFound') }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Component -->
        <Pagination
          :links="users.links"
          :from="users.from"
          :to="users.to"
          :total="users.total"
        />
      </div>
    </div>

    <!-- Create / Edit User Modal -->
    <div v-if="isModalOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto" @click="isModalOpen = false">
      <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 max-w-xl w-full p-5 sm:p-6 shadow-2xl relative my-6" @click.stop>
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-accent text-white flex items-center justify-center shadow-xs">
              <UserPlus v-if="!editingUser" class="w-4 h-4" />
              <Edit2 v-else class="w-4 h-4" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                {{ editingUser ? t('editUser') : t('addUser') }}
              </h3>
              <p class="text-[10px] text-slate-400">
                {{ editingUser ? editingUser.name + ' (' + editingUser.email + ')' : 'إضافة مستخدم جديد للنظام مع ضبط إعدادات وموقع الحضور' }}
              </p>
            </div>
          </div>
          <button @click="isModalOpen = false" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitUser" class="space-y-3.5 text-xs">
          <!-- Name & Job Title -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('userName') }} *</label>
              <input
                v-model="userForm.name"
                type="text"
                required
                class="w-full h-9 px-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 font-medium"
              />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('jobTitle') }}</label>
              <input
                v-model="userForm.job_title"
                type="text"
                :placeholder="t('jobTitlePlaceholder')"
                class="w-full h-9 px-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 font-medium"
              />
            </div>
          </div>

          <!-- Email & Password -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('email') }} *</label>
              <input
                v-model="userForm.email"
                type="email"
                required
                class="w-full h-9 px-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 font-mono"
              />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                {{ t('password') }} {{ editingUser ? `(${t('optionalPasswordHint')})` : '*' }}
              </label>
              <input
                v-model="userForm.password"
                type="password"
                :required="!editingUser"
                class="w-full h-9 px-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 font-medium"
              />
            </div>
          </div>

          <!-- Role & Department -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('userRole') }} *</label>
              <select
                v-model="userForm.role"
                required
                class="w-full h-9 px-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 font-medium"
              >
                <option value="employee">{{ t('employeeRole') }}</option>
                <option value="head">{{ t('headRole') }}</option>
                <option value="admin">{{ t('adminRole') }}</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('userDepartment') }}</label>
              <select
                v-model="userForm.department_id"
                class="w-full h-9 px-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 font-medium"
              >
                <option value="">{{ t('selectDept') }}</option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                  {{ dept.name }}
                </option>
              </select>
            </div>
          </div>

          <!-- Active Toggle -->
          <div class="flex items-center gap-2 pt-0.5">
            <input
              v-model="userForm.is_active"
              type="checkbox"
              id="is_active_toggle"
              class="w-4 h-4 rounded text-sky-600 focus:ring-sky-500 cursor-pointer"
            />
            <label for="is_active_toggle" class="font-semibold text-slate-700 dark:text-slate-300 select-none cursor-pointer">
              {{ t('activeAccountCheckbox') }}
            </label>
          </div>

          <!-- Attendance & Location Mode Section (Per-User) -->
          <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
            <div class="flex items-center justify-between">
              <label class="text-[11px] font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                <MapPin class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400" />
                <span>إعدادات ونمط الموقع الجغرافي لهذا المستخدم</span>
              </label>
              <span class="text-[10px] text-slate-400">اختر نمط الحضور</span>
            </div>

            <!-- Radio Options -->
            <div class="grid grid-cols-2 gap-2.5">
              <label
                :class="userForm.attendance_mode === 'gps' ? 'border-sky-500 bg-sky-50/80 dark:bg-sky-950/60 ring-2 ring-sky-500/20' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800'"
                class="flex items-start gap-2 p-2.5 rounded-xl border cursor-pointer transition-all"
              >
                <input type="radio" v-model="userForm.attendance_mode" value="gps" class="mt-0.5 text-sky-600" />
                <div>
                  <div class="text-[11px] font-bold text-slate-900 dark:text-white flex items-center gap-1">
                    <Navigation class="w-3 h-3 text-sky-600" />
                    <span>تتبع GPS ميداني</span>
                  </div>
                  <p class="text-[9px] text-slate-400 mt-0.5">تتبع GPS نشط عبر جهاز الموظف</p>
                </div>
              </label>

              <label
                :class="userForm.attendance_mode === 'fixed' ? 'border-purple-500 bg-purple-50/80 dark:bg-purple-950/60 ring-2 ring-purple-500/20' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800'"
                class="flex items-start gap-2 p-2.5 rounded-xl border cursor-pointer transition-all"
              >
                <input type="radio" v-model="userForm.attendance_mode" value="fixed" class="mt-0.5 text-purple-600" />
                <div>
                  <div class="text-[11px] font-bold text-slate-900 dark:text-white flex items-center gap-1">
                    <Building2 class="w-3 h-3 text-purple-600" />
                    <span>موقع عمل ثابت (المقر/الفرع)</span>
                  </div>
                  <p class="text-[9px] text-slate-400 mt-0.5">تسجيل تلقائي بدون إلزام GPS</p>
                </div>
              </label>
            </div>

            <!-- Fixed Location Details & Interactive Map -->
            <div v-if="userForm.attendance_mode === 'fixed'" class="space-y-2.5 pt-2 border-t border-slate-200 dark:border-slate-700 animate-fade-in">
              <div>
                <label class="block text-[10px] font-bold text-slate-700 dark:text-slate-300 mb-1">اسم المقر / المكتب *</label>
                <input
                  v-model="userForm.fixed_location_name"
                  type="text"
                  placeholder="مثال: المقر الرئيسي للمؤسسة / المكتب التنفيذي"
                  class="w-full h-8 px-2.5 text-xs rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white outline-none focus:border-purple-500"
                />
              </div>

              <!-- Quick Presets -->
              <div>
                <label class="block text-[9px] font-bold uppercase text-slate-400 mb-1">مواقع سريعة معتمدة داخل المؤسسة</label>
                <div class="flex flex-wrap gap-1">
                  <button
                    v-for="preset in corporatePresets"
                    :key="preset.name"
                    @click="applyFormPreset(preset)"
                    type="button"
                    class="px-2 py-0.5 rounded-lg text-[10px] font-medium bg-white dark:bg-slate-800 hover:bg-purple-100 dark:hover:bg-purple-950 text-slate-700 dark:text-slate-300 hover:text-purple-700 border border-slate-200 dark:border-slate-700 cursor-pointer"
                  >
                    {{ preset.name }}
                  </button>
                </div>
              </div>

              <!-- Leaflet Map Picker -->
              <div>
                <label class="block text-[10px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                  تحديد الموقع على الخريطة (افتراضي: المقر الرئيسي للمؤسسة)
                </label>
                <LocationPickerMap
                  v-model:lat="userForm.fixed_latitude"
                  v-model:lng="userForm.fixed_longitude"
                  height="190px"
                />
              </div>

              <!-- Coordinates Inputs -->
              <div class="grid grid-cols-2 gap-2">
                <div>
                  <label class="block text-[9px] font-bold text-slate-400 mb-0.5">خط العرض (Lat)</label>
                  <input
                    v-model="userForm.fixed_latitude"
                    type="number"
                    step="any"
                    class="w-full h-7 px-2 text-[11px] font-mono rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white"
                  />
                </div>
                <div>
                  <label class="block text-[9px] font-bold text-slate-400 mb-0.5">خط الطول (Lng)</label>
                  <input
                    v-model="userForm.fixed_longitude"
                    type="number"
                    step="any"
                    class="w-full h-7 px-2 text-[11px] font-mono rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
            <button
              @click="isModalOpen = false"
              type="button"
              class="h-9 px-4 rounded-xl border border-slate-200 dark:border-slate-700 font-semibold text-slate-600 dark:text-slate-300 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-xs"
            >
              {{ t('cancel') }}
            </button>
            <button
              :disabled="userForm.processing"
              type="submit"
              class="h-9 px-5 rounded-xl bg-accent bg-accent-hover active:scale-95 text-white font-bold text-xs shadow-accent transition-all cursor-pointer disabled:opacity-50"
            >
              {{ editingUser ? t('saveChanges') : t('addUser') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Reset Password Modal -->
    <div v-if="isResetPasswordModalOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="isResetPasswordModalOpen = false">
      <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 max-w-md w-full p-6 shadow-2xl relative" @click.stop>
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
              <KeyRound class="w-4 h-4" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                {{ t('resetPassword') }}
              </h3>
              <p class="text-[11px] text-slate-400 mt-0.5">{{ resetPasswordUser?.name }}</p>
            </div>
          </div>
          <button @click="isResetPasswordModalOpen = false" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitResetPassword" class="space-y-4 text-xs">
          <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
              {{ t('newPasswordPrompt') }} *
            </label>
            <div class="flex items-center gap-2">
              <input
                v-model="resetPasswordForm.password"
                type="text"
                required
                class="flex-1 h-10 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 font-mono text-xs outline-none focus:border-sky-500"
              />
              <button
                @click="copyPassword"
                type="button"
                class="h-10 px-3 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold flex items-center gap-1.5 transition-colors cursor-pointer"
                :title="passwordCopied ? 'تم النسخ' : 'نسخ كلمة المرور'"
              >
                <Check v-if="passwordCopied" class="w-3.5 h-3.5 text-emerald-500" />
                <Copy v-else class="w-3.5 h-3.5" />
              </button>
            </div>
            <div v-if="resetPasswordForm.errors.password" class="text-rose-500 text-[10px] mt-1">
              {{ resetPasswordForm.errors.password }}
            </div>
          </div>

          <div class="flex items-center justify-between gap-2 pt-1">
            <button
              @click="generatePassword"
              type="button"
              class="text-xs text-accent font-bold hover:underline flex items-center gap-1 cursor-pointer"
            >
              <Sparkles class="w-3.5 h-3.5" />
              <span>{{ t('generateRandomPassword') }}</span>
            </button>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
            <button
              @click="isResetPasswordModalOpen = false"
              type="button"
              class="h-10 px-4 rounded-xl border border-slate-200 dark:border-slate-700 font-semibold text-slate-600 dark:text-slate-300 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
            >
              {{ t('cancel') }}
            </button>
            <button
              :disabled="resetPasswordForm.processing || !resetPasswordForm.password"
              type="submit"
              class="h-10 px-5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold shadow-md shadow-amber-600/20 active:scale-95 disabled:opacity-50 cursor-pointer transition-all flex items-center justify-center gap-1.5"
            >
              <KeyRound class="w-3.5 h-3.5" />
              <span>{{ resetPasswordForm.processing ? t('saving') : t('resetPassword') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Bulk Excel Import Modal -->
    <div v-if="isImportModalOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="isImportModalOpen = false">
      <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 max-w-lg w-full p-6 sm:p-7 shadow-2xl relative" @click.stop>
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-5">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-2xl bg-emerald-50 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
              <FileSpreadsheet class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-900 dark:text-white leading-tight">
                {{ t('importModalTitle') }}
              </h3>
              <p class="text-[11px] text-slate-400 mt-0.5">{{ t('importModalSubtitle') }}</p>
            </div>
          </div>
          <button @click="isImportModalOpen = false" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="space-y-5 text-xs">
          <!-- Step 1: Download Template -->
          <div class="p-4 rounded-2xl bg-accent-light border border-accent/20">
            <div class="flex items-start justify-between gap-3">
              <div>
                <div class="font-bold text-slate-800 dark:text-slate-100 text-xs">{{ t('templateStep') }}</div>
                <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                  {{ t('templateStepDesc') }}
                </p>
              </div>
              <a
                href="/users/template"
                target="_blank"
                class="shrink-0 h-9 inline-flex items-center gap-1.5 px-3 rounded-xl bg-accent bg-accent-hover text-white font-bold text-xs shadow-xs active:scale-95 transition-all"
              >
                <Download class="w-3.5 h-3.5" />
                <span>{{ t('downloadTemplate') }}</span>
              </a>
            </div>
          </div>

          <!-- Step 2: Upload File -->
          <form @submit.prevent="submitImport" class="space-y-4">
            <div>
              <label class="block font-bold text-slate-700 dark:text-slate-300 mb-2">
                {{ t('uploadStep') }}
              </label>

              <!-- Hidden native file input -->
              <input
                ref="importFileRef"
                type="file"
                accept=".xlsx,.xls,.csv"
                class="hidden"
                @change="handleFileChange"
              />

              <!-- Drag & Drop / Click Zone -->
              <div
                @click="triggerFileInput"
                class="border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-emerald-500 dark:hover:border-emerald-500 rounded-2xl p-6 text-center cursor-pointer transition-colors bg-slate-50/50 dark:bg-slate-800/40"
              >
                <div class="w-12 h-12 mx-auto rounded-2xl bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3">
                  <Upload class="w-6 h-6" />
                </div>
                
                <div v-if="selectedFileName" class="space-y-1">
                  <div class="font-bold text-emerald-600 dark:text-emerald-400 text-xs flex items-center justify-center gap-1.5">
                    <CheckCircle2 class="w-4 h-4" />
                    <span>{{ t('fileSelected') }} {{ selectedFileName }}</span>
                  </div>
                  <p class="text-[10px] text-slate-400">انقر لتغيير الملف المرفوع</p>
                </div>
                <div v-else class="space-y-1">
                  <div class="font-bold text-slate-800 dark:text-slate-200 text-xs">
                    {{ t('selectExcelFile') }}
                  </div>
                  <p class="text-[10px] text-slate-400">XLSX, XLS, CSV (حتى 10MB)</p>
                </div>
              </div>

              <div v-if="importForm.errors.file" class="mt-2 text-[11px] text-rose-500 font-semibold flex items-center gap-1">
                <AlertCircle class="w-3.5 h-3.5" />
                <span>{{ importForm.errors.file }}</span>
              </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
              <button
                @click="isImportModalOpen = false"
                type="button"
                class="h-10 px-4 rounded-xl border border-slate-200 dark:border-slate-700 font-semibold text-slate-600 dark:text-slate-300 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
              >
                {{ t('cancel') }}
              </button>
              <button
                :disabled="!importForm.file || importForm.processing"
                type="submit"
                class="h-10 px-5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs shadow-md shadow-emerald-600/20 disabled:opacity-50 cursor-pointer transition-all flex items-center gap-2"
              >
                <FileSpreadsheet class="w-4 h-4" />
                <span>{{ importForm.processing ? t('importing') : t('startImportBtn') }}</span>
              </button>
            </div>
          </form>
        </div>

      </div>
    </div>


    <!-- ========================================================= -->
    <!-- LOCATION & GPS SETTINGS MODAL                             -->
    <!-- ========================================================= -->
    <div
      v-if="isLocationModalOpen"
      class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4"
      @click="isLocationModalOpen = false"
    >
      <div
        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-7 max-w-lg w-full shadow-2xl space-y-5 animate-scale-in"
        @click.stop
      >
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-purple-100 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold shadow-xs">
              <MapPin class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">
                {{ t('locationSettingsTitle') }}
              </h3>
              <p class="text-[11px] text-purple-600 dark:text-purple-400 font-semibold mt-0.5">
                {{ locationTargetUser?.name }} ({{ locationTargetUser?.email }})
              </p>
            </div>
          </div>
          <button @click="isLocationModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
            <X class="w-5 h-5" />
          </button>
        </div>

        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
          {{ t('locationSettingsDesc') }}
        </p>

        <!-- Attendance Mode Selection Cards -->
        <div class="space-y-3">
          <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">{{ t('attendanceMode') }}</label>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- Mode 1: Dynamic GPS -->
            <label
              :class="[
                locationForm.attendance_mode === 'gps'
                  ? 'border-sky-500 bg-sky-50/50 dark:bg-sky-950/40 ring-2 ring-sky-500/20'
                  : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 hover:bg-slate-100 dark:hover:bg-slate-800'
              ]"
              class="flex flex-col justify-between p-3.5 rounded-2xl border cursor-pointer transition-all"
            >
              <div class="flex items-start gap-2.5">
                <input
                  type="radio"
                  v-model="locationForm.attendance_mode"
                  value="gps"
                  class="mt-0.5 text-sky-600 focus:ring-sky-500"
                />
                <div>
                  <div class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                    <Navigation class="w-3.5 h-3.5 text-sky-600 dark:text-sky-400" />
                    <span>{{ t('modeGpsLive') }}</span>
                  </div>
                  <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    {{ t('modeGpsLiveDesc') }}
                  </p>
                </div>
              </div>
            </label>

            <!-- Mode 2: Fixed Location -->
            <label
              :class="[
                locationForm.attendance_mode === 'fixed'
                  ? 'border-purple-500 bg-purple-50/50 dark:bg-purple-950/40 ring-2 ring-purple-500/20'
                  : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 hover:bg-slate-100 dark:hover:bg-slate-800'
              ]"
              class="flex flex-col justify-between p-3.5 rounded-2xl border cursor-pointer transition-all"
            >
              <div class="flex items-start gap-2.5">
                <input
                  type="radio"
                  v-model="locationForm.attendance_mode"
                  value="fixed"
                  class="mt-0.5 text-purple-600 focus:ring-purple-500"
                />
                <div>
                  <div class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                    <Building2 class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400" />
                    <span>{{ t('modeFixedOffice') }}</span>
                  </div>
                  <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    {{ t('modeFixedOfficeDesc') }}
                  </p>
                </div>
              </div>
            </label>
          </div>
        </div>

        <!-- Fixed Location Details (Visible if Mode === 'fixed') -->
        <div v-if="locationForm.attendance_mode === 'fixed'" class="space-y-4 pt-2 border-t border-slate-100 dark:border-slate-800 animate-fade-in">
          <!-- Office Name -->
          <div>
            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
              {{ t('fixedLocationName') }} *
            </label>
            <input
              v-model="locationForm.fixed_location_name"
              type="text"
              placeholder="مثال: المقر الرئيسي / مكتب الإدارة / مبنى العمليات"
              class="w-full h-10 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 outline-none text-slate-900 dark:text-white focus:border-purple-500 font-medium"
            />
          </div>

          <!-- Quick Corporate Presets Chips -->
          <div>
            <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">
              {{ t('quickPresets') }}
            </label>
            <div class="flex flex-wrap gap-1.5">
              <button
                v-for="preset in corporatePresets"
                :key="preset.name"
                @click="applyLocationPreset(preset)"
                type="button"
                class="px-2.5 py-1 rounded-xl text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 hover:bg-purple-100 dark:hover:bg-purple-950/60 text-slate-700 dark:text-slate-300 hover:text-purple-700 dark:hover:text-purple-300 border border-slate-200 dark:border-slate-700 transition-colors cursor-pointer"
              >
                {{ preset.name }}
              </button>
            </div>
          </div>

          <!-- Coordinates Inputs -->
          <!-- Interactive Location Picker Map -->
          <div class="space-y-1.5">
            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">
              تحديد الموقع الدقيق على الخريطة (انقر أو اسحب الدبوس)
            </label>
            <LocationPickerMap
              v-model:lat="locationForm.fixed_latitude"
              v-model:lng="locationForm.fixed_longitude"
              height="220px"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                {{ t('fixedLat') }}
              </label>
              <input
                v-model="locationForm.fixed_latitude"
                type="number"
                step="any"
                class="w-full h-10 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 outline-none font-mono text-slate-900 dark:text-white focus:border-purple-500"
              />
            </div>

            <div>
              <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                {{ t('fixedLng') }}
              </label>
              <input
                v-model="locationForm.fixed_longitude"
                type="number"
                step="any"
                class="w-full h-10 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 outline-none font-mono text-slate-900 dark:text-white focus:border-purple-500"
              />
            </div>
          </div>

          <!-- Capture Current Browser Location Button -->
          <button
            @click="captureCurrentBrowserLocation"
            type="button"
            class="w-full py-2 px-3 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold flex items-center justify-center gap-2 transition-colors cursor-pointer"
          >
            <Crosshair class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400" />
            <span>{{ t('useMyCurrentLocation') }}</span>
          </button>
        </div>

        <!-- Modal Actions -->
        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
          <button
            @click="isLocationModalOpen = false"
            type="button"
            class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition-colors cursor-pointer"
          >
            {{ t('cancel') }}
          </button>
          <button
            @click="submitLocationSettings"
            :disabled="locationForm.processing"
            type="button"
            class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white text-xs font-bold shadow-md shadow-purple-600/20 transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50"
          >
            <CheckCircle2 class="w-4 h-4" />
            <span>{{ t('saveLocationSettings') }}</span>
          </button>
        </div>
      </div>
    </div>

  </AppLayout>
</template>
