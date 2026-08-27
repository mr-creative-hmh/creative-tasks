<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import { t } from '@/i18n';
import { MapPin, Navigation } from 'lucide-vue-next';

const props = defineProps({
  lat: {
    type: [Number, String],
    default: 33.31524
  },
  lng: {
    type: [Number, String],
    default: 44.36612
  },
  height: {
    type: String,
    default: '260px'
  },
  zoom: {
    type: Number,
    default: 16
  }
});

const emit = defineEmits(['update:lat', 'update:lng', 'change']);

const mapContainer = ref(null);
let map = null;
let marker = null;
let geofenceCircle = null;

// Baghdad Corporate Headquarters coordinates
const defaultCampusLat = 33.31524;
const defaultCampusLng = 44.36612;

function createPinIcon() {
  return L.divIcon({
    className: 'custom-picker-pin',
    html: `
      <div style="position: relative; width: 36px; height: 36px; transform: translate(-50%, -100%);">
        <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #0284c7, #0d9488); border: 2.5px solid white; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); box-shadow: 0 4px 14px rgba(2, 132, 199, 0.45); display: flex; align-items: center; justify-content: center;">
          <div style="width: 12px; height: 12px; background: white; border-radius: 50%;"></div>
        </div>
      </div>
    `,
    iconSize: [36, 36],
    iconAnchor: [18, 36]
  });
}

function initMap() {
  if (!mapContainer.value) return;

  if (map) {
    map.remove();
    map = null;
  }

  const initialLat = Number(props.lat) || defaultCampusLat;
  const initialLng = Number(props.lng) || defaultCampusLng;

  map = L.map(mapContainer.value, {
    center: [initialLat, initialLng],
    zoom: props.zoom,
    zoomControl: true,
    attributionControl: false
  });

  // High performance clean tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    subdomains: ['a', 'b', 'c']
  }).addTo(map);

  // Corporate Geofence Perimeter Guide
  geofenceCircle = L.circle([defaultCampusLat, defaultCampusLng], {
    color: '#0284c7',
    fillColor: '#38bdf8',
    fillOpacity: 0.12,
    weight: 2,
    dashArray: '5, 8',
    radius: 450 // 450 meters campus perimeter
  }).addTo(map);

  // Add Interactive Pin Marker
  marker = L.marker([initialLat, initialLng], {
    icon: createPinIcon(),
    draggable: true
  }).addTo(map);

  // Marker drag event
  marker.on('dragend', (e) => {
    const position = marker.getLatLng();
    updateCoordinates(position.lat, position.lng);
  });

  // Map click to place pin
  map.on('click', (e) => {
    const { lat, lng } = e.latlng;
    marker.setLatLng([lat, lng]);
    updateCoordinates(lat, lng);
  });

  setTimeout(() => {
    if (map) map.invalidateSize();
  }, 200);
}

function updateCoordinates(newLat, newLng) {
  const roundedLat = parseFloat(Number(newLat).toFixed(6));
  const roundedLng = parseFloat(Number(newLng).toFixed(6));
  emit('update:lat', roundedLat);
  emit('update:lng', roundedLng);
  emit('change', { lat: roundedLat, lng: roundedLng });
}

function panTo(newLat, newLng) {
  const latVal = Number(newLat) || defaultCampusLat;
  const lngVal = Number(newLng) || defaultCampusLng;
  if (map && marker) {
    marker.setLatLng([latVal, lngVal]);
    map.flyTo([latVal, lngVal], props.zoom, { duration: 0.8 });
  }
}

watch(
  () => [props.lat, props.lng],
  ([newLat, newLng]) => {
    if (map && marker) {
      const cur = marker.getLatLng();
      if (Math.abs(cur.lat - Number(newLat)) > 0.00001 || Math.abs(cur.lng - Number(newLng)) > 0.00001) {
        panTo(newLat, newLng);
      }
    }
  }
);

onMounted(() => {
  nextTick(() => {
    initMap();
  });
});

onUnmounted(() => {
  if (map) {
    map.remove();
    map = null;
  }
});

defineExpose({
  panTo,
  refresh: () => {
    if (map) map.invalidateSize();
  }
});
</script>

<template>
  <div class="relative w-full rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 shadow-inner bg-slate-100 dark:bg-slate-800">
    <div ref="mapContainer" :style="{ height: props.height }" class="w-full z-0"></div>

    <!-- Mini overlay badge indicating campus center -->
    <div class="absolute bottom-2 start-2 z-[400] bg-white/90 dark:bg-slate-900/90 backdrop-blur-xs px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-800 text-[10px] font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5 shadow-xs pointer-events-none">
      <MapPin class="w-3 h-3 text-sky-600 dark:text-sky-400" />
      <span>المقر الرئيسي للمؤسسة (افتراضي)</span>
    </div>
  </div>
</template>