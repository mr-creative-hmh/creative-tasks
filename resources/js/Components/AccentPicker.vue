<script setup>
import { ref, onMounted } from 'vue';
import { Palette, Check } from 'lucide-vue-next';
import { t } from '@/i18n';

const isOpen = ref(false);
const currentAccent = ref('sky');

const colors = [
  { id: 'sky', name: 'accentSky', bg: '#0284c7', ring: 'ring-sky-500' },
  { id: 'emerald', name: 'accentEmerald', bg: '#059669', ring: 'ring-emerald-500' },
  { id: 'indigo', name: 'accentIndigo', bg: '#4f46e5', ring: 'ring-indigo-500' },
  { id: 'teal', name: 'accentTeal', bg: '#0d9488', ring: 'ring-teal-500' },
  { id: 'rose', name: 'accentRose', bg: '#e11d48', ring: 'ring-rose-500' },
  { id: 'amber', name: 'accentAmber', bg: '#d97706', ring: 'ring-amber-500' },
  { id: 'purple', name: 'accentPurple', bg: '#9333ea', ring: 'ring-purple-500' },
];

function setAccent(colorId) {
  currentAccent.value = colorId;
  document.documentElement.setAttribute('data-accent', colorId);
  localStorage.setItem('app_accent', colorId);
  window.dispatchEvent(new CustomEvent('accent-changed', { detail: colorId }));
  isOpen.value = false;
}

onMounted(() => {
  const saved = localStorage.getItem('app_accent') || 'sky';
  currentAccent.value = saved;
  document.documentElement.setAttribute('data-accent', saved);
});
</script>

<template>
  <div class="relative">
    <!-- Trigger Button -->
    <button
      @click="isOpen = !isOpen"
      type="button"
      class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all cursor-pointer flex items-center justify-center relative active:scale-95 shadow-xs"
      :title="t('accentColor')"
    >
      <Palette class="w-4 h-4" />
      <!-- Small color indicator dot -->
      <span
        class="absolute bottom-1 end-1 w-2 h-2 rounded-full border border-white dark:border-slate-900 shadow-xs"
        :style="{ backgroundColor: colors.find(c => c.id === currentAccent)?.bg || '#0284c7' }"
      ></span>
    </button>

    <!-- Palette Dropdown Popover -->
    <div
      v-if="isOpen"
      class="fixed inset-0 z-40"
      @click="isOpen = false"
    ></div>

    <div
      v-if="isOpen"
      class="absolute end-0 mt-2 z-50 p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl w-56 animate-fade-in"
      @click.stop
    >
      <div class="flex items-center justify-between pb-2 mb-2.5 border-b border-slate-100 dark:border-slate-800">
        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
          <Palette class="w-3.5 h-3.5 text-slate-400" />
          {{ t('accentColor') }}
        </span>
      </div>

      <div class="grid grid-cols-4 gap-2">
        <button
          v-for="color in colors"
          :key="color.id"
          @click="setAccent(color.id)"
          type="button"
          :title="t(color.name)"
          :class="[
            currentAccent === color.id ? 'ring-2 ring-offset-2 dark:ring-offset-slate-900 ' + color.ring : 'opacity-80 hover:opacity-100 hover:scale-105',
          ]"
          class="w-10 h-10 rounded-xl flex items-center justify-center transition-all cursor-pointer shadow-xs relative"
          :style="{ backgroundColor: color.bg }"
        >
          <Check v-if="currentAccent === color.id" class="w-4 h-4 text-white" />
        </button>
      </div>
    </div>
  </div>
</template>
