<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { t } from '@/i18n';
import LanguageToggle from '@/Components/LanguageToggle.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import AccentPicker from '@/Components/AccentPicker.vue';
import GpsLiveIndicator from '@/Components/GpsLiveIndicator.vue';
import { initGlobalGpsTracker } from '@/Services/gpsTracker';
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

  // For Employee
  if (role === 'employee') {
    return [
      {
        name: t('navEmployeePortal'),
        href: '/employee/tasks',
        icon: CheckSquare,
        active: page.url.startsWith('/employee/tasks')
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

  // Admin exclusive navigation
  if (role === 'admin') {
    items.splice(3, 0,
      {
        name: t('navDepartments'),
        href: '/departments',
        icon: Building2,
        active: page.url.startsWith('/departments')
      },
      {
        name: t('navUsers'),
        href: '/users',
        icon: Users,
        active: page.url.startsWith('/users')
      }
    );
  }

  return items;
});

// Native Mobile Bottom Nav Items (Max 4 items for clear mobile tap zones)
const mobileBottomNavItems = computed(() => {
  const role = authUser.value?.role || 'employee';
  if (role === 'employee') {
    return [
      {
        name: t('navEmployeePortal'),
        href: '/employee/tasks',
        icon: CheckSquare,
        active: page.url.startsWith('/employee/tasks')
      },
      {
        name: t('navProfile'),
        href: '/profile',
        icon: User,
        active: page.url.startsWith('/profile')
      }
    ];
  }

  return [
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
});

function logout() {
  router.post('/logout');
}

onMounted(() => {
  // Start silent automatic background GPS tracking across all pages
  if (authUser.value) {
    initGlobalGpsTracker();
  }
});
</script>

<template>
  <div class="min-h-screen bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col md:flex-row transition-colors duration-200">
    
    <!-- ========================================================= -->
    <!-- 1. DESKTOP / LAPTOP / TABLET SIDEBAR (>= md screens)       -->
    <!-- ========================================================= -->
    <aside class="hidden md:flex flex-col w-64 lg:w-72 bg-white dark:bg-slate-900 border-e border-slate-200 dark:border-slate-800 shrink-0 select-none transition-colors duration-200">
      <!-- Logo Header -->
      <div class="p-6 border-b border-slate-100 dark:border-slate-800">
        <Link href="/" class="flex items-center gap-3 group">
          <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-sky-600 to-teal-400 flex items-center justify-center text-white shadow-lg shadow-sky-500/25 group-hover:scale-105 transition-transform duration-200">
            <GraduationCap class="w-6 h-6" />
          </div>
          <div>
            <h1 class="text-sm font-black tracking-tight text-slate-900 dark:text-white leading-tight">
              {{ t('appName') }}
            </h1>
            <p class="text-[11px] text-sky-600 dark:text-sky-400 font-semibold mt-0.5">
              {{ t('appSubtitle') }}
            </p>
          </div>
        </Link>
      </div>

      <!-- Department Shift & Attendance Badge (Live Status Pill) -->
      <div v-if="activeDepartment" class="mx-4 mt-4 p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 text-xs">
        <div class="flex items-center justify-between gap-1 mb-1.5">
          <span class="font-bold text-slate-700 dark:text-slate-200 truncate">{{ activeDepartment?.name }}</span>
          <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-sky-100 dark:bg-sky-950/80 text-sky-700 dark:text-sky-300 font-mono font-semibold">
            {{ activeDepartment?.work_start_time?.substring(0,5) || '08:00' }} - {{ activeDepartment?.work_end_time?.substring(0,5) || '15:30' }}
          </span>
        </div>

        <div class="flex items-center justify-between text-[11px] pt-1.5 border-t border-slate-200/60 dark:border-slate-700/60">
          <div v-if="todayAttendance" class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-bold">
            <CheckCircle2 class="w-3.5 h-3.5" />
            <span>{{ t('present') }} ({{ todayAttendance.log_time }})</span>
          </div>
          <div v-else class="flex items-center gap-1.5 text-amber-600 dark:text-amber-400 font-bold">
            <AlertTriangle class="w-3.5 h-3.5 animate-pulse" />
            <span>{{ t('gpsWaiting') }}</span>
          </div>
          
          <span class="text-[10px] text-slate-400 font-mono">{{ t('today') }}</span>
        </div>
      </div>

      <!-- Navigation Links -->
      <div class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <div class="px-3 pb-2 text-[10px] font-black uppercase tracking-wider text-slate-400">
          {{ t('navDashboard') }}
        </div>

        <Link
          v-for="item in navItems"
          :key="item.href"
          :href="item.href"
          :class="[
            item.active
              ? 'bg-accent text-white shadow-accent font-bold'
              : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-100 font-semibold'
          ]"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all duration-150 group"
        >
          <component
            :is="item.icon"
            :class="item.active ? 'text-white' : 'text-slate-400 group-hover:text-sky-600 dark:group-hover:text-sky-400'"
            class="w-4 h-4 shrink-0 transition-colors"
          />
          <span class="truncate">{{ item.name }}</span>
        </Link>
      </div>

      <!-- Sidebar Footer (Preferences, Profile Card & Logout) -->
      <div class="p-3 border-t border-slate-100 dark:border-slate-800 space-y-2">
        <!-- User Info Strip -->
        <Link
          href="/profile"
          class="flex items-center gap-2.5 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/40 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/60 dark:border-slate-700/60 transition-colors group cursor-pointer"
        >
          <div class="w-8 h-8 rounded-lg bg-accent text-white font-black text-xs flex items-center justify-center shadow-xs shrink-0">
            {{ authUser?.name ? authUser.name.charAt(0) : 'U' }}
          </div>
          <div class="min-w-0 flex-1">
            <div class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">{{ authUser?.name }}</div>
            <div class="text-[10px] text-slate-400 truncate">
              {{ authUser?.job_title || authUser?.department?.name || t(authUser?.role + 'Role') }}
            </div>
          </div>
        </Link>

        <!-- Controls Toolbar (GPS Indicator, Theme & Language) -->
        <div class="flex items-center justify-between gap-1.5 px-1">
          <GpsLiveIndicator />
          <div class="flex items-center gap-1.5">
            <AccentPicker placement="top" />
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
          <GpsLiveIndicator />
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
        class="w-72 bg-white dark:bg-slate-900 h-full p-4 flex flex-col justify-between shadow-2xl transition-transform duration-300 animate-slide-in"
        @click.stop
      >
        <div class="space-y-4">
          <!-- Drawer Header -->
          <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-xl bg-accent text-white flex items-center justify-center font-bold text-xs">
                {{ authUser?.name ? authUser.name.charAt(0) : 'U' }}
              </div>
              <div class="min-w-0">
                <div class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ authUser?.name }}</div>
                <div class="text-[10px] text-slate-400 truncate">{{ authUser?.email }}</div>
              </div>
            </div>
            <button @click="isMobileMenuOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
              <X class="w-4 h-4" />
            </button>
          </div>

          <!-- Drawer Navigation Links -->
          <nav class="space-y-1">
            <Link
              v-for="mItem in navItems"
              :key="mItem.href"
              :href="mItem.href"
              @click="isMobileMenuOpen = false"
              :class="[
                mItem.active
                  ? 'bg-accent text-white shadow-accent font-bold'
                  : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 font-semibold'
              ]"
              class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-colors"
            >
              <component :is="mItem.icon" class="w-4 h-4 shrink-0" />
              <span>{{ mItem.name }}</span>
            </Link>
          </nav>
        </div>

        <!-- Drawer Footer -->
        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2">
          <button
            @click="logout"
            type="button"
            class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-bold text-rose-600 bg-rose-50 dark:bg-rose-950/40"
          >
            <LogOut class="w-4 h-4" />
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
        <component :is="bItem.icon" class="w-5 h-5" />
        <span>{{ bItem.name }}</span>
      </Link>
    </nav>

  </div>
</template>
