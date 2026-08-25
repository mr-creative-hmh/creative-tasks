<script setup>
import { onMounted } from 'vue';
import { isDark, toggleTheme, initTheme } from '@/theme';
import { Sun, Moon } from 'lucide-vue-next';

onMounted(() => {
  initTheme();
});
</script>

<template>
  <button
    @click="toggleTheme"
    type="button"
    :title="isDark ? 'التبديل إلى الوضع النهاري (Light Mode)' : 'التبديل إلى الوضع الليلي (Dark Mode)'"
    class="relative p-2 rounded-xl border transition-all flex items-center justify-center cursor-pointer select-none active:scale-95 bg-white dark:bg-slate-800/80 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-amber-300 hover:bg-slate-50 dark:hover:bg-slate-800 shadow-sm"
  >
    <Sun v-if="isDark" class="w-4 h-4 text-amber-400 transition-transform duration-300 rotate-0 hover:rotate-45" />
    <Moon v-else class="w-4 h-4 text-sky-600 transition-transform duration-300 -rotate-12 hover:rotate-0" />
    <span class="sr-only">Toggle Dark/Light Mode</span>
  </button>
</template>
