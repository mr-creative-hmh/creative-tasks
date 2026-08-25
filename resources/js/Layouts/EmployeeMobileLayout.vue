<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { t } from '@/i18n';
import LanguageToggle from '@/Components/LanguageToggle.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import {
  GraduationCap,
  CheckSquare,
  Clock,
  CheckCircle2,
  AlertTriangle,
  LogOut,
  User,
  ShieldCheck,
  Briefcase
} from 'lucide-vue-next';

const page = usePage();
const authUser = computed(() => page.props.auth?.user);
const todayAttendance = computed(() => page.props.todayAttendance);
const activeDepartment = computed(() => page.props.activeDepartment);

function logout() {
  router.post('/logout');
}
</script>

<template>
  <div class="min-h-screen bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col w-full max-w-lg md:max-w-xl mx-auto relative shadow-2xl border-x border-slate-200/80 dark:border-slate-800 transition-colors duration-200">
    <!-- Top Mobile App Bar -->
    <header class="sticky top-0 z-30 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 px-4 py-3">
      <div class="flex items-center justify-between gap-2">
        <!-- University Logo & Brand -->
        <Link href="/profile" class="flex items-center gap-2.5 min-w-0">
          <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-sky-600 to-teal-400 flex items-center justify-center text-white shadow-md shadow-sky-500/20 shrink-0">
            <GraduationCap class="w-5 h-5" />
          </div>
          <div class="min-w-0">
            <div class="text-xs font-black text-slate-900 dark:text-white leading-none tracking-tight">{{ t('appName') }}</div>
            <div class="text-[10px] text-sky-600 dark:text-sky-400 font-semibold mt-0.5 truncate">
              {{ authUser?.name }}{{ authUser?.job_title ? ` (${authUser.job_title})` : '' }}
            </div>
          </div>
        </Link>

        <!-- Right action icons -->
        <div class="flex items-center gap-1.5 shrink-0">
          <ThemeToggle />
          <LanguageToggle />
          <button
            @click="logout"
            type="button"
            :title="t('logout')"
            class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-slate-600 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors cursor-pointer"
          >
            <LogOut class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- Campus Shift & Attendance Pill Strip -->
      <div class="mt-2.5 flex flex-wrap items-center justify-between gap-1.5 text-[11px] bg-slate-50 dark:bg-slate-950/70 px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800/80">
        <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
          <Clock class="w-3.5 h-3.5 text-sky-600 dark:text-sky-400 shrink-0" />
          <span>{{ t('shiftHours') }}: {{ activeDepartment?.work_start_time?.substring(0,5) || '08:00' }} - {{ activeDepartment?.work_end_time?.substring(0,5) || '15:30' }}</span>
        </div>

        <div v-if="todayAttendance" class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-bold bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-lg border border-emerald-200 dark:border-emerald-800/40">
          <CheckCircle2 class="w-3 h-3" />
          <span>{{ t('present') }} ({{ todayAttendance.log_time }})</span>
        </div>
        <div v-else class="flex items-center gap-1 text-amber-600 dark:text-amber-400 font-bold bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-lg border border-amber-200 dark:border-amber-800/40">
          <AlertTriangle class="w-3 h-3" />
          <span>{{ t('gpsWaiting') }}</span>
        </div>
      </div>
    </header>

    <!-- Scrollable Content Area with Safe Bottom Spacing -->
    <main class="flex-1 p-3 sm:p-4 overflow-y-auto w-full">
      <slot />
      
      <!-- Dedicated Bottom Spacer so fixed nav bar never covers any cards -->
      <div class="h-28 w-full shrink-0" aria-hidden="true"></div>
    </main>

    <!-- Bottom Navigation Bar for PWA with Shadow and Safe Area -->
    <nav class="fixed bottom-0 inset-x-0 max-w-lg md:max-w-xl mx-auto bg-white/95 dark:bg-slate-900/95 backdrop-blur-lg border-t border-slate-200/90 dark:border-slate-800 px-4 pt-2 pb-3 flex items-center justify-around z-30 transition-colors duration-200 shadow-lg shadow-black/15">
      <Link
        href="/employee/tasks"
        :class="page.url.startsWith('/employee') ? 'text-sky-600 dark:text-sky-400 font-bold' : 'text-slate-500 dark:text-slate-400'"
        class="flex flex-col items-center gap-0.5 text-[10px]"
      >
        <div :class="page.url.startsWith('/employee') ? 'bg-sky-500/15 text-sky-600 dark:text-sky-400' : 'text-slate-500'" class="w-9 h-9 rounded-xl flex items-center justify-center transition-all">
          <GraduationCap class="w-4 h-4" />
        </div>
        <span>{{ t('navEmployeePortal') }}</span>
      </Link>

      <Link
        v-if="['admin', 'head'].includes(authUser?.role)"
        href="/dashboard"
        class="flex flex-col items-center gap-0.5 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 text-[10px]"
      >
        <div class="w-9 h-9 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition-colors">
          <ShieldCheck class="w-4 h-4" />
        </div>
        <span>{{ t('navDashboard') }}</span>
      </Link>

      <Link
        href="/profile"
        :class="page.url.startsWith('/profile') ? 'text-sky-600 dark:text-sky-400 font-bold' : 'text-slate-500 dark:text-slate-400'"
        class="flex flex-col items-center gap-0.5 text-[10px]"
      >
        <div :class="page.url.startsWith('/profile') ? 'bg-sky-500/15 text-sky-600 dark:text-sky-400' : 'text-slate-500'" class="w-9 h-9 rounded-xl flex items-center justify-center transition-all">
          <User class="w-4 h-4" />
        </div>
        <span>{{ t('navProfile') }}</span>
      </Link>
    </nav>
  </div>
</template>
