<script setup>
import { onMounted, ref, watch, nextTick } from 'vue';
import { i18nState, t } from '@/i18n';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

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
    default: null
  }
});

const emit = defineEmits(['select-coordinates']);

const mapContainer = ref(null);
let map = null;
let markersLayer = null;
let manualMarker = null;

// Al-Ma'moon University, Baghdad Coordinates
const defaultLat = 33.31524;
const defaultLng = 44.36612;

function getLeaflet() {
  return (typeof window !== 'undefined' && window.L) ? window.L : L;
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

  // If editable, listen to map clicks
  if (props.editable) {
    map.on('click', (e) => {
      const { lat, lng } = e.latlng;
      setManualPin(lat, lng);
      emit('select-coordinates', { latitude: lat, longitude: lng });
    });
  }

  renderMarkers();

  // If already has selected coords
  if (props.selectedCoords && props.selectedCoords.latitude) {
    setManualPin(props.selectedCoords.latitude, props.selectedCoords.longitude);
  }

  // Trigger resize to prevent grey tiles
  setTimeout(() => {
    map?.invalidateSize();
  }, 300);
}

function setManualPin(lat, lng) {
  if (!map) return;
  const leafletInstance = getLeaflet();
  if (!leafletInstance) return;

  if (manualMarker) {
    markersLayer?.removeLayer(manualMarker);
  }

  const pinIcon = leafletInstance.divIcon({
    className: 'custom-manual-pin',
    html: `<div style="background-color: #f43f5e; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 12px rgba(244,63,94,0.7); animation: bounce 1s infinite alternate; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px; font-weight: bold;">📍</div>`,
    iconSize: [24, 24],
    iconAnchor: [12, 12]
  });

  manualMarker = leafletInstance.marker([lat, lng], { icon: pinIcon }).addTo(markersLayer);
}

function renderMarkers() {
  if (!map || !markersLayer) return;
  const leafletInstance = getLeaflet();
  if (!leafletInstance) return;

  markersLayer.clearLayers();

  const latLngs = [];

  props.points.forEach((point) => {
    if (point.latitude && point.longitude) {
      const latLng = [point.latitude, point.longitude];
      latLngs.push(latLng);

      const customIcon = leafletInstance.divIcon({
        className: 'custom-map-marker',
        html: `
          <div style="
            background: linear-gradient(135deg, #0284c7, #0d9488);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 11px;
            border: 2px solid #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.15), 0 2px 4px -1px rgba(0, 0, 0, 0.08);
          ">
            ${point.user_name ? point.user_name.substring(0, 1) : 'U'}
          </div>
        `,
        iconSize: [32, 32],
        iconAnchor: [16, 16]
      });

      const marker = leafletInstance.marker(latLng, { icon: customIcon }).addTo(markersLayer);
      const isRtl = i18nState.locale === 'ar';
      
      const popupContent = `
        <div style="font-family: inherit; text-align: ${isRtl ? 'right' : 'left'}; direction: ${isRtl ? 'rtl' : 'ltr'}; min-width: 170px; padding: 2px;">
          <div style="font-weight: 800; color: #0284c7; font-size: 13px;">${point.user_name || ''}</div>
          <div style="color: #64748b; font-size: 11px; margin-top: 2px;">${point.department_name || ''}</div>
          <div style="margin-top: 6px; font-size: 11px; color: #1e293b; background: #f0f9ff; padding: 4px 8px; border-radius: 6px;">
            ⏰ ${t('tableLogTime')}: <strong>${point.log_time}</strong><br>
            📅 ${t('tableLogDate')}: <strong>${point.log_date}</strong>
          </div>
          <div style="margin-top: 4px; font-size: 10px; color: #94a3b8;">
            📍 (${point.latitude.toFixed(5)}, ${point.longitude.toFixed(5)})
          </div>
        </div>
      `;

      marker.bindPopup(popupContent);
    }
  });

  if (latLngs.length > 0 && !props.selectedCoords) {
    const bounds = leafletInstance.latLngBounds(latLngs);
    map.fitBounds(bounds, { padding: [50, 50], maxZoom: 16 });
  }
}

watch(() => props.points, () => {
  renderMarkers();
}, { deep: true });

watch(() => props.selectedCoords, (newCoords) => {
  if (newCoords && newCoords.latitude && newCoords.longitude) {
    setManualPin(newCoords.latitude, newCoords.longitude);
    map?.panTo([newCoords.latitude, newCoords.longitude]);
  }
}, { deep: true });

onMounted(() => {
  nextTick(() => {
    initMap();
  });
});
</script>

<template>
  <div class="relative w-full h-full rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-xs">
    <div ref="mapContainer" class="w-full h-full z-0"></div>
    
    <!-- Map Instructions overlay for Admin -->
    <div v-if="editable" class="absolute bottom-3 start-3 z-10 bg-slate-900/85 backdrop-blur-md text-white px-3.5 py-1.5 rounded-xl text-[11px] font-semibold border border-slate-700 shadow-lg pointer-events-none flex items-center gap-1.5">
      <span>{{ t('mapHintAdmin') }}</span>
    </div>
  </div>
</template>
