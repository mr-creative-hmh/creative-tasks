<script setup>
import { ref } from 'vue';
import { gpsState } from '@/Services/gpsTracker';
import { t } from '@/i18n';
import {
  Navigation,
  Radio,
  CheckCircle2,
  AlertTriangle,
  RefreshCw,
  X,
  MapPin,
  Clock,
  ShieldCheck,
  WifiOff
} from 'lucide-vue-next';

const isOpen = ref(false);

function triggerManualSync() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        // Will be picked up and synced by handlePosition
        gpsState.latitude = pos.coords.latitude;
        gpsState.longitude = pos.coords.longitude;
        gpsState.accuracy = Math.round(pos.coords.accuracy);
      },
      () => {},
      { enableHighAccuracy: true, timeout: 10000 }
    );
  }
}
</script>

<template>
  <div class="relative inline-block text-xs select-none">
    <!-- Trigger Pill Button -->
    <button
      @click="isOpen = !isOpen"
      type="button"
      :class="[
        gpsState.isVerified
          ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60 hover:bg-emerald-100'
          : (gpsState.error
            ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60 hover:bg-rose-100'
            : 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60 hover:bg-amber-100')
      ]"
      class="h-8 px-2.5 rounded-xl border transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs active:scale-95 font-bold text-[11px]"
      :title="gpsState.isVerified ? 'تتبع الموقع الجغرافي نشط في الخلفية' : (gpsState.error || 'جاري الاتصال بالـ GPS...')"
    >
      <!-- Pulsing Dot Indicator -->
      <span class="relative flex h-2 w-2">
        <span
          v-if="gpsState.isVerified"
          class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"
        ></span>
        <span
          :class="[
            gpsState.isVerified
              ? 'bg-emerald-500'
              : (gpsState.error ? 'bg-rose-500' : 'bg-amber-500 animate-pulse')
          ]"
          class="relative inline-flex rounded-full h-2 w-2"
        ></span>
      </span>

      <!-- Label -->
      <span class="hidden sm:inline">
        {{ gpsState.isVerified ? 'GPS Live' : (gpsState.error ? 'GPS تنبيه' : 'GPS...') }}
      </span>

      <!-- Offline Backlog Badge if any -->
      <span
        v-if="gpsState.offlineQueueCount > 0"
        class="px-1 py-0.2 rounded-full bg-amber-500 text-white text-[9px] font-mono font-black"
        title="سجلات بانتظار المزامنة"
      >
        {{ gpsState.offlineQueueCount }}
      </span>
    </button>

    <!-- Backdrop Closer -->
    <div
      v-if="isOpen"
      class="fixed inset-0 z-40"
      @click="isOpen = false"
    ></div>

    <!-- Live Status Popover Details -->
    <div
      v-if="isOpen"
      class="absolute z-50 top-full mt-2 end-0 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl w-72 animate-fade-in text-start"
      @click.stop
    >
      <!-- Header -->
      <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-100 dark:border-slate-800">
        <div class="flex items-center gap-2">
          <div
            :class="gpsState.isVerified ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950' : 'bg-amber-50 text-amber-600 dark:bg-amber-950'"
            class="w-7 h-7 rounded-xl flex items-center justify-center font-bold"
          >
            <Radio class="w-3.5 h-3.5" />
          </div>
          <div>
            <h4 class="text-xs font-bold text-slate-900 dark:text-white">تتبع الحضور الميداني التلقائي</h4>
            <p class="text-[10px] text-slate-400">تحديث تلقائي صامت في الخلفية</p>
          </div>
        </div>
        <button @click="isOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
          <X class="w-3.5 h-3.5" />
        </button>
      </div>

      <!-- Status Info -->
      <div class="space-y-2 text-[11px]">
        <!-- Verification status -->
        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60">
          <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
            <ShieldCheck class="w-3.5 h-3.5 text-accent" />
            <span>حالة التوثيق:</span>
          </span>
          <span
            :class="gpsState.isVerified ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-500'"
            class="font-bold font-mono text-[10px]"
          >
            {{ gpsState.isVerified ? 'موثق جغرافياً' : 'جاري التحقق...' }}
          </span>
        </div>

        <!-- Coordinates -->
        <div v-if="gpsState.latitude" class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 font-mono">
          <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1.5 font-sans">
            <MapPin class="w-3.5 h-3.5 text-sky-500" />
            <span>الإحداثيات:</span>
          </span>
          <span class="text-slate-800 dark:text-slate-200 font-bold text-[10px]">
            {{ gpsState.latitude?.toFixed(4) }}, {{ gpsState.longitude?.toFixed(4) }}
          </span>
        </div>

        <!-- Accuracy -->
        <div v-if="gpsState.accuracy" class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60">
          <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
            <Navigation class="w-3.5 h-3.5 text-teal-500" />
            <span>دقة الإشارة:</span>
          </span>
          <span class="text-slate-800 dark:text-slate-200 font-bold font-mono text-[10px]">
            ± {{ gpsState.accuracy }} متر
          </span>
        </div>

        <!-- Last Sync Time -->
        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60">
          <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
            <Clock class="w-3.5 h-3.5 text-amber-500" />
            <span>آخر مزامنة:</span>
          </span>
          <span class="text-slate-800 dark:text-slate-200 font-bold font-mono text-[10px]">
            {{ gpsState.lastSyncTime || 'الآن' }}
          </span>
        </div>

        <!-- Error if present -->
        <div v-if="gpsState.error" class="p-2 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-[10px] flex items-center gap-1.5">
          <AlertTriangle class="w-3.5 h-3.5 shrink-0" />
          <span>{{ gpsState.error }}</span>
        </div>
      </div>

      <!-- Action Button -->
      <div class="pt-3 mt-3 border-t border-slate-100 dark:border-slate-800">
        <button
          @click="triggerManualSync"
          :disabled="gpsState.isSyncing"
          type="button"
          class="w-full h-8 rounded-xl bg-accent bg-accent-hover text-white font-bold text-[11px] shadow-xs active:scale-95 disabled:opacity-50 transition-all flex items-center justify-center gap-1.5 cursor-pointer"
        >
          <RefreshCw :class="{ 'animate-spin': gpsState.isSyncing }" class="w-3.5 h-3.5" />
          <span>{{ gpsState.isSyncing ? 'جاري المزامنة...' : 'تحديث الموقع الآن' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>
