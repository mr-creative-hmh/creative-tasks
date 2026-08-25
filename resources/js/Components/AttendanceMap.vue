<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

const props = defineProps({
  points: {
    type: Array,
    default: () => []
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

const emit = defineEmits(['select-coordinates']);

const mapContainer = ref(null);
let map = null;
let markersLayer = null;
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
  if (!leafletInstance) {
    console.error('Leaflet is not loaded');
    return;
  }

  // If already initialized, remove old map
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
  leafletInstance.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
    maxZoom: 19,
  }).addTo(map);

  markersLayer = leafletInstance.layerGroup().addTo(map);

  // Add Campus Geofence Boundary (Circle for Al-Ma'moon University Campus)
  leafletInstance.circle([defaultLat, defaultLng], {
    color: '#0284c7',
    fillColor: '#38bdf8',
    fillOpacity: 0.15,
    radius: 400
  }).addTo(map);

  // Map Click Handler for Manual Pinning
  map.on('click', handleMapClick);

  renderMarkers();

  // If already has selected coords
  if (props.selectedCoords && props.selectedCoords.latitude && props.selectedCoords.longitude) {
    setManualPin(props.selectedCoords.latitude, props.selectedCoords.longitude);
  }

  // Set initial cursor
  if (mapContainer.value) {
    mapContainer.value.style.cursor = props.editable ? 'crosshair' : 'grab';
  }

  // Trigger resize to prevent grey tiles
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

  // Clear existing attendance markers (preserve manualMarker if exists)
  markersLayer.clearLayers();
  if (manualMarker) {
    manualMarker.addTo(markersLayer);
  }

  props.points.forEach((point) => {
    if (!point.latitude || !point.longitude) return;

    const userIcon = leafletInstance.divIcon({
      className: 'custom-user-marker',
      html: `
        <div class="relative group">
          <div class="w-8 h-8 rounded-full bg-sky-600 border-2 border-white shadow-lg flex items-center justify-center text-white font-bold text-xs">
            ${point.user_name ? point.user_name.charAt(0) : 'U'}
          </div>
          <span class="absolute -bottom-1 -right-1 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full"></span>
        </div>
      `,
      iconSize: [32, 32],
      iconAnchor: [16, 16],
    });

    const marker = leafletInstance.marker([point.latitude, point.longitude], { icon: userIcon });

    const popupContent = `
      <div class="p-2 text-start font-sans" dir="rtl">
        <div class="font-bold text-slate-900 text-xs">${point.user_name || 'موظف'}</div>
        <div class="text-[11px] text-sky-600 font-semibold">${point.department_name || 'قسم غير محدد'}</div>
        <div class="text-[10px] text-slate-500 mt-1 flex items-center gap-1">
          <span>⏰ وقت الحضور:</span>
          <span class="font-mono font-bold">${point.log_time || '--:--'}</span>
        </div>
      </div>
    `;

    marker.bindPopup(popupContent);
    markersLayer.addLayer(marker);
  });
}

watch(() => props.points, () => {
  renderMarkers();
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
  <div class="relative w-full h-[480px] rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-inner z-0">
    <div ref="mapContainer" class="w-full h-full"></div>
    
    <!-- Floating Map Controls / Mode Indicator -->
    <div class="absolute top-3 end-3 z-10 flex flex-col gap-2">
      <div v-if="editable" class="px-3 py-1.5 rounded-xl bg-rose-600 text-white text-[11px] font-bold shadow-lg flex items-center gap-1.5 animate-pulse">
        <span class="w-2 h-2 rounded-full bg-white"></span>
        <span>وضع التحديد اليدوي نشط (انقر لتثبيت الموقع)</span>
      </div>
    </div>
  </div>
</template>
