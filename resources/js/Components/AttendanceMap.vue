<script setup>
import { onMounted, ref, watch } from 'vue';
import L from 'leaflet';

const props = defineProps({
  points: {
    type: Array,
    default: () => []
  },
  editable: {
    type: Boolean,
    default: true
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

function initMap() {
  if (!mapContainer.value) return;

  // Al-Ma'moon University, Baghdad Coordinates
  const defaultLat = 33.31524;
  const defaultLng = 44.36612;

  map = L.map(mapContainer.value).setView([defaultLat, defaultLng], 15);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors | جامعة المأمون'
  }).addTo(map);

  markersLayer = L.layerGroup().addTo(map);
  renderMarkers();

  // Click on map to pick location manually for Admin
  if (props.editable) {
    map.on('click', (e) => {
      const { lat, lng } = e.latlng;
      setManualPin(lat, lng);
      emit('select-coordinates', { latitude: lat, longitude: lng });
    });
  }
}

function setManualPin(lat, lng) {
  if (!map) return;

  if (manualMarker) {
    manualMarker.setLatLng([lat, lng]);
  } else {
    const manualIcon = L.divIcon({
      className: 'manual-map-pin',
      html: `<div style="background-color: #e11d48; color: white; border-radius: 50%; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 0 15px rgba(225,29,72,0.8); font-weight: bold; font-size: 16px; animation: bounce 1s infinite;">📍</div>`,
      iconSize: [34, 34],
      iconAnchor: [17, 17]
    });

    manualMarker = L.marker([lat, lng], { icon: manualIcon, draggable: true }).addTo(map);

    manualMarker.on('dragend', () => {
      const pos = manualMarker.getLatLng();
      emit('select-coordinates', { latitude: pos.lat, longitude: pos.lng });
    });
  }

  manualMarker.bindPopup(`
    <div style="font-family: Cairo, sans-serif; text-align: right; direction: rtl; font-size: 11px;">
      <strong style="color: #e11d48;">الموقع المحدد يدوياً</strong><br>
      ${lat.toFixed(5)}, ${lng.toFixed(5)}<br>
      <span style="color: #64748b;">(اسحب الدبوس لتغيير المكان)</span>
    </div>
  `).openPopup();
}

function renderMarkers() {
  if (!map || !markersLayer) return;
  markersLayer.clearLayers();

  if (props.points.length === 0) return;

  const latLngs = [];

  props.points.forEach((point) => {
    if (point.latitude && point.longitude) {
      const latLng = [point.latitude, point.longitude];
      latLngs.push(latLng);

      const customIcon = L.divIcon({
        className: 'custom-map-pin',
        html: `<div style="background-color: #0284c7; color: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.3); font-weight: bold; font-size: 12px; transition: transform 0.2s;">📍</div>`,
        iconSize: [30, 30],
        iconAnchor: [15, 15]
      });

      const marker = L.marker(latLng, { icon: customIcon }).addTo(markersLayer);
      
      const popupContent = `
        <div style="font-family: Cairo, sans-serif; text-align: right; direction: rtl; min-width: 170px; padding: 2px;">
          <div style="font-weight: 800; color: #0284c7; font-size: 13px;">${point.user_name || 'كادر جامعي'}</div>
          <div style="color: #64748b; font-size: 11px; margin-top: 2px;">${point.department_name || 'الكلية / القسم'}</div>
          <div style="margin-top: 6px; font-size: 11px; color: #1e293b; background: #f0f9ff; padding: 4px 8px; border-radius: 6px;">
            ⏰ وقت التواجد: <strong>${point.log_time}</strong><br>
            📅 التاريخ: <strong>${point.log_date}</strong>
          </div>
          <div style="margin-top: 4px; font-size: 10px; color: #94a3b8;">
            📍 حرم جامعة المأمون (${point.latitude.toFixed(5)}, ${point.longitude.toFixed(5)})
          </div>
        </div>
      `;

      marker.bindPopup(popupContent);
    }
  });

  if (latLngs.length > 0 && !props.selectedCoords) {
    const bounds = L.latLngBounds(latLngs);
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
  initMap();
});
</script>

<template>
  <div class="relative w-full h-[450px] rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-sm">
    <div ref="mapContainer" class="w-full h-full z-0"></div>
    
    <!-- Map Instructions overlay for Admin -->
    <div v-if="editable" class="absolute bottom-3 start-3 z-10 bg-slate-900/85 backdrop-blur-md text-white px-3.5 py-1.5 rounded-xl text-[11px] font-semibold border border-slate-700 shadow-lg pointer-events-none flex items-center gap-1.5">
      <span>💡 انقر فوق أي موقع في الخريطة لتحديد وتعديل إحداثيات الموظف يدوياً</span>
    </div>
  </div>
</template>
