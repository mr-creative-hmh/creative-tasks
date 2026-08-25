<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { t } from '@/i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageBanner from '@/Components/PageBanner.vue';
import LanguageToggle from '@/Components/LanguageToggle.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import AccentPicker from '@/Components/AccentPicker.vue';
import {
  User,
  KeyRound,
  Shield,
  Building2,
  CheckCircle2,
  Settings,
  Mail,
  Lock,
  Sparkles,
  Save,
  Briefcase,
  Clock
} from 'lucide-vue-next';

const props = defineProps({
  user: {
    type: Object,
    required: true
  },
  status: {
    type: String,
    default: ''
  }
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user);
const todayAttendance = computed(() => page.props.todayAttendance);

const profileForm = useForm({
  name: props.user.name,
  job_title: props.user.job_title || '',
  email: props.user.email,
});

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const profileSaved = ref(false);
const passwordSaved = ref(false);

function updateProfile() {
  profileForm.patch('/profile', {
    preserveScroll: true,
    onSuccess: () => {
      profileSaved.value = true;
      setTimeout(() => (profileSaved.value = false), 3000);
    }
  });
}

function updatePassword() {
  passwordForm.put('/profile/password', {
    preserveScroll: true,
    onSuccess: () => {
      passwordForm.reset();
      passwordSaved.value = true;
      setTimeout(() => (passwordSaved.value = false), 3000);
    }
  });
}
</script>

<template>
  <Head :title="t('navProfile')" />

  <AppLayout>
    <div class="w-full space-y-6">
      
      <!-- Unified Page Banner -->
      <PageBanner
        :title="user.name"
        :subtitle="user.email + (user.job_title ? ' • ' + user.job_title : '')"
        :badge="t(user.role + 'Role') + (user.department ? ' • ' + user.department.name : '')"
        :icon="User"
      >
        <template #actions>
          <div class="flex flex-wrap items-center gap-2">
            <!-- Attendance Info Pill if present -->
            <div v-if="todayAttendance" class="h-10 px-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/60 text-xs flex items-center gap-2 text-emerald-700 dark:text-emerald-300 font-bold shadow-xs">
              <Clock class="w-4 h-4 text-emerald-500" />
              <span>{{ t('todayAttendanceTime') }}: <strong class="font-mono">{{ todayAttendance.log_time }}</strong></span>
            </div>

            <!-- Quick Controls Bar -->
            <div class="flex items-center gap-1.5 p-1 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs">
              <AccentPicker />
              <ThemeToggle />
              <LanguageToggle />
            </div>
          </div>
        </template>
      </PageBanner>

      <!-- Main Forms Grid (Full-Width Responsive) -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        
        <!-- 1. Personal Information Card -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 sm:p-7 shadow-xs">
          <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="w-8 h-8 rounded-xl bg-accent-light text-accent flex items-center justify-center">
              <User class="w-4 h-4" />
            </div>
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ t('personalInfo') }}</h2>
          </div>

          <form @submit.prevent="updateProfile" class="space-y-4 text-xs">
            <!-- Name -->
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ t('userName') }} *</label>
              <input
                v-model="profileForm.name"
                type="text"
                required
                class="w-full h-10 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 text-xs font-medium transition-colors"
              />
              <span v-if="profileForm.errors.name" class="text-rose-500 text-[10px] mt-1">{{ profileForm.errors.name }}</span>
            </div>

            <!-- Job Title -->
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ t('jobTitle') }}</label>
              <input
                v-model="profileForm.job_title"
                type="text"
                :placeholder="t('jobTitlePlaceholder')"
                class="w-full h-10 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 text-xs font-medium transition-colors"
              />
              <span v-if="profileForm.errors.job_title" class="text-rose-500 text-[10px] mt-1">{{ profileForm.errors.job_title }}</span>
            </div>

            <!-- Email -->
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ t('email') }} *</label>
              <input
                v-model="profileForm.email"
                type="email"
                required
                class="w-full h-10 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 font-mono text-xs transition-colors"
              />
              <span v-if="profileForm.errors.email" class="text-rose-500 text-[10px] mt-1">{{ profileForm.errors.email }}</span>
            </div>

            <div class="pt-2 flex items-center justify-between gap-2">
              <button
                :disabled="profileForm.processing"
                type="submit"
                class="w-full sm:w-auto h-10 px-5 rounded-xl bg-accent bg-accent-hover text-white font-bold shadow-accent active:scale-95 disabled:opacity-50 transition-all cursor-pointer flex items-center justify-center gap-1.5"
              >
                <Save class="w-3.5 h-3.5" />
                <span>{{ profileForm.processing ? t('saving') : t('save') }}</span>
              </button>

              <span v-if="profileSaved" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1 animate-fade-in">
                <CheckCircle2 class="w-4 h-4" />
                <span>{{ t('savedSuccess') }}</span>
              </span>
            </div>
          </form>
        </div>

        <!-- 2. Security & Password Card -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 sm:p-7 shadow-xs">
          <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
              <KeyRound class="w-4 h-4" />
            </div>
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ t('securityPassword') }}</h2>
          </div>

          <form @submit.prevent="updatePassword" class="space-y-4 text-xs">
            <!-- Current Password -->
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ t('currentPassword') }} *</label>
              <input
                v-model="passwordForm.current_password"
                type="password"
                required
                placeholder="••••••••"
                class="w-full h-10 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 text-xs font-medium transition-colors"
              />
              <span v-if="passwordForm.errors.current_password" class="text-rose-500 text-[10px] mt-1">{{ passwordForm.errors.current_password }}</span>
            </div>

            <!-- New Password -->
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ t('newPassword') }} *</label>
              <input
                v-model="passwordForm.password"
                type="password"
                required
                placeholder="••••••••"
                class="w-full h-10 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 text-xs font-medium transition-colors"
              />
              <span v-if="passwordForm.errors.password" class="text-rose-500 text-[10px] mt-1">{{ passwordForm.errors.password }}</span>
            </div>

            <!-- Confirm Password -->
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ t('confirmNewPassword') }} *</label>
              <input
                v-model="passwordForm.password_confirmation"
                type="password"
                required
                placeholder="••••••••"
                class="w-full h-10 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 text-xs font-medium transition-colors"
              />
            </div>

            <div class="pt-2 flex items-center justify-between gap-2">
              <button
                :disabled="passwordForm.processing"
                type="submit"
                class="w-full sm:w-auto h-10 px-5 rounded-xl bg-slate-800 dark:bg-slate-700 hover:bg-slate-700 dark:hover:bg-slate-600 active:scale-95 text-white font-bold shadow-md disabled:opacity-50 transition-all cursor-pointer flex items-center justify-center gap-1.5"
              >
                <Lock class="w-3.5 h-3.5" />
                <span>{{ passwordForm.processing ? t('saving') : t('updatePassword') }}</span>
              </button>

              <span v-if="passwordSaved" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1 animate-fade-in">
                <CheckCircle2 class="w-4 h-4" />
                <span>{{ t('passwordUpdated') }}</span>
              </span>
            </div>
          </form>
        </div>

      </div>

    </div>
  </AppLayout>
</template>
