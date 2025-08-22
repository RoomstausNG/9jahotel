import { ref } from "vue";

interface Coordinates {
  latitude: number;
  longitude: number;
}

export function useGeolocation() {
  const coords = ref<Coordinates | null>(null);
  const isSupported =
    typeof navigator !== "undefined" && "geolocation" in navigator;
  const error = ref<Partial<GeolocationPositionError> | null>(null);

  const requestLocation = () => {
    return new Promise<Coordinates>((resolve, reject) => {
      if (!isSupported) {
        const notSupportedError: Partial<GeolocationPositionError> = {
          code: 4, // Using a custom code for not supported
          message: "Geolocation is not supported by this browser.",
        };
        error.value = notSupportedError;
        return reject(notSupportedError);
      }

      // Try to get from localStorage first
      const storedCoords = localStorage.getItem("user_location");
      if (storedCoords) {
        try {
          const parsedCoords = JSON.parse(storedCoords);
          coords.value = parsedCoords;
          return resolve(parsedCoords);
        } catch {
          // Corrupted data in localStorage, proceed to fetch new location
          localStorage.removeItem("user_location");
        }
      }

      navigator.geolocation.getCurrentPosition(
        (position) => {
          const newCoords: Coordinates = {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
          };
          coords.value = newCoords;
          localStorage.setItem("user_location", JSON.stringify(newCoords));
          resolve(newCoords);
        },
        (err) => {
          error.value = err;
          reject(err);
        }
      );
    });
  };

  return { coords, isSupported, error, requestLocation };
}
