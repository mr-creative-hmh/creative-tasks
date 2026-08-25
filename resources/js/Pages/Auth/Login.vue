<script setup>
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import { t } from '@/i18n';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import AccentPicker from '@/Components/AccentPicker.vue';
import LanguageToggle from '@/Components/LanguageToggle.vue';
import {
  GraduationCap,
  Mail,
  Lock,
  ArrowRight,
  ShieldCheck,
  AlertCircle,
  Sparkles,
  UserCheck
} from 'lucide-vue-next';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

function submit() {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
}

function fillDemo(email, password) {
  form.email = email;
  form.password = password;
}
</script>

<template>
  <Head :title="t('signIn')" />

  <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-slate-50 dark:bg-slate-950 transition-colors duration-300 relative overflow-hidden">
    <!-- Ambient Background Lighting -->
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-sky-500/10 dark:bg-sky-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-teal-500/10 dark:bg-teal-500/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md px-4 relative z-10">
      <!-- Top header & language/theme toggles -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-600 to-teal-500 flex items-center justify-center text-white shadow-lg shadow-sky-500/30">
            <GraduationCap class="w-7 h-7" />
          </div>
          <div>
            <h1 class="text-xl font-black tracking-tight text-slate-900 dark:text-white leading-tight">
              {{ t('appName') }}
            </h1>
            <p class="text-[11px] text-sky-600 dark:text-sky-300 font-medium">Al-Ma'moon University</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <AccentPicker />
          <ThemeToggle />
          <LanguageToggle />
        </div>
      </div>

      <!-- Main Login Card -->
      <div class="bg-white/90 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl relative">
        <div class="mb-6 text-center sm:text-start">
          <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">{{ t('loginTitle') }}</h2>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ t('loginSubtitle') }}</p>
        </div>

        <!-- Global Error Alert -->
        <div v-if="form.errors.email" class="mb-5 p-3.5 bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/60 rounded-2xl flex items-center gap-2.5 text-xs text-rose-600 dark:text-rose-300">
          <AlertCircle class="w-4 h-4 shrink-0 text-rose-500 dark:text-rose-400" />
          <span>{{ form.errors.email }}</span>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ t('email') }}</label>
            <div class="relative">
              <div class="absolute inset-y-0 start-0 ps-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                <Mail class="w-4 h-4" />
              </div>
              <input
                v-model="form.email"
                type="email"
                required
                autocomplete="username"
                placeholder="name@almamonuc.edu.iq"
                class="w-full ps-10 pe-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-all outline-none"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ t('password') }}</label>
            <div class="relative">
              <div class="absolute inset-y-0 start-0 ps-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                <Lock class="w-4 h-4" />
              </div>
              <input
                v-model="form.password"
                type="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
                class="w-full ps-10 pe-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-all outline-none"
              />
            </div>
          </div>

          <div class="flex items-center justify-between text-xs pt-1">
            <label class="flex items-center gap-2 cursor-pointer select-none">
              <input
                v-model="form.remember"
                type="checkbox"
                class="w-4 h-4 rounded bg-slate-100 dark:bg-slate-950 border-slate-300 dark:border-slate-800 text-sky-600 focus:ring-sky-500 cursor-pointer"
              />
              <span class="text-slate-600 dark:text-slate-400 font-medium">{{ t('rememberMe') }}</span>
            </label>
          </div>

          <button
            :disabled="form.processing"
            type="submit"
            class="w-full mt-2 py-3 px-4 rounded-xl bg-gradient-to-r from-sky-600 to-teal-500 hover:from-sky-500 hover:to-teal-400 active:scale-[0.98] text-white font-bold text-sm shadow-lg shadow-sky-500/25 transition-all flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer"
          >
            <span>{{ form.processing ? t('signingIn') : t('signIn') }}</span>
            <ArrowRight class="w-4 h-4 rtl:rotate-180" />
          </button>
        </form>

        <!-- Quick Demo Switcher -->
        <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800/80">
          <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-2.5 text-center flex items-center justify-center gap-1.5">
            <UserCheck class="w-3.5 h-3.5 text-sky-500 dark:text-sky-400" />
            <span>{{ t('demoAccountsTitle') }}</span>
          </div>

          <div class="grid grid-cols-3 gap-2">
            <button
              @click="fillDemo('admin@almamonuc.edu.iq', 'password')"
              type="button"
              class="px-2 py-2 rounded-xl bg-slate-50 dark:bg-slate-950/60 hover:bg-sky-50 dark:hover:bg-sky-950/50 border border-slate-200 dark:border-slate-800 hover:border-sky-300 dark:hover:border-sky-700/60 text-[10px] font-medium text-slate-700 dark:text-slate-300 transition-all text-center leading-tight active:scale-95 cursor-pointer"
            >
              <div class="font-bold text-sky-600 dark:text-sky-400 mb-0.5">{{ t('demoPresidency') }}</div>
              <div class="text-[9px] text-slate-400 dark:text-slate-500">Super Admin</div>
            </button>

            <button
              @click="fillDemo('head.it@almamonuc.edu.iq', 'password')"
              type="button"
              class="px-2 py-2 rounded-xl bg-slate-50 dark:bg-slate-950/60 hover:bg-sky-50 dark:hover:bg-sky-950/50 border border-slate-200 dark:border-slate-800 hover:border-sky-300 dark:hover:border-sky-700/60 text-[10px] font-medium text-slate-700 dark:text-slate-300 transition-all text-center leading-tight active:scale-95 cursor-pointer"
            >
              <div class="font-bold text-teal-600 dark:text-teal-400 mb-0.5">{{ t('demoHead') }}</div>
              <div class="text-[9px] text-slate-400 dark:text-slate-500">Head of IT</div>
            </button>

            <button
              @click="fillDemo('ali.it@almamonuc.edu.iq', 'password')"
              type="button"
              class="px-2 py-2 rounded-xl bg-slate-50 dark:bg-slate-950/60 hover:bg-sky-50 dark:hover:bg-sky-950/50 border border-slate-200 dark:border-slate-800 hover:border-sky-300 dark:hover:border-sky-700/60 text-[10px] font-medium text-slate-700 dark:text-slate-300 transition-all text-center leading-tight active:scale-95 cursor-pointer"
            >
              <div class="font-bold text-amber-600 dark:text-amber-400 mb-0.5">{{ t('demoStaff') }}</div>
              <div class="text-[9px] text-slate-400 dark:text-slate-500">Field Staff</div>
            </button>
          </div>
        </div>
      </div>

      <!-- Footer info -->
            <!-- Footer info with Creator Credit -->
      <div class="text-center mt-8 text-xs text-slate-500 dark:text-slate-400 space-y-1.5">
        <div class="font-bold text-slate-700 dark:text-slate-300">{{ t('appName') }} • {{ t('systemSignature') }}</div>
        <div class="text-[11px] font-bold text-sky-600 dark:text-sky-400">{{ t('creatorCredit') }}</div>
        <div class="text-[10px] text-slate-400 font-mono">{{ t('allRightsReserved', { year: new Date().getFullYear() }) }}</div>
      </div>
    </div>
  </div>
</template>
