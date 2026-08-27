<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import { t } from '@/i18n';

const props = defineProps({
  points: {
    type: Array,
    default: () => []
  },
  trailPoints: {
    type: Array,
    default: () => []
  },
  selectedEmployee: {
    type: Object,
    default: () => null
  },
  editable: {
    type: Boolean,
    default: false
  },
  selectedCoords: {
    type: Object,
    default: () => null
  }
});

const emit = defineEmits(['select-coordinates', 'select-employee']);

const mapContainer = ref(null);
let map = null;
let markersLayer = null;
let trailLayer = null;
let manualMarker = null;

// Campus center (Baghdad / Al-Ma'moon University)
const defaultLat = 33.3152;
const defaultLng = 44.3661;

function getLeaflet() {
  if (typeof window !== 'undefined') {
    return window.L || L;
  }
  return null;
}

function initMap() {
  if (!mapContainer.value) return;

  const leafletInstance = getLeaflet();
  if (!leafletInstance) return;

  if (map) {
    map.remove();
    map = null;
  }

  // Initialize Map
  map = leafletInstance.map(mapContainer.value, {
    zoomControl: true,
    attributionControl: false
  }).setView([defaultLat, defaultLng], 15);

  // Modern Clean CartoDB Voyager Tile Layer
  leafletInstance.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    subdomains: ['a', 'b', 'c'],
  }).addTo(map);

  // Layer groups
  trailLayer = leafletInstance.layerGroup().addTo(map);
  markersLayer = leafletInstance.layerGroup().addTo(map);

  // Add Campus Geofence Boundary (Circle for Al-Ma'moon University Campus)
  leafletInstance.circle([defaultLat, defaultLng], {
    color: '#0284c7',
    fillColor: '#38bdf8',
    fillOpacity: 0.12,
    weight: 2,
    dashArray: '6, 6',
    radius: 450
  }).addTo(map);

  // Map Click Handler for Manual Pinning
  map.on('click', handleMapClick);

  renderMarkers();
  renderTrail();

  // If already has selected coords
  if (props.selectedCoords && props.selectedCoords.latitude && props.selectedCoords.longitude) {
    setManualPin(props.selectedCoords.latitude, props.selectedCoords.longitude);
  }

  if (mapContainer.value) {
    mapContainer.value.style.cursor = props.editable ? 'crosshair' : 'grab';
  }

  setTimeout(() => {
    map?.invalidateSize();
  }, 300);
}

function handleMapClick(e) {
  if (!props.editable) return;
  const { lat, lng } = e.latlng;
  setManualPin(lat, lng);
  emit('select-coordinates', { latitude: lat, longitude: lng });
}

function setManualPin(lat, lng) {
  if (!map) return;
  const leafletInstance = getLeaflet();
  if (!leafletInstance) return;

  if (manualMarker) {
    markersLayer.removeLayer(manualMarker);
  }

  const pinIcon = leafletInstance.divIcon({
    className: 'custom-manual-pin',
    html: `
      <div class="relative flex items-center justify-center">
        <span class="animate-ping absolute inline-flex h-8 w-8 rounded-full bg-rose-400 opacity-75"></span>
        <div class="w-8 h-8 rounded-full bg-rose-600 border-2 border-white shadow-xl flex items-center justify-center text-white">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
        </div>
      </div>
    `,
    iconSize: [32, 32],
    iconAnchor: [16, 32],
  });

  manualMarker = leafletInstance.marker([lat, lng], { icon: pinIcon }).addTo(markersLayer);
}

function renderMarkers() {
  if (!map || !markersLayer) return;
  const leafletInstance = getLeaflet();
  if (!leafletInstance) return;

  markersLayer.clearLayers();
  if (manualMarker) {
    manualMarker.addTo(markersLayer);
  }

  // If a trail is active, hide general markers to focus on the route
  if (props.trailPoints && props.trailPoints.length > 0) {
    return;
  }

  const bounds = [];

  props.points.forEach((point) => {
    if (!point.latitude || !point.longitude) return;

    bounds.push([point.latitude, point.longitude]);

    const isLiveActive = point.status === 'active' || (point.last_seen_seconds && point.last_seen_seconds < 180);
    const isRecent = point.status === 'recent' || (point.last_seen_seconds && point.last_seen_seconds < 900);

    const statusBadgeColor = isLiveActive
      ? 'bg-emerald-500 ring-4 ring-emerald-500/30'
      : (isRecent ? 'bg-amber-500 ring-2 ring-amber-500/30' : 'bg-slate-400');

    const statusLabel = isLiveActive
      ? t('activeNow')
      : (isRecent ? t('recentActive') : t('offlineStatus'));

    const pulseHtml = isLiveActive
      ? `<span class="animate-ping absolute -top-1 -right-1 inline-flex h-4 w-4 rounded-full bg-emerald-400 opacity-75"></span>`
      : '';

    const userIcon = leafletInstance.divIcon({
      className: 'custom-user-marker',
      html: `
        <div class="relative group cursor-pointer">
          ${pulseHtml}
          <div class="w-9 h-9 rounded-2xl bg-gradient-to-tr from-sky-600 to-teal-600 border-2 border-white shadow-xl flex items-center justify-center text-white font-black text-xs transition-transform transform group-hover:scale-110">
            ${point.user_name ? point.user_name.charAt(0) : 'U'}
          </div>
          <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 ${statusBadgeColor} border-2 border-white rounded-full"></span>
        </div>
      `,
      iconSize: [36, 36],
      iconAnchor: [18, 18],
    });

    const marker = leafletInstance.marker([point.latitude, point.longitude], { icon: userIcon });

    const popupContent = `
      <div class="p-2.5 text-start font-sans min-w-[160px]">
        <div class="flex items-center gap-1.5 mb-1">
          <span class="w-2 h-2 rounded-full ${isLiveActive ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'}"></span>
          <span class="text-[10px] font-bold ${isLiveActive ? 'text-emerald-600' : 'text-slate-500'}">${statusLabel}</span>
        </div>
        <div class="font-black text-slate-900 text-xs leading-tight">${point.user_name || t('employee')}</div>
        <div class="text-[11px] text-sky-600 font-semibold mt-0.5">${point.department_name || t('department')}</div>
        <div class="text-[10px] text-slate-500 mt-2 flex items-center justify-between border-t border-slate-100 pt-1 font-mono">
          <span>${t('tableLogTime')}:</span>
          <span class="font-bold text-slate-800">${point.log_time || '--:--'}</span>
        </div>
      </div>
    `;

    marker.bindPopup(popupContent);
    marker.on('click', () => {
      emit('select-employee', point);
    });

    markersLayer.addLayer(marker);
  });

  // Fit bounds if multiple points
  if (bounds.length > 1 && map) {
    map.fitBounds(bounds, { padding: [40, 40], maxZoom: 17 });
  }
}

function renderTrail() {
  if (!map || !trailLayer) return;
  const leafletInstance = getLeaflet();
  if (!leafletInstance) return;

  trailLayer.clearLayers();

  if (!props.trailPoints || props.trailPoints.length === 0) {
    return;
  }

  const latlngs = props.trailPoints.map(p => [p.latitude, p.longitude]);

  // 1. Draw glowing background shadow polyline
  leafletInstance.polyline(latlngs, {
    color: '#0284c7',
    weight: 8,
    opacity: 0.25,
    lineCap: 'round',
    lineJoin: 'round'
  }).addTo(trailLayer);

  // 2. Draw main vivid route polyline
  leafletInstance.polyline(latlngs, {
    color: '#0284c7',
    weight: 4,
    opacity: 0.9,
    dashArray: '8, 8',
    lineCap: 'round',
    lineJoin: 'round'
  }).addTo(trailLayer);

  // 3. Add Waypoint Markers along the trail
  props.trailPoints.forEach((point, index) => {
    const isStart = index === 0;
    const isEnd = index === props.trailPoints.length - 1;

    let iconHtml = '';
    let size = [24, 24];
    let anchor = [12, 12];

    if (isStart) {
      // Start Marker (Green Flag)
      size = [34, 34];
      anchor = [17, 34];
      iconHtml = `
        <div class="relative flex flex-col items-center">
          <div class="px-2 py-0.5 rounded-full bg-emerald-600 text-white font-mono text-[9px] font-bold shadow-md whitespace-nowrap mb-1">
            ${t('startPoint')} ${point.time}
          </div>
          <div class="w-6 h-6 rounded-full bg-emerald-600 border-2 border-white shadow-lg flex items-center justify-center text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" x2="4" y1="22" y2="15"/></svg>
          </div>
        </div>
      `;
    } else if (isEnd) {
      // Latest / Current Marker (Sky Pulse)
      size = [36, 36];
      anchor = [18, 36];
      iconHtml = `
        <div class="relative flex flex-col items-center">
          <div class="px-2 py-0.5 rounded-full bg-sky-600 text-white font-mono text-[9px] font-bold shadow-md whitespace-nowrap mb-1 animate-pulse">
            ${t('currentPoint')} ${point.time}
          </div>
          <div class="relative flex items-center justify-center">
            <span class="animate-ping absolute inline-flex h-8 w-8 rounded-full bg-sky-400 opacity-75"></span>
            <div class="w-7 h-7 rounded-full bg-sky-600 border-2 border-white shadow-xl flex items-center justify-center text-white">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg>
            </div>
          </div>
        </div>
      `;
    } else {
      // Intermediate Waypoint Dot with timestamp
      iconHtml = `
        <div class="group relative flex items-center justify-center cursor-pointer">
          <div class="w-3.5 h-3.5 rounded-full bg-sky-500 border-2 border-white shadow-md group-hover:scale-150 transition-transform"></div>
          <div class="opacity-0 group-hover:opacity-100 transition-opacity absolute -top-7 px-1.5 py-0.5 rounded-md bg-slate-900 text-white font-mono text-[9px] shadow-md whitespace-nowrap pointer-events-none z-50">
            ${point.time}
          </div>
        </div>
      `;
    }

    const waypointIcon = leafletInstance.divIcon({
      className: 'custom-trail-waypoint',
      html: iconHtml,
      iconSize: size,
      iconAnchor: anchor,
    });

    const marker = leafletInstance.marker([point.latitude, point.longitude], { icon: waypointIcon });
    marker.bindPopup(`
      <div class="p-2 text-start font-sans">
        <div class="font-bold text-xs text-slate-900">${isStart ? t('startPoint') : (isEnd ? t('currentPoint') : t('trailTitle'))}</div>
        <div class="text-[11px] font-mono text-sky-600 font-bold mt-0.5">⏱️ ${point.time} (${point.time_human})</div>
        <div class="text-[10px] text-slate-500 mt-1 font-mono">${point.latitude.toFixed(5)}, ${point.longitude.toFixed(5)}</div>
      </div>
    `);
    trailLayer.addLayer(marker);
  });

  // Fit view bounds to cover the entire trail perfectly
  if (latlngs.length > 0 && map) {
    map.fitBounds(latlngs, { padding: [60, 60], maxZoom: 18 });
  }
}

watch(() => props.points, () => {
  renderMarkers();
}, { deep: true });

watch(() => props.trailPoints, () => {
  renderMarkers();
  renderTrail();
}, { deep: true });

watch(() => props.editable, (isEdit) => {
  if (mapContainer.value) {
    mapContainer.value.style.cursor = isEdit ? 'crosshair' : 'grab';
  }
  if (map) {
    setTimeout(() => {
      map.invalidateSize();
    }, 100);
  }
});

watch(() => props.selectedCoords, (newCoords) => {
  if (newCoords && newCoords.latitude && newCoords.longitude) {
    setManualPin(newCoords.latitude, newCoords.longitude);
  }
}, { deep: true });

onMounted(() => {
  initMap();
});

onUnmounted(() => {
  if (map) {
    map.remove();
    map = null;
  }
});
</script>

<template>
  <div class="relative w-full h-[500px] rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-inner z-0">
    <div ref="mapContainer" class="w-full h-full"></div>
    
    <!-- Floating Map Controls / Mode Indicator -->
    <div class="absolute top-3 end-3 z-10 flex flex-col gap-2">
      <div v-if="editable" class="px-3 py-1.5 rounded-xl bg-rose-600 text-white text-[11px] font-bold shadow-lg flex items-center gap-1.5 animate-pulse">
        <span class="w-2 h-2 rounded-full bg-white"></span>
        <span>{{ t('mapHintAdmin') }}</span>
      </div>
    </div>
  </div>
</template>
