import { reactive } from 'vue';
import axios from 'axios';

export const gpsState = reactive({
  isTracking: false,
  isVerified: false,
  latitude: null,
  longitude: null,
  accuracy: null,
  lastSyncTime: null,
  lastSyncTimestamp: 0,
  isSyncing: false,
  error: null,
  offlineQueueCount: 0,
  isOutsideShift: false,
});

let watchId = null;
let intervalId = null;
let isInitialized = false;

// Calculate distance in meters between two coordinates using Haversine formula
function calculateDistance(lat1, lon1, lat2, lon2) {
  if (!lat1 || !lon1 || !lat2 || !lon2) return Infinity;
  const R = 6371e3; // Earth radius in meters
  const φ1 = (lat1 * Math.PI) / 180;
  const φ2 = (lat2 * Math.PI) / 180;
  const Δφ = ((lat2 - lat1) * Math.PI) / 180;
  const Δλ = ((lon2 - lon1) * Math.PI) / 180;

  const a =
    Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
    Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

  return R * c; // Distance in meters
}

// Send coordinates to server silently
async function sendCoordinates(lat, lng, accuracy = null) {
  if (!navigator.onLine) {
    queueOffline(lat, lng, accuracy);
    return;
  }

  gpsState.isSyncing = true;
  gpsState.error = null;

  try {
    const response = await axios.post('/attendance/log', {
      latitude: lat,
      longitude: lng,
      accuracy: accuracy,
    }, {
      headers: {
        'X-Silent-Tracking': 'true',
      },
    });

    const now = new Date();
    gpsState.lastSyncTime = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    gpsState.lastSyncTimestamp = Date.now();
    gpsState.isVerified = true;
    gpsState.latitude = lat;
    gpsState.longitude = lng;
    gpsState.accuracy = accuracy ? Math.round(accuracy) : null;

    // Dispatch global event for live maps to re-center or plot
    if (typeof window !== 'undefined') {
      window.dispatchEvent(
        new CustomEvent('gps-updated', {
          detail: { latitude: lat, longitude: lng, accuracy, time: gpsState.lastSyncTime },
        })
      );
    }
  } catch (err) {
    gpsState.error = err.response?.data?.message || 'Error syncing GPS location';
  } finally {
    gpsState.isSyncing = false;
  }
}

// Offline queue management
function queueOffline(lat, lng, accuracy) {
  try {
    const queue = JSON.parse(localStorage.getItem('gps_offline_queue') || '[]');
    queue.push({
      latitude: lat,
      longitude: lng,
      accuracy,
      timestamp: Date.now(),
    });
    // Keep max 20 offline pings
    if (queue.length > 20) queue.shift();
    localStorage.setItem('gps_offline_queue', JSON.stringify(queue));
    gpsState.offlineQueueCount = queue.length;
  } catch (e) {
    console.warn('GPS queue error', e);
  }
}

async function flushOfflineQueue() {
  if (!navigator.onLine) return;
  try {
    const queue = JSON.parse(localStorage.getItem('gps_offline_queue') || '[]');
    if (queue.length === 0) return;

    // Send latest coordinate
    const latest = queue[queue.length - 1];
    await sendCoordinates(latest.latitude, latest.longitude, latest.accuracy);
    localStorage.removeItem('gps_offline_queue');
    gpsState.offlineQueueCount = 0;
  } catch (e) {
    console.warn('Flush offline GPS queue error', e);
  }
}

// Process new geolocation position with intelligent debouncing / throttling
function handlePosition(position) {
  const { latitude, longitude, accuracy } = position.coords;
  const now = Date.now();
  const timeSinceLastSync = now - gpsState.lastSyncTimestamp;

  const distanceMoved = calculateDistance(
    gpsState.latitude,
    gpsState.longitude,
    latitude,
    longitude
  );

  // Update local reactive state
  gpsState.latitude = latitude;
  gpsState.longitude = longitude;
  gpsState.accuracy = Math.round(accuracy);
  gpsState.isTracking = true;

  // Trigger server sync if:
  // 1. Never synced before, OR
  // 2. Moved more than 20 meters, OR
  // 3. More than 2.5 minutes (150,000 ms) have passed since last ping
  if (
    !gpsState.lastSyncTimestamp ||
    distanceMoved > 20 ||
    timeSinceLastSync > 150000
  ) {
    sendCoordinates(latitude, longitude, accuracy);
  }
}

function handleError(error) {
  switch (error.code) {
    case error.PERMISSION_DENIED:
      gpsState.error = 'تم رفض إذن الوصول للموقع الجغرافي (GPS Denied)';
      break;
    case error.POSITION_UNAVAILABLE:
      gpsState.error = 'إشارة الـ GPS غير متوفرة حالياً';
      break;
    case error.TIMEOUT:
      gpsState.error = 'انتهت مهلة قراءة الموقع الجغرافي';
      break;
    default:
      gpsState.error = 'تعذر قراءة الموقع الجغرافي';
  }
  gpsState.isTracking = false;
}

// Initialize Global Invisible Background GPS Tracker
export function initGlobalGpsTracker() {
  if (typeof window === 'undefined' || !navigator.geolocation) {
    gpsState.error = 'المتصفح لا يدعم خدمة تحديد الموقع الجغرافي';
    return;
  }

  if (isInitialized) {
    // Flush offline queue if needed
    flushOfflineQueue();
    return;
  }

  isInitialized = true;
  gpsState.isTracking = true;

  // Listen to online events to flush offline backlog
  window.addEventListener('online', flushOfflineQueue);

  // 1. Initial One-shot immediate sync
  navigator.geolocation.getCurrentPosition(handlePosition, handleError, {
    enableHighAccuracy: true,
    timeout: 15000,
    maximumAge: 5000,
  });

  // 2. High-precision continuous watchPosition
  watchId = navigator.geolocation.watchPosition(handlePosition, handleError, {
    enableHighAccuracy: true,
    timeout: 25000,
    maximumAge: 10000,
  });

  // 3. Heartbeat interval every 2 minutes for stationary presence keepalive
  intervalId = setInterval(() => {
    if (navigator.geolocation && gpsState.isTracking) {
      navigator.geolocation.getCurrentPosition(handlePosition, handleError, {
        enableHighAccuracy: false,
        timeout: 10000,
        maximumAge: 60000,
      });
    }
  }, 120000);
}

// Stop tracker (on logout)
export function stopGlobalGpsTracker() {
  if (watchId !== null && typeof navigator !== 'undefined') {
    navigator.geolocation.clearWatch(watchId);
    watchId = null;
  }
  if (intervalId !== null) {
    clearInterval(intervalId);
    intervalId = null;
  }
  isInitialized = false;
  gpsState.isTracking = false;
}
