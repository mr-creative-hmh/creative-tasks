<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  },
  badge: {
    type: String,
    default: ''
  },
  icon: {
    type: Object,
    default: null
  }
});
</script>

<template>
  <div class="relative w-full rounded-3xl overflow-hidden mb-6 p-5 sm:p-6 lg:p-7 border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-sm transition-all">
    <!-- Ambient Background Lighting Mesh -->
    <div class="absolute inset-0 bg-gradient-to-r from-slate-50 via-white to-slate-50/50 dark:from-slate-900 dark:via-slate-900/90 dark:to-slate-950 pointer-events-none"></div>
    <div class="absolute -top-16 -end-16 w-64 h-64 bg-accent/10 dark:bg-accent/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-16 -start-16 w-64 h-64 bg-teal-500/10 dark:bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
      <!-- Title & Icon Info -->
      <div class="flex items-start sm:items-center gap-3.5 sm:gap-4.5">
        <!-- Icon Container -->
        <div
          v-if="icon"
          class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-accent-gradient text-white flex items-center justify-center shadow-lg shadow-accent/25 shrink-0 transform transition-transform hover:scale-105"
        >
          <component :is="icon" class="w-6 h-6 sm:w-7 sm:h-7" />
        </div>

        <div>
          <!-- Badge Pill if provided -->
          <div v-if="badge" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-accent-light text-accent border border-accent/20 text-[10px] font-bold mb-1.5 shadow-2xs">
            <span class="w-1.5 h-1.5 rounded-full bg-accent animate-pulse"></span>
            <span>{{ badge }}</span>
          </div>

          <!-- Main Title -->
          <h1 class="text-xl sm:text-2xl lg:text-3xl font-black tracking-tight text-slate-900 dark:text-white leading-tight">
            {{ title }}
          </h1>

          <!-- Subtitle Description -->
          <p v-if="subtitle" class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-2xl leading-relaxed">
            {{ subtitle }}
          </p>
        </div>
      </div>

      <!-- Action Buttons Slot -->
      <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2 sm:gap-2.5 shrink-0">
        <slot name="actions" />
      </div>
    </div>
  </div>
</template>
