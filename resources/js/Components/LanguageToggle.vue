<script setup>
import { computed } from 'vue';
import { i18nState, setLocale } from '@/i18n';
import { Globe } from 'lucide-vue-next';

const isArabic = computed(() => i18nState.locale === 'ar');

function toggleLanguage(e) {
  if (e) {
    e.preventDefault();
    e.stopPropagation();
  }
  const next = i18nState.locale === 'ar' ? 'en' : 'ar';
  setLocale(next);
}
</script>

<template>
  <button 
    @click.stop.prevent="toggleLanguage" 
    type="button"
    :title="isArabic ? 'Switch to English' : 'التحويل إلى اللغة العربية'"
    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 transition-all shadow-xs active:scale-95 cursor-pointer select-none"
  >
    <Globe class="w-3.5 h-3.5 text-sky-600 dark:text-sky-400" />
    <span>{{ isArabic ? 'EN' : 'العربية' }}</span>
  </button>
</template>
