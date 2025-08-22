const STORAGE_KEY = "geolocation_cache";
const CACHE_DURATION = 1 * 60 * 60 * 1000; // 24 hours in milliseconds

interface CachedLocation {
  coords: {
    latitude: number;
    longitude: number;
  };
  timestamp: number;
}

export function useAdvancedGeolocation() {
  const getCachedLocation = (): GeolocationCoordinates | null => {
    const cached = localStorage.getItem(STORAGE_KEY);
    if (!cached) {
      return null;
    }

    try {
      const data: CachedLocation = JSON.parse(cached);
      const isExpired = Date.now() - data.timestamp > CACHE_DURATION;

      if (isExpired) {
        console.log("Geolocation cache expired.");
        localStorage.removeItem(STORAGE_KEY);
        return null;
      }

      console.log("Using valid cached location.");
      return data.coords as GeolocationCoordinates;
    } catch (e) {
      console.error("Error reading geolocation cache:", e);
      localStorage.removeItem(STORAGE_KEY);
      return null;
    }
  };

  const setCachedLocation = (coords: GeolocationCoordinates) => {
    try {
      const data: CachedLocation = {
        coords: {
          latitude: coords.latitude,
          longitude: coords.longitude,
        },
        timestamp: Date.now(),
      };
      localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
      console.log("Geolocation data cached.");
    } catch (e) {
      console.error("Error saving to geolocation cache:", e);
    }
  };

  const requestLocation = (): Promise<GeolocationCoordinates> => {
    return new Promise((resolve, reject) => {
      const cachedCoords = getCachedLocation();
      if (cachedCoords) {
        resolve(cachedCoords);
        return;
      }

      if (!navigator.geolocation) {
        return reject(
          new Error("Geolocation is not supported by your browser.")
        );
      }

      navigator.geolocation.getCurrentPosition(
        (position) => {
          setCachedLocation(position.coords);
          resolve(position.coords);
        },
        (error) => {
          reject(error);
        }
      );
    });
  };

  return { requestLocation };
}
