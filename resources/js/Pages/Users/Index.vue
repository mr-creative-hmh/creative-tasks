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
  Briefcase
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
</script>

<template>
  <Head :title="t('navUsers')" />

  <AppLayout>
    <!-- Header with Add User Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
          {{ t('navUsers') }}
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
          {{ t('usersSubtitle') }}
        </p>
      </div>

      <button
        @click="openCreateModal"
        type="button"
        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-bold text-xs shadow-md shadow-sky-600/25 transition-all cursor-pointer"
      >
        <UserPlus class="w-4 h-4" />
        <span>{{ t('addUser') }}</span>
      </button>
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
          <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ t('userRole') }}</label>
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
          <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ t('userDepartment') }}</label>
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
      </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-start text-xs">
          <thead class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
            <tr>
              <th class="px-5 py-4 text-start">{{ t('userName') }}</th>
              <th class="px-5 py-4 text-start">{{ t('jobTitle') }}</th>
              <th class="px-5 py-4 text-start">{{ t('email') }}</th>
              <th class="px-5 py-4 text-center">{{ t('userRole') }}</th>
              <th class="px-5 py-4 text-start">{{ t('userDepartment') }}</th>
              <th class="px-5 py-4 text-center">{{ t('userStatus') }}</th>
              <th class="px-5 py-4 text-center">{{ t('actions') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr 
              v-for="u in users.data" 
              :key="u.id"
              class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors"
            >
              <!-- Name -->
              <td class="px-5 py-4 font-bold text-slate-900 dark:text-white">
                {{ u.name }}
              </td>

              <!-- Job Title -->
              <td class="px-5 py-4 text-slate-600 dark:text-slate-300 font-medium">
                <span v-if="u.job_title" class="inline-flex items-center gap-1 text-sky-700 dark:text-sky-300 font-semibold bg-sky-50 dark:bg-sky-950/60 px-2 py-0.5 rounded-md text-[11px]">
                  <Briefcase class="w-3 h-3 text-sky-600" />
                  <span>{{ u.job_title }}</span>
                </span>
                <span v-else class="text-slate-400">-</span>
              </td>

              <!-- Email -->
              <td class="px-5 py-4 text-slate-500 font-mono text-[11px]">
                {{ u.email }}
              </td>

              <!-- Role Badge -->
              <td class="px-5 py-4 text-center">
                <span 
                  :class="{
                    'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200': u.role === 'admin',
                    'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border-sky-200': u.role === 'head',
                    'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200': u.role === 'employee',
                  }"
                  class="whitespace-nowrap inline-flex items-center justify-center text-[10px] font-bold px-2.5 py-1 rounded-full border shrink-0"
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
  </AppLayout>
</template>
