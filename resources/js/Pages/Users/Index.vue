<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { t } from '@/i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
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
  AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
  users: {
    type: Object,
    default: () => ({ data: [] })
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
});

// Bulk Excel Import State
const isImportModalOpen = ref(false);
const importFileRef = ref(null);
const selectedFileName = ref('');

const importForm = useForm({
  file: null,
});

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
  isModalOpen.value = true;
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
    <!-- Header with Action Buttons -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
          {{ t('navUsers') }}
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
          {{ t('usersSubtitle') }}
        </p>
      </div>

      <!-- Actions Strip -->
      <div class="flex flex-wrap items-center gap-2">
        <!-- Download Template Button -->
        <a
          href="/users/template"
          target="_blank"
          class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/80 active:scale-95 text-slate-700 dark:text-slate-200 font-bold text-xs shadow-xs transition-all cursor-pointer"
          :title="t('downloadTemplate')"
        >
          <Download class="w-4 h-4 text-sky-600 dark:text-sky-400" />
          <span>{{ t('downloadTemplate') }}</span>
        </a>

        <!-- Import Excel Button -->
        <button
          @click="isImportModalOpen = true"
          type="button"
          class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all cursor-pointer"
        >
          <FileSpreadsheet class="w-4 h-4" />
          <span>{{ t('importExcel') }}</span>
        </button>

        <!-- Add Single User Button -->
        <button
          @click="openCreateModal"
          type="button"
          class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-bold text-xs shadow-md shadow-sky-600/25 transition-all cursor-pointer"
        >
          <UserPlus class="w-4 h-4" />
          <span>{{ t('addUser') }}</span>
        </button>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs mb-6">
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
              class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl ps-8 pe-3 py-2 outline-none text-slate-800 dark:text-slate-100"
            />
            <Search class="w-3.5 h-3.5 text-slate-400 absolute start-2.5 top-2.5" />
          </div>
        </div>

        <!-- Role Filter -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ t('role') }}</label>
          <select
            v-model="filterForm.role"
            @change="applyFilters"
            class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100"
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
            class="w-full text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 outline-none text-slate-800 dark:text-slate-100"
          >
            <option value="">{{ t('allDepartments') }}</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
        </div>

        <!-- Stats Counter -->
        <div class="flex items-end justify-between bg-slate-50 dark:bg-slate-800/60 p-2.5 rounded-2xl border border-slate-100 dark:border-slate-800">
          <div>
            <div class="text-[10px] text-slate-400 font-semibold">{{ t('totalUsers') }}</div>
            <div class="text-lg font-black text-slate-800 dark:text-slate-200 font-mono">{{ users.total || users.data.length }}</div>
          </div>
          <div class="text-end">
            <div class="text-[10px] text-emerald-500 font-semibold">{{ t('activeUsers') }}</div>
            <div class="text-lg font-black text-emerald-600 dark:text-emerald-400 font-mono">
              {{ users.data.filter(u => u.is_active).length }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-start text-xs">
          <thead class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
            <tr>
              <th class="px-5 py-4 text-start">{{ t('userName') }}</th>
              <th class="px-5 py-4 text-start">{{ t('email') }}</th>
              <th class="px-5 py-4 text-start">{{ t('role') }}</th>
              <th class="px-5 py-4 text-start">{{ t('department') }}</th>
              <th class="px-5 py-4 text-center">{{ t('status') }}</th>
              <th class="px-5 py-4 text-center">{{ t('actions') }}</th>
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
                <div v-if="u.job_title" class="text-[10px] text-sky-600 dark:text-sky-400 font-semibold mt-0.5">
                  {{ u.job_title }}
                </div>
              </td>

              <!-- Email -->
              <td class="px-5 py-4 text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                {{ u.email }}
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

              <!-- Department -->
              <td class="px-5 py-4 text-slate-600 dark:text-slate-400 font-medium">
                {{ u.department?.name || '-' }}
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

              <!-- Actions -->
              <td class="px-5 py-4 text-center">
                <div class="inline-flex items-center gap-1">
                  <button
                    @click="openEditModal(u)"
                    type="button"
                    class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 cursor-pointer"
                    :title="t('edit')"
                  >
                    <Edit2 class="w-4 h-4" />
                  </button>
                  <button
                    @click="deleteUser(u)"
                    type="button"
                    class="p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 cursor-pointer"
                    :title="t('delete')"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create / Edit User Modal -->
    <div v-if="isModalOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="isModalOpen = false">
      <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 max-w-md w-full p-6 shadow-2xl relative" @click.stop>
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
          <h3 class="text-sm font-bold text-slate-900 dark:text-white">
            {{ editingUser ? t('edit') : t('addUser') }}
          </h3>
          <button @click="isModalOpen = false" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitUser" class="space-y-3.5 text-xs">
          <!-- Name -->
          <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('userName') }} *</label>
            <input
              v-model="userForm.name"
              type="text"
              required
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500"
            />
          </div>

          <!-- Job Title -->
          <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('jobTitle') }}</label>
            <input
              v-model="userForm.job_title"
              type="text"
              :placeholder="t('jobTitlePlaceholder')"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500"
            />
          </div>

          <!-- Email -->
          <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('email') }} *</label>
            <input
              v-model="userForm.email"
              type="email"
              required
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 font-mono"
            />
          </div>

          <!-- Password -->
          <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
              {{ t('password') }} {{ editingUser ? `(${t('optionalPasswordHint')})` : '*' }}
            </label>
            <input
              v-model="userForm.password"
              type="password"
              :required="!editingUser"
              placeholder="••••••••"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500"
            />
          </div>

          <!-- Role -->
          <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('userRole') }} *</label>
            <select
              v-model="userForm.role"
              required
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none"
            >
              <option value="employee">{{ t('employeeRole') }}</option>
              <option value="head">{{ t('headRole') }}</option>
              <option value="admin">{{ t('adminRole') }}</option>
            </select>
          </div>

          <!-- Department -->
          <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('userDepartment') }}</label>
            <select
              v-model="userForm.department_id"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none"
            >
              <option value="">{{ t('selectDept') }}</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                {{ dept.name }}
              </option>
            </select>
          </div>

          <!-- Active Toggle -->
          <div class="flex items-center gap-2 pt-1">
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

          <!-- Actions -->
          <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
            <button
              @click="isModalOpen = false"
              type="button"
              class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 font-semibold text-slate-600 dark:text-slate-300 cursor-pointer"
            >
              {{ t('cancel') }}
            </button>
            <button
              :disabled="userForm.processing"
              type="submit"
              class="px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-bold shadow-sm disabled:opacity-50 cursor-pointer transition-all"
            >
              {{ userForm.processing ? t('saving') : t('save') }}
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
          <div class="p-4 rounded-2xl bg-sky-50/70 dark:bg-sky-950/40 border border-sky-200/70 dark:border-sky-800/50">
            <div class="flex items-start justify-between gap-3">
              <div>
                <div class="font-bold text-sky-900 dark:text-sky-200 text-xs">{{ t('templateStep') }}</div>
                <p class="text-[11px] text-sky-700/80 dark:text-sky-300/80 mt-1 leading-relaxed">
                  {{ t('templateStepDesc') }}
                </p>
              </div>
              <a
                href="/users/template"
                target="_blank"
                class="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-xs active:scale-95 transition-all"
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
                class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 font-semibold text-slate-600 dark:text-slate-300 cursor-pointer"
              >
                {{ t('cancel') }}
              </button>
              <button
                :disabled="!importForm.file || importForm.processing"
                type="submit"
                class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs shadow-md shadow-emerald-600/20 disabled:opacity-50 cursor-pointer transition-all flex items-center gap-2"
              >
                <FileSpreadsheet class="w-4 h-4" />
                <span>{{ importForm.processing ? t('importing') : t('startImportBtn') }}</span>
              </button>
            </div>
          </form>
        </div>

      </div>
    </div>

  </AppLayout>
</template>
