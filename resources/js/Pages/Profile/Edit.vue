<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { t } from '@/i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import LanguageToggle from '@/Components/LanguageToggle.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
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
  Briefcase
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
    <div class="w-full max-w-4xl mx-auto space-y-4 sm:space-y-6">
      
      <!-- Top Profile Banner Card -->
      <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-600 via-sky-700 to-teal-700 text-white p-4 sm:p-6 shadow-md shadow-sky-500/15">
        <!-- Ambient Glow Decoration -->
        <div class="absolute -top-12 -left-12 w-48 h-48 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <!-- Avatar + Info -->
          <div class="flex items-center gap-3 sm:gap-4 min-w-0 w-full sm:w-auto">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 flex items-center justify-center text-xl sm:text-2xl font-black shrink-0 shadow-inner">
              {{ user.name?.charAt(0) || 'U' }}
            </div>
            <div class="min-w-0 flex-1">
              <h1 class="text-base sm:text-lg font-black tracking-tight truncate leading-tight">{{ user.name }}</h1>
              
              <!-- Job Title Badge in Hero -->
              <div v-if="user.job_title" class="inline-flex items-center gap-1 text-xs text-sky-100 font-semibold mt-0.5">
                <Briefcase class="w-3.5 h-3.5" />
                <span>{{ user.job_title }}</span>
              </div>
              
              <div class="text-xs text-sky-100/90 font-mono truncate mt-0.5">{{ user.email }}</div>
              
              <div class="flex flex-wrap items-center gap-1.5 mt-2">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-white/25 backdrop-blur-sm border border-white/20">
                  <Shield class="w-3 h-3" />
                  <span>{{ t(user.role + 'Role') }}</span>
                </span>
                <span v-if="user.department" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-white/25 backdrop-blur-sm border border-white/20">
                  <Building2 class="w-3 h-3" />
                  <span>{{ user.department.name }}</span>
                </span>
              </div>
            </div>
          </div>

          <!-- Attendance Info Pill on Mobile & Desktop -->
          <div v-if="todayAttendance" class="w-full sm:w-auto px-3.5 py-2 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 text-xs flex items-center justify-between sm:block">
            <span class="text-[10px] text-sky-100">{{ t('todayAttendanceTime') }}</span>
            <div class="font-bold font-mono">⏰ {{ todayAttendance.log_time }}</div>
          </div>
        </div>
      </div>

      <!-- Main Forms Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        
        <!-- 1. Personal Information Card -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-4 sm:p-6 shadow-xs">
          <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
            <User class="w-4 h-4 text-sky-600 dark:text-sky-400" />
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ t('personalInfo') }}</h2>
          </div>

          <form @submit.prevent="updateProfile" class="space-y-3.5 text-xs">
            <!-- Name -->
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('userName') }} *</label>
              <input
                v-model="profileForm.name"
                type="text"
                required
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 text-xs transition-colors"
              />
              <span v-if="profileForm.errors.name" class="text-rose-500 text-[10px] mt-1">{{ profileForm.errors.name }}</span>
            </div>

            <!-- Job Title -->
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('jobTitle') }}</label>
              <input
                v-model="profileForm.job_title"
                type="text"
                :placeholder="t('jobTitlePlaceholder')"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 text-xs transition-colors"
              />
              <span v-if="profileForm.errors.job_title" class="text-rose-500 text-[10px] mt-1">{{ profileForm.errors.job_title }}</span>
            </div>

            <!-- Email -->
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('email') }} *</label>
              <input
                v-model="profileForm.email"
                type="email"
                required
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 font-mono text-xs transition-colors"
              />
              <span v-if="profileForm.errors.email" class="text-rose-500 text-[10px] mt-1">{{ profileForm.errors.email }}</span>
            </div>

            <div class="pt-2 flex items-center justify-between gap-2">
              <button
                :disabled="profileForm.processing"
                type="submit"
                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-bold shadow-md shadow-sky-500/20 disabled:opacity-50 transition-all cursor-pointer flex items-center justify-center gap-1.5"
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

        <!-- 2. Change Password Card -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-4 sm:p-6 shadow-xs">
          <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
            <KeyRound class="w-4 h-4 text-amber-500" />
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ t('changePassword') }}</h2>
          </div>

          <form @submit.prevent="updatePassword" class="space-y-3.5 text-xs">
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('currentPassword') }}</label>
              <input
                v-model="passwordForm.current_password"
                type="password"
                required
                placeholder="••••••••"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-amber-500 text-xs transition-colors"
              />
              <span v-if="passwordForm.errors.current_password" class="text-rose-500 text-[10px] mt-1">{{ passwordForm.errors.current_password }}</span>
            </div>

            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('newPassword') }}</label>
              <input
                v-model="passwordForm.password"
                type="password"
                required
                placeholder="••••••••"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-amber-500 text-xs transition-colors"
              />
              <span v-if="passwordForm.errors.password" class="text-rose-500 text-[10px] mt-1">{{ passwordForm.errors.password }}</span>
            </div>

            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ t('confirmPassword') }}</label>
              <input
                v-model="passwordForm.password_confirmation"
                type="password"
                required
                placeholder="••••••••"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-amber-500 text-xs transition-colors"
              />
            </div>

            <div class="pt-2 flex items-center justify-between gap-2">
              <button
                :disabled="passwordForm.processing"
                type="submit"
                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-bold shadow-md shadow-amber-500/20 disabled:opacity-50 transition-all cursor-pointer flex items-center justify-center gap-1.5"
              >
                <Lock class="w-3.5 h-3.5" />
                <span>{{ passwordForm.processing ? t('saving') : t('changePassword') }}</span>
              </button>

              <span v-if="passwordSaved" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1 animate-fade-in">
                <CheckCircle2 class="w-4 h-4" />
                <span>{{ t('passwordChangedSuccess') }}</span>
              </span>
            </div>
          </form>
        </div>
      </div>

      <!-- 3. System Preferences & Theme Card -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-4 sm:p-6 shadow-xs">
        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
          <Settings class="w-4 h-4 text-teal-600 dark:text-teal-400" />
          <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ t('systemPreferences') }}</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 text-xs">
          <!-- Theme Preference -->
          <div class="flex items-center justify-between p-3.5 sm:p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
            <div class="min-w-0 flex-1 pl-2">
              <div class="font-bold text-slate-900 dark:text-white mb-0.5">{{ t('themeMode') }}</div>
              <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ t('themeSub') }}</div>
            </div>
            <ThemeToggle />
          </div>

          <!-- Language Preference -->
          <div class="flex items-center justify-between p-3.5 sm:p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
            <div class="min-w-0 flex-1 pl-2">
              <div class="font-bold text-slate-900 dark:text-white mb-0.5">{{ t('languagePref') }}</div>
              <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ t('languageSub') }}</div>
            </div>
            <LanguageToggle />
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>
