<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageBanner from '@/Components/PageBanner.vue';
import AccentPicker from '@/Components/AccentPicker.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import LanguageToggle from '@/Components/LanguageToggle.vue';
import { t } from '@/i18n';
import {
  User,
  Mail,
  Briefcase,
  Building2,
  Shield,
  KeyRound,
  Save,
  CheckCircle2,
  Clock,
  MapPin,
  Sparkles,
  Info,
  Lock,
  Smartphone,
  Calendar
} from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  todayAttendance: Object,
});

const isAdmin = computed(() => props.user?.role === 'admin');

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
  if (!isAdmin.value) return;
  profileForm.patch('/profile', {
    preserveScroll: true,
    onSuccess: () => {
      profileSaved.value = true;
      setTimeout(() => (profileSaved.value = false), 3000);
    },
  });
}

function updatePassword() {
  if (!isAdmin.value) return;
  passwordForm.put('/profile/password', {
    preserveScroll: true,
    onSuccess: () => {
      passwordSaved.value = true;
      passwordForm.reset();
      setTimeout(() => (passwordSaved.value = false), 3000);
    },
  });
}
</script>

<template>
  <AppLayout>
    <Head :title="t('navProfile')" />

    <div class="space-y-6 max-w-7xl mx-auto">
      
      <!-- Profile Executive Banner -->
      <PageBanner
        :title="props.user.name"
        :description="props.user.department?.name ? `${props.user.department.name} • ${props.user.job_title || t(props.user.role + 'Role')}` : (props.user.job_title || t(props.user.role + 'Role'))"
        icon="user"
      >
        <template #badges>
          <div class="flex flex-wrap items-center gap-2">
            <!-- Role Badge -->
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white/20 dark:bg-slate-800/60 backdrop-blur-md text-xs font-bold text-slate-800 dark:text-slate-100 border border-white/20 dark:border-slate-700/40">
              <Shield class="w-3.5 h-3.5 text-accent" />
              <span>{{ t(props.user.role + 'Role') }}</span>
            </span>

            <!-- Department Badge -->
            <span v-if="props.user.department" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white/20 dark:bg-slate-800/60 backdrop-blur-md text-xs font-bold text-slate-800 dark:text-slate-100 border border-white/20 dark:border-slate-700/40">
              <Building2 class="w-3.5 h-3.5 text-sky-500" />
              <span>{{ props.user.department.name }}</span>
            </span>

            <!-- Attendance Status Badge -->
            <span v-if="props.todayAttendance" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 backdrop-blur-md text-xs font-bold border border-emerald-500/30">
              <CheckCircle2 class="w-3.5 h-3.5" />
              <span>{{ t('present') }}: {{ props.todayAttendance.log_time }}</span>
            </span>
          </div>
        </template>

        <template #actions>
          <!-- Quick Appearance Toolbar -->
          <div class="flex items-center gap-2 p-1.5 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <AccentPicker placement="bottom" />
            <ThemeToggle />
            <LanguageToggle />
          </div>
        </template>
      </PageBanner>

      <!-- ========================================================= -->
      <!-- 1. ADMIN VIEW: EDITABLE PERSONAL INFO & CHANGE PASSWORD   -->
      <!-- ========================================================= -->
      <div v-if="isAdmin" class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        
        <!-- Editable Personal Information Card (Admin Only) -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 sm:p-7 shadow-xs">
          <div class="flex items-center justify-between gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-xl bg-accent-light text-accent flex items-center justify-center">
                <User class="w-4 h-4" />
              </div>
              <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ t('personalInfo') }}</h2>
            </div>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800/40">
              {{ t('adminRole') }}
            </span>
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

        <!-- Change Password Card (Admin Only) -->
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
                autocomplete="current-password"
                class="w-full h-10 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 text-xs transition-colors"
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
                autocomplete="new-password"
                class="w-full h-10 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 text-xs transition-colors"
              />
              <span v-if="passwordForm.errors.password" class="text-rose-500 text-[10px] mt-1">{{ passwordForm.errors.password }}</span>
            </div>

            <!-- Confirm New Password -->
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ t('confirmNewPassword') }} *</label>
              <input
                v-model="passwordForm.password_confirmation"
                type="password"
                required
                autocomplete="new-password"
                class="w-full h-10 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 outline-none focus:border-sky-500 text-xs transition-colors"
              />
            </div>

            <div class="pt-2 flex items-center justify-between gap-2">
              <button
                :disabled="passwordForm.processing"
                type="submit"
                class="w-full sm:w-auto h-10 px-5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold shadow-md shadow-amber-600/20 active:scale-95 disabled:opacity-50 transition-all cursor-pointer flex items-center justify-center gap-1.5"
              >
                <KeyRound class="w-3.5 h-3.5" />
                <span>{{ passwordForm.processing ? t('saving') : t('changePassword') }}</span>
              </button>

              <span v-if="passwordSaved" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1 animate-fade-in">
                <CheckCircle2 class="w-4 h-4" />
                <span>{{ t('passwordUpdated') }}</span>
              </span>
            </div>
          </form>
        </div>

      </div>

      <!-- ========================================================= -->
      <!-- 2. NON-ADMIN VIEW (HEAD / EMPLOYEE): READ-ONLY VERIFIED   -->
      <!-- ========================================================= -->
      <div v-else class="space-y-6">
        
        <!-- Official Verified Profile Details Card -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-xs">
          
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-5 mb-6 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-2xl bg-accent text-white font-black text-sm flex items-center justify-center shadow-xs">
                {{ props.user.name ? props.user.name.charAt(0) : 'U' }}
              </div>
              <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ t('officialAccountData') }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ t('contactAdminForChanges') }}</p>
              </div>
            </div>

            <!-- Role Badge -->
            <span class="self-start sm:self-auto inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold border border-slate-200 dark:border-slate-700">
              <Shield class="w-3.5 h-3.5 text-accent" />
              <span>{{ t(props.user.role + 'Role') }}</span>
            </span>
          </div>

          <!-- Read-Only Information Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            
            <!-- Full Name -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-700/60">
              <div class="flex items-center gap-2 text-slate-400 dark:text-slate-500 mb-1">
                <User class="w-3.5 h-3.5" />
                <span class="text-[11px] font-semibold uppercase">{{ t('userName') }}</span>
              </div>
              <div class="text-xs font-bold text-slate-900 dark:text-white">{{ props.user.name }}</div>
            </div>

            <!-- Official Email -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-700/60">
              <div class="flex items-center gap-2 text-slate-400 dark:text-slate-500 mb-1">
                <Mail class="w-3.5 h-3.5" />
                <span class="text-[11px] font-semibold uppercase">{{ t('email') }}</span>
              </div>
              <div class="text-xs font-bold font-mono text-slate-900 dark:text-white truncate">{{ props.user.email }}</div>
            </div>

            <!-- Job / Academic Title -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-700/60">
              <div class="flex items-center gap-2 text-slate-400 dark:text-slate-500 mb-1">
                <Briefcase class="w-3.5 h-3.5" />
                <span class="text-[11px] font-semibold uppercase">{{ t('academicJobTitle') }}</span>
              </div>
              <div class="text-xs font-bold text-slate-900 dark:text-white">{{ props.user.job_title || '—' }}</div>
            </div>

            <!-- Faculty / Department -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-700/60">
              <div class="flex items-center gap-2 text-slate-400 dark:text-slate-500 mb-1">
                <Building2 class="w-3.5 h-3.5" />
                <span class="text-[11px] font-semibold uppercase">{{ t('department') }}</span>
              </div>
              <div class="text-xs font-bold text-slate-900 dark:text-white">{{ props.user.department?.name || '—' }}</div>
            </div>

            <!-- Official Work Shift Hours -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-700/60">
              <div class="flex items-center gap-2 text-slate-400 dark:text-slate-500 mb-1">
                <Clock class="w-3.5 h-3.5" />
                <span class="text-[11px] font-semibold uppercase">{{ t('officialShiftSchedule') }}</span>
              </div>
              <div class="text-xs font-bold font-mono text-slate-900 dark:text-white">
                {{ props.user.department?.work_start_time?.substring(0,5) || '08:00' }} - {{ props.user.department?.work_end_time?.substring(0,5) || '15:30' }}
              </div>
            </div>

            <!-- Account Security Status -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-700/60">
              <div class="flex items-center gap-2 text-slate-400 dark:text-slate-500 mb-1">
                <Lock class="w-3.5 h-3.5" />
                <span class="text-[11px] font-semibold uppercase">{{ t('securityPassword') }}</span>
              </div>
              <div class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>{{ t('activeStatus') }} • {{ t('managedByAdmin') || 'مؤمن ومدار من الإدارة' }}</span>
              </div>
            </div>

          </div>

          <!-- Official Administrative Notice Footer -->
          <div class="mt-6 p-4 rounded-2xl bg-sky-50/80 dark:bg-sky-950/40 border border-sky-200/70 dark:border-sky-900/40 flex items-start gap-3">
            <Info class="w-4 h-4 text-sky-600 dark:text-sky-400 shrink-0 mt-0.5" />
            <p class="text-xs text-sky-900 dark:text-sky-200 leading-relaxed font-medium">
              {{ t('profileReadOnlyNotice') }}
            </p>
          </div>

        </div>

      </div>

    </div>
  </AppLayout>
</template>
