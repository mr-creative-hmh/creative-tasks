import { reactive } from 'vue';
import axios from 'axios';
import { t } from '@/i18n';

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
  const dLat = ((lat2 - lat1) * Math.PI) / 180;
  const dLon = ((lon2 - lon1) * Math.PI) / 180;
  const radLat1 = (lat1 * Math.PI) / 180;
  const radLat2 = (lat2 * Math.PI) / 180;

  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(radLat1) * Math.cos(radLat2) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

  return R * c; // Distance in meters
}

// Send coordinates to server silently
export async function sendCoordinates(lat, lng, accuracy = null) {
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
    gpsState.error = null;

    // Dispatch global event for live maps to re-center or plot
    if (typeof window !== 'undefined') {
      window.dispatchEvent(
        new CustomEvent('gps-updated', {
          detail: { latitude: lat, longitude: lng, accuracy, time: gpsState.lastSyncTime },
        })
      );
    }
  } catch (err) {
    gpsState.error = err.response?.data?.message || t('connectionFailed');
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
  gpsState.error = null;

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

// Resilient Geolocation Resolver: Tries high accuracy first, then seamlessly falls back to standard accuracy
export function resolveCurrentPosition(onSuccess, onError) {
  if (typeof window === 'undefined' || !navigator.geolocation) {
    if (onError) onError(new Error(t('gpsError')));
    return;
  }

  // Attempt 1: High Accuracy with reasonable timeout
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      onSuccess(pos);
    },
    (err) => {
      // If timed out or unavailable, automatically fall back to standard/network accuracy
      console.warn('High accuracy GPS timed out or unavailable, falling back to standard accuracy:', err.message);
      
      navigator.geolocation.getCurrentPosition(
        (fallbackPos) => {
          onSuccess(fallbackPos);
        },
        (finalErr) => {
          if (onError) onError(finalErr);
        },
        {
          enableHighAccuracy: false,
          timeout: 15000,
          maximumAge: 300000, // Accept cached location up to 5 minutes old
        }
      );
    },
    {
      enableHighAccuracy: true,
      timeout: 6000, // 6 seconds before fallback
      maximumAge: 60000,
    }
  );
}

// Manual One-Click Sync Action with Fallback & Server Update
export async function syncCurrentGpsLocation() {
  gpsState.isSyncing = true;
  gpsState.error = null;

  resolveCurrentPosition(
    async (pos) => {
      const { latitude, longitude, accuracy } = pos.coords;
      await sendCoordinates(latitude, longitude, accuracy);
    },
    (err) => {
      gpsState.isSyncing = false;
      if (err.code === 1) {
        gpsState.error = 'تم رفض الإذن للوصول للموقع (Permission Denied)';
      } else if (err.code === 2) {
        gpsState.error = 'إشارة الموقع غير متوفرة حالياً (Position Unavailable)';
      } else if (err.code === 3) {
        gpsState.error = 'انتهت مهلة استجابة الـ GPS (Timeout Expired)';
      } else {
        gpsState.error = err.message || t('gpsError');
      }
    }
  );
}

function handleError(error) {
  // If watchPosition encounters a timeout, don't crash the UI; gracefully try standard fix
  if (error.code === 3) {
    resolveCurrentPosition(handlePosition, null);
    return;
  }

  if (error.code === 1) {
    gpsState.error = 'تم رفض إذن الوصول للموقع (GPS Permission Denied)';
  } else if (error.code === 2) {
    gpsState.error = 'إشارة الـ GPS غير متوفرة';
  } else {
    gpsState.error = t('gpsError');
  }
}

// Initialize Global Invisible Background GPS Tracker
export function initGlobalGpsTracker() {
  if (typeof window === 'undefined' || !navigator.geolocation) {
    gpsState.error = 'المتصفح لا يدعم التحديد الجغرافي';
    return;
  }

  if (isInitialized) {
    flushOfflineQueue();
    return;
  }

  isInitialized = true;
  gpsState.isTracking = true;

  // Listen to online events to flush offline backlog
  window.addEventListener('online', flushOfflineQueue);

  // 1. Initial immediate sync using resilient resolver
  resolveCurrentPosition(handlePosition, handleError);

  // 2. Continuous watchPosition
  watchId = navigator.geolocation.watchPosition(handlePosition, handleError, {
    enableHighAccuracy: true,
    timeout: 20000,
    maximumAge: 30000,
  });

  // 3. Heartbeat interval every 2 minutes for stationary presence keepalive
  intervalId = setInterval(() => {
    if (navigator.geolocation && gpsState.isTracking) {
      resolveCurrentPosition(handlePosition, null);
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
