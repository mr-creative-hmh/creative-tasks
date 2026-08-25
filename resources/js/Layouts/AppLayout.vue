<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { t, currentLocale } from '@/i18n';
import LanguageToggle from '@/Components/LanguageToggle.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
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
  GraduationCap,
  Smartphone,
  ShieldCheck
} from 'lucide-vue-next';

defineProps({
  title: {
    type: String,
    default: ''
  }
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user);
const isMobileMenuOpen = ref(false);

const navigation = computed(() => {
  const role = authUser.value?.role;
  const items = [
    {
      name: t('navDashboard'),
      href: '/dashboard',
      icon: LayoutDashboard,
      roles: ['admin', 'head'],
      active: page.url.startsWith('/dashboard')
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

function logout() {
  router.post('/logout');
}
</script>

<template>
  <div class="min-h-screen bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col md:flex-row transition-colors duration-200">
    <!-- Desktop Sidebar -->
    <aside class="hidden md:flex flex-col w-64 bg-white dark:bg-slate-900 border-e border-slate-200 dark:border-slate-800 shrink-0 select-none transition-colors duration-200">
      <!-- Logo Header -->
      <div class="h-16 flex items-center justify-between px-5 border-b border-slate-100 dark:border-slate-800">
        <Link href="/" class="flex items-center gap-2.5 group">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-600 to-teal-500 flex items-center justify-center text-white shadow-md shadow-sky-500/20 group-hover:scale-105 transition-transform">
            <GraduationCap class="w-6 h-6" />
          </div>
          <div>
            <div class="font-black text-sm tracking-tight text-slate-900 dark:text-white leading-none">
              جامعة المأمون
            </div>
            <div class="text-[10px] text-sky-600 dark:text-sky-400 font-semibold mt-0.5">
              Al-Ma'moon University
            </div>
          </div>
        </Link>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 px-3 py-4 space-y-1.5 overflow-y-auto">
        <Link
          v-for="item in navigation"
          :key="item.name"
          :href="item.href"
          :class="[
            item.active 
              ? 'bg-sky-500/10 text-sky-700 dark:text-sky-300 font-bold border border-sky-500/20' 
              : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 font-medium'
          ]"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all group"
        >
          <component :is="item.icon" class="w-4 h-4 text-sky-600 dark:text-sky-400 transition-transform group-hover:scale-110" />
          <span>{{ item.name }}</span>
        </Link>
      </nav>

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
            <div class="text-[10px] text-slate-400 truncate">{{ authUser?.department_name || t(authUser?.role + 'Role') }}</div>
          </div>
        </Link>

        <!-- Controls Toolbar (Theme & Language) -->
        <div class="flex items-center justify-between gap-2 px-1">
          <span class="text-[10px] font-semibold text-slate-400">التفضيلات</span>
          <div class="flex items-center gap-1.5">
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

    <!-- Mobile Header -->
    <header class="md:hidden flex items-center justify-between h-16 px-4 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-30 transition-colors duration-200">
      <Link href="/" class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-sky-600 to-teal-500 flex items-center justify-center text-white font-bold">
          <GraduationCap class="w-5 h-5" />
        </div>
        <span class="font-extrabold text-sm tracking-tight text-slate-900 dark:text-white">
          جامعة المأمون
        </span>
      </Link>

      <div class="flex items-center gap-2">
        <ThemeToggle />
        <LanguageToggle />
        <button
          @click="isMobileMenuOpen = !isMobileMenuOpen"
          type="button"
          class="p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300"
        >
          <Menu v-if="!isMobileMenuOpen" class="w-5 h-5" />
          <X v-else class="w-5 h-5" />
        </button>
      </div>
    </header>

    <!-- Mobile Navigation Drawer -->
    <div v-if="isMobileMenuOpen" class="md:hidden fixed inset-0 top-16 bg-slate-900/60 backdrop-blur-sm z-20" @click="isMobileMenuOpen = false">
      <div class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 p-4 space-y-2 shadow-xl" @click.stop>
        <Link
          v-for="item in navigation"
          :key="item.name"
          :href="item.href"
          @click="isMobileMenuOpen = false"
          :class="[
            item.active 
              ? 'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 font-bold' 
              : 'text-slate-700 dark:text-slate-300'
          ]"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium"
        >
          <component :is="item.icon" class="w-4 h-4 text-sky-600" />
          <span>{{ item.name }}</span>
        </Link>
        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <ThemeToggle />
            <LanguageToggle />
          </div>
          <button
            @click="logout"
            type="button"
            class="flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-rose-600"
          >
            <LogOut class="w-4 h-4" />
            <span>{{ t('logout') }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
      <div class="p-4 sm:p-6 md:p-8 max-w-7xl w-full mx-auto">
        <slot />
      </div>
    </main>
  </div>
</template>
