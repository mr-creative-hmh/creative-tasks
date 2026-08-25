<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { t, i18nState } from '@/i18n';
import { ChevronRight, ChevronLeft } from 'lucide-vue-next';

const props = defineProps({
  links: {
    type: Array,
    default: () => []
  },
  from: {
    type: Number,
    default: 0
  },
  to: {
    type: Number,
    default: 0
  },
  total: {
    type: Number,
    default: 0
  }
});

const isRtl = computed(() => i18nState.locale === 'ar');

// Clean label for Previous/Next
function getCleanLabel(label) {
  if (!label) return '';
  if (label.includes('Previous') || label.includes('&laquo;') || label.includes('«')) {
    return t('previous');
  }
  if (label.includes('Next') || label.includes('&raquo;') || label.includes('»')) {
    return t('next');
  }
  return label;
}
</script>

<template>
  <div v-if="links && links.length > 3" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-4 py-3 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 text-xs">
    <!-- Counter summary -->
    <div class="text-slate-500 dark:text-slate-400 font-medium">
      <span v-if="total > 0">
        {{ t('showingEntries', { from: from || 1, to: to || total, total: total }) }}
      </span>
    </div>

    <!-- Navigation Pagination Pill buttons -->
    <div class="flex items-center gap-1 flex-wrap">
      <template v-for="(link, index) in links" :key="index">
        <!-- Disabled button (no URL) -->
        <span
          v-if="!link.url"
          class="px-3 py-1.5 rounded-xl border border-slate-100 dark:border-slate-800/80 text-slate-300 dark:text-slate-600 text-xs font-semibold cursor-not-allowed select-none bg-slate-50/50 dark:bg-slate-950/30"
          v-html="getCleanLabel(link.label)"
        ></span>

        <!-- Active / Clickable link -->
        <Link
          v-else
          :href="link.url"
          preserve-scroll
          :class="link.active 
            ? 'bg-sky-600 text-white font-black shadow-md shadow-sky-600/25 border border-sky-600' 
            : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-sky-50 dark:hover:bg-slate-700 font-semibold'"
          class="px-3 py-1.5 rounded-xl text-xs transition-all active:scale-95 cursor-pointer select-none"
          v-html="getCleanLabel(link.label)"
        />
      </template>
    </div>
  </div>
</template>
