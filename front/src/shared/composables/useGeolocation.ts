import { ref } from "vue";

function useGeolocation() {
  const isLocating = ref(false);
  const error = ref<string | null>(null);

  function locate(): Promise<GeolocationCoordinates | null> {
    error.value = null;

    if (!("geolocation" in navigator)) {
      error.value = "Votre navigateur ne permet pas la localisation.";
      return Promise.resolve(null);
    }

    isLocating.value = true;

    return new Promise((resolve) => {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          isLocating.value = false;
          resolve(position.coords);
        },
        (failure) => {
          isLocating.value = false;
          error.value =
            failure.code === failure.PERMISSION_DENIED
              ? "Localisation refusée. Autorisez-la dans votre navigateur ou saisissez la position."
              : "Position introuvable. Réessayez ou saisissez-la à la main.";
          resolve(null);
        },
        { enableHighAccuracy: true, timeout: 10_000, maximumAge: 60_000 },
      );
    });
  }

  return { isLocating, error, locate };
}

export { useGeolocation };
