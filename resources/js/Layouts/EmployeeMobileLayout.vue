<script setup>
import { computed } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import { t, currentLocale } from '@/i18n';
import LanguageToggle from '@/Components/LanguageToggle.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import {
  GraduationCap,
  LogOut,
  Clock,
  ShieldCheck,
  CheckCircle2,
  AlertTriangle,
  User
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
  <div class="min-h-screen bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col max-w-md mx-auto relative shadow-2xl border-x border-slate-200 dark:border-slate-800 transition-colors duration-200">
    <!-- Top Mobile App Bar -->
    <header class="sticky top-0 z-30 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200 dark:border-slate-800/80 px-4 py-3">
      <div class="flex items-center justify-between">
        <!-- University Logo & Brand -->
        <Link href="/profile" class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-sky-600 to-teal-400 flex items-center justify-center text-white shadow-md shadow-sky-500/20">
            <GraduationCap class="w-5 h-5" />
          </div>
          <div>
            <div class="text-xs font-black text-slate-900 dark:text-white leading-none tracking-tight">جامعة المأمون</div>
            <div class="text-[10px] text-sky-600 dark:text-sky-400 font-semibold mt-0.5">{{ authUser?.name }}</div>
          </div>
        </Link>

        <!-- Right action icons -->
        <div class="flex items-center gap-2">
          <ThemeToggle />
          <LanguageToggle />
          <button
            @click="logout"
            type="button"
            title="تسجيل الخروج"
            class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-slate-600 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors"
          >
            <LogOut class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- Campus Shift & Attendance Pill Strip -->
      <div class="mt-2.5 flex items-center justify-between gap-2 text-[10px] bg-slate-50 dark:bg-slate-950/70 p-2 rounded-xl border border-slate-200 dark:border-slate-800/80">
        <div class="flex items-center gap-1.5 min-w-0 text-slate-600 dark:text-slate-400">
          <Clock class="w-3.5 h-3.5 text-sky-600 dark:text-sky-400 shrink-0" />
          <span class="truncate">الدوام: {{ activeDepartment?.work_start_time?.substring(0,5) || '08:00' }} - {{ activeDepartment?.work_end_time?.substring(0,5) || '15:30' }}</span>
        </div>

        <div v-if="todayAttendance" class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold shrink-0 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-lg border border-emerald-200 dark:border-emerald-800/40">
          <CheckCircle2 class="w-3 h-3" />
          <span>تم الحضور ({{ todayAttendance.log_time }})</span>
        </div>
        <div v-else class="flex items-center gap-1 text-amber-600 dark:text-amber-400 font-semibold shrink-0 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-lg border border-amber-200 dark:border-amber-800/40">
          <AlertTriangle class="w-3 h-3" />
          <span>بانتظار الـ GPS</span>
        </div>
      </div>
    </header>

    <!-- Scrollable Content Area -->
    <main class="flex-1 p-4 pb-20 overflow-y-auto">
      <slot />
    </main>

    <!-- Bottom Navigation Bar for PWA -->
    <nav class="fixed bottom-0 inset-x-0 max-w-md mx-auto bg-white/95 dark:bg-slate-900/95 backdrop-blur-lg border-t border-slate-200 dark:border-slate-800 px-6 py-2 flex items-center justify-around z-30 transition-colors duration-200">
      <Link
        href="/employee/tasks"
        :class="page.url.startsWith('/employee') ? 'text-sky-600 dark:text-sky-400 font-bold' : 'text-slate-500 dark:text-slate-400'"
        class="flex flex-col items-center gap-1 text-[10px]"
      >
        <div class="w-8 h-8 rounded-xl bg-sky-500/15 flex items-center justify-center">
          <GraduationCap class="w-4 h-4 text-sky-600 dark:text-sky-400" />
        </div>
        <span>مهامي الجامعية</span>
      </Link>

      <Link
        v-if="['admin', 'head'].includes(authUser?.role)"
        href="/dashboard"
        class="flex flex-col items-center gap-1 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 text-[10px]"
      >
        <div class="w-8 h-8 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition-colors">
          <ShieldCheck class="w-4 h-4" />
        </div>
        <span>لوحة الإدارة</span>
      </Link>

      <Link
        href="/profile"
        :class="page.url.startsWith('/profile') ? 'text-sky-600 dark:text-sky-400 font-bold' : 'text-slate-500 dark:text-slate-400'"
        class="flex flex-col items-center gap-1 text-[10px]"
      >
        <div class="w-8 h-8 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition-colors">
          <User class="w-4 h-4" />
        </div>
        <span>حسابي</span>
      </Link>
    </nav>
  </div>
</template>
