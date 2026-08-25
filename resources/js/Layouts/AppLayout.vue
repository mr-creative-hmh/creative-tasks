<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { t } from '@/i18n';
import LanguageToggle from '@/Components/LanguageToggle.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import AccentPicker from '@/Components/AccentPicker.vue';
import {
  LayoutDashboard,
  CheckSquare,
  MapPin,
  Building2,
  Users,
  FileBarChart,
  User,
  LogOut,
  Menu,
  X,
  Smartphone,
  GraduationCap,
  Briefcase,
  Clock,
  CheckCircle2,
  AlertTriangle,
  Layers,
  Settings
} from 'lucide-vue-next';

const isMobileMenuOpen = ref(false);
const page = usePage();
const authUser = computed(() => page.props.auth?.user);
const activeDepartment = computed(() => page.props.activeDepartment || authUser.value?.department);
const todayAttendance = computed(() => page.props.todayAttendance);

const navItems = computed(() => {
  const role = authUser.value?.role || 'employee';

  const items = [
    {
      name: t('navDashboard'),
      href: '/dashboard',
      icon: LayoutDashboard,
      roles: ['admin', 'head'],
      active: page.url === '/dashboard' || page.url === '/'
    },
    {
      name: t('navTasks'),
      href: '/tasks',
      icon: CheckSquare,
      roles: ['admin', 'head'],
      active: page.url.startsWith('/tasks')
    },
    {
      name: t('navAttendance'),
      href: '/attendance',
      icon: MapPin,
      roles: ['admin', 'head'],
      active: page.url.startsWith('/attendance')
    },
    {
      name: t('navDepartments'),
      href: '/departments',
      icon: Building2,
      roles: ['admin'],
      active: page.url.startsWith('/departments')
    },
    {
      name: t('navUsers'),
      href: '/users',
      icon: Users,
      roles: ['admin'],
      active: page.url.startsWith('/users')
    },
    {
      name: t('navReports'),
      href: '/reports',
      icon: FileBarChart,
      roles: ['admin', 'head'],
      active: page.url.startsWith('/reports')
    },
    {
      name: t('navEmployeePortal'),
      href: '/employee/tasks',
      icon: Smartphone,
      roles: ['admin', 'head', 'employee'],
      active: page.url.startsWith('/employee')
    },
    {
      name: t('navProfile'),
      href: '/profile',
      icon: User,
      roles: ['admin', 'head', 'employee'],
      active: page.url.startsWith('/profile')
    }
  ];

  return items.filter(item => item.roles.includes(role));
});

// Primary Bottom Navigation Items for Mobile Screen (< md)
const mobileBottomNavItems = computed(() => {
  const role = authUser.value?.role || 'employee';

  if (role === 'employee') {
    return [
      {
        name: t('navEmployeePortal'),
        href: '/employee/tasks',
        icon: GraduationCap,
        active: page.url.startsWith('/employee')
      },
      {
        name: t('navProfile'),
        href: '/profile',
        icon: User,
        active: page.url.startsWith('/profile')
      }
    ];
  }

  // For Head & Admin
  const items = [
    {
      name: t('navDashboard'),
      href: '/dashboard',
      icon: LayoutDashboard,
      active: page.url === '/dashboard' || page.url === '/'
    },
    {
      name: t('navTasks'),
      href: '/tasks',
      icon: CheckSquare,
      active: page.url.startsWith('/tasks')
    },
    {
      name: t('navAttendance'),
      href: '/attendance',
      icon: MapPin,
      active: page.url.startsWith('/attendance')
    },
    {
      name: t('navReports'),
      href: '/reports',
      icon: FileBarChart,
      active: page.url.startsWith('/reports')
    },
    {
      name: t('navProfile'),
      href: '/profile',
      icon: User,
      active: page.url.startsWith('/profile')
    }
  ];

  return items;
});

function logout() {
  router.post('/logout');
}
</script>

<template>
  <div class="min-h-screen bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col md:flex-row transition-colors duration-200">
    
    <!-- ========================================================= -->
    <!-- 1. DESKTOP / LAPTOP / TABLET SIDEBAR (>= md screens)       -->
    <!-- ========================================================= -->
    <aside class="hidden md:flex flex-col w-64 lg:w-72 bg-white dark:bg-slate-900 border-e border-slate-200 dark:border-slate-800 shrink-0 select-none transition-colors duration-200">
      <!-- Logo Header -->
      <div class="h-16 flex items-center justify-between px-5 border-b border-slate-100 dark:border-slate-800">
        <Link href="/" class="flex items-center gap-2.5 group">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-600 to-teal-500 flex items-center justify-center text-white shadow-md shadow-sky-500/20 group-hover:scale-105 transition-transform">
            <GraduationCap class="w-6 h-6" />
          </div>
          <div>
            <div class="font-black text-sm text-slate-900 dark:text-white leading-tight tracking-tight">{{ t('appName') }}</div>
            <div class="text-[10px] text-sky-600 dark:text-sky-400 font-semibold leading-tight">{{ t('appSubtitle') }}</div>
          </div>
        </Link>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 px-3 py-4 space-y-1.5 overflow-y-auto">
        <Link
          v-for="item in navItems"
          :key="item.name"
          :href="item.href"
          :class="[
            item.active 
              ? 'bg-sky-500/10 text-sky-700 dark:text-sky-300 font-bold border border-sky-500/20 shadow-xs' 
              : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 font-medium'
          ]"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all group"
        >
          <component :is="item.icon" class="w-4 h-4 text-sky-600 dark:text-sky-400 transition-transform group-hover:scale-110" />
          <span>{{ item.name }}</span>
        </Link>
      </nav>

      <!-- Shift Info Badge on Desktop -->
      <div v-if="activeDepartment" class="mx-3 mb-2 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/60 text-[11px]">
        <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300 font-semibold mb-1">
          <Clock class="w-3.5 h-3.5 text-sky-600 dark:text-sky-400" />
          <span>{{ t('shiftHours') }}</span>
        </div>
        <div class="font-mono text-slate-500 dark:text-slate-400 text-[10px]">
          {{ activeDepartment.work_start_time?.substring(0,5) || '08:00' }} - {{ activeDepartment.work_end_time?.substring(0,5) || '15:30' }}
        </div>
      </div>

      <!-- User Profile & Footer Actions -->
      <div class="p-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 space-y-2">
        <!-- Profile Link Card -->
        <Link
          href="/profile"
          class="flex items-center gap-2.5 p-2 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/60 hover:border-sky-500/50 transition-all cursor-pointer group"
        >
          <div class="w-8 h-8 rounded-lg bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-300 flex items-center justify-center font-bold text-xs shrink-0 group-hover:scale-105 transition-transform">
            {{ authUser?.name?.charAt(0) || 'U' }}
          </div>
          <div class="min-w-0 flex-1">
            <div class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">{{ authUser?.name }}</div>
            <div class="text-[10px] text-slate-400 truncate">
              {{ authUser?.job_title || authUser?.department?.name || t(authUser?.role + 'Role') }}
            </div>
          </div>
        </Link>

        <!-- Controls Toolbar (Theme & Language) -->
        <div class="flex items-center justify-between gap-2 px-1">
          <span class="text-[10px] font-semibold text-slate-400">{{ t('preferences') }}</span>
          <div class="flex items-center gap-1.5">
            <AccentPicker />
            <ThemeToggle />
            <LanguageToggle />
          </div>
        </div>

        <!-- Logout Button -->
        <button
          @click="logout"
          type="button"
          class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
        >
          <LogOut class="w-3.5 h-3.5" />
          <span>{{ t('logout') }}</span>
        </button>
      </div>
    </aside>

    <!-- ========================================================= -->
    <!-- 2. MOBILE APP TOP HEADER (< md screens)                   -->
    <!-- ========================================================= -->
    <header class="md:hidden sticky top-0 z-30 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 px-4 py-3 transition-colors duration-200">
      <div class="flex items-center justify-between gap-2">
        <!-- University Logo & Brand -->
        <Link href="/" class="flex items-center gap-2.5 min-w-0">
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

        <!-- Right Controls & Hamburger Drawer Trigger -->
        <div class="flex items-center gap-1.5 shrink-0">
          <AccentPicker />
          <ThemeToggle />
          <LanguageToggle />
          <button
            @click="isMobileMenuOpen = !isMobileMenuOpen"
            type="button"
            class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer"
          >
            <Menu v-if="!isMobileMenuOpen" class="w-4 h-4" />
            <X v-else class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- Campus Shift & Attendance Pill Strip on Mobile -->
      <div v-if="activeDepartment" class="mt-2.5 flex flex-wrap items-center justify-between gap-1.5 text-[11px] bg-slate-50 dark:bg-slate-950/70 px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800/80">
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

    <!-- Mobile Slide Drawer Menu (< md screens) -->
    <div
      v-if="isMobileMenuOpen"
      class="md:hidden fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs flex"
      @click="isMobileMenuOpen = false"
    >
      <div
        class="w-72 bg-white dark:bg-slate-900 h-full flex flex-col p-4 shadow-2xl border-e border-slate-200 dark:border-slate-800 animate-slide-in"
        @click.stop
      >
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-sky-600 flex items-center justify-center text-white font-bold">
              <GraduationCap class="w-5 h-5" />
            </div>
            <span class="font-bold text-xs">{{ t('appName') }}</span>
          </div>
          <button @click="isMobileMenuOpen = false" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400">
            <X class="w-4 h-4" />
          </button>
        </div>

        <nav class="flex-1 space-y-1.5 overflow-y-auto">
          <Link
            v-for="item in navItems"
            :key="item.name"
            :href="item.href"
            @click="isMobileMenuOpen = false"
            :class="[
              item.active 
                ? 'bg-sky-500/10 text-sky-700 dark:text-sky-300 font-bold border border-sky-500/20' 
                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60'
            ]"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs"
          >
            <component :is="item.icon" class="w-4 h-4 text-sky-600 dark:text-sky-400" />
            <span>{{ item.name }}</span>
          </Link>
        </nav>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-3">
          <Link
            href="/profile"
            @click="isMobileMenuOpen = false"
            class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/60"
          >
            <div class="w-7 h-7 rounded-lg bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-300 flex items-center justify-center font-bold text-xs">
              {{ authUser?.name?.charAt(0) || 'U' }}
            </div>
            <div class="min-w-0 flex-1">
              <div class="text-xs font-bold truncate">{{ authUser?.name }}</div>
              <div class="text-[10px] text-slate-400 truncate">{{ authUser?.job_title || authUser?.department?.name }}</div>
            </div>
          </Link>

          <button
            @click="logout"
            type="button"
            class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-rose-200/40 dark:border-rose-900/40"
          >
            <LogOut class="w-3.5 h-3.5" />
            <span>{{ t('logout') }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ========================================================= -->
    <!-- 3. MAIN CONTENT AREA (Responsive Container)               -->
    <!-- ========================================================= -->
    <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto w-full">
      <!-- Flash Alert Feedback Messages -->
      <div v-if="page.props.flash?.success" class="mb-4 sm:mb-5 p-3.5 sm:p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-bold flex items-center gap-2 animate-fade-in shadow-xs">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        <span>{{ page.props.flash.success }}</span>
      </div>

      <div v-if="page.props.flash?.error" class="mb-4 sm:mb-5 p-3.5 sm:p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-bold flex items-center gap-2 animate-fade-in shadow-xs">
        <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
        <span>{{ page.props.flash.error }}</span>
      </div>

      <slot />

      <!-- Universal Designer / Creator Credit Footer -->
      <footer class="mt-12 pt-6 pb-4 border-t border-slate-200/80 dark:border-slate-800/80 text-center text-xs text-slate-500 dark:text-slate-400">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-2 w-full px-2">
          <div class="flex items-center gap-2 font-bold text-slate-700 dark:text-slate-300">
            <GraduationCap class="w-4 h-4 text-sky-600 dark:text-sky-400" />
            <span>{{ t('appName') }}</span>
            <span class="text-slate-300 dark:text-slate-700">•</span>
            <span class="text-[11px] font-normal text-slate-500 dark:text-slate-400">{{ t('systemSignature') }}</span>
          </div>

          <div class="text-[11px] flex items-center gap-1.5 font-medium">
            <span class="text-sky-600 dark:text-sky-400 font-bold">{{ t('creatorCredit') }}</span>
            <span class="text-slate-300 dark:text-slate-700">•</span>
            <span class="font-mono text-slate-400 dark:text-slate-500">{{ t('allRightsReserved', { year: new Date().getFullYear() }) }}</span>
          </div>
        </div>
      </footer>

      <!-- Bottom Spacer for Mobile so content is never blocked by Bottom Bar -->
      <div class="h-24 md:hidden w-full shrink-0" aria-hidden="true"></div>
    </main>

    <!-- ========================================================= -->
    <!-- 4. NATIVE MOBILE APP BOTTOM NAVIGATION BAR (< md screens)  -->
    <!-- ========================================================= -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur-lg border-t border-slate-200/90 dark:border-slate-800 px-3 pt-2 pb-2.5 flex items-center justify-around z-30 transition-colors duration-200 shadow-lg shadow-black/15">
      <Link
        v-for="bItem in mobileBottomNavItems"
        :key="bItem.href"
        :href="bItem.href"
        :class="bItem.active ? 'text-sky-600 dark:text-sky-400 font-bold' : 'text-slate-500 dark:text-slate-400'"
        class="flex flex-col items-center gap-0.5 text-[10px] transition-colors"
      >
        <div :class="bItem.active ? 'bg-sky-500/15 text-sky-600 dark:text-sky-400' : 'text-slate-500'" class="w-9 h-9 rounded-xl flex items-center justify-center transition-all">
          <component :is="bItem.icon" class="w-4 h-4" />
        </div>
        <span class="truncate max-w-[70px]">{{ bItem.name }}</span>
      </Link>
    </nav>

  </div>
</template>
