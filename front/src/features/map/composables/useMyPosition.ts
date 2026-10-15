import { ref } from "vue";
import type { Coordinates } from "../types";

function useMyPosition() {
  const position = ref<Coordinates | null>(null);
  const isLocating = ref(false);
  const error = ref<string | null>(null);

  function find(): Promise<Coordinates | null> {
    error.value = null;

    if (!("geolocation" in navigator)) {
      error.value = "Votre navigateur ne permet pas la localisation.";
      return Promise.resolve(null);
    }

    isLocating.value = true;

    return new Promise((resolve) => {
      navigator.geolocation.getCurrentPosition(
        ({ coords }) => {
          isLocating.value = false;
          resolve({ latitude: coords.latitude, longitude: coords.longitude });
        },
        (failure) => {
          isLocating.value = false;
          error.value =
            failure.code === failure.PERMISSION_DENIED
              ? "Localisation refusée. Autorisez-la dans les réglages de votre navigateur."
              : "Position introuvable. Réessayez dans un instant.";
          resolve(null);
        },
        { enableHighAccuracy: true, timeout: 10_000, maximumAge: 60_000 },
      );
    });
  }

  async function show(): Promise<void> {
    const found = await find();
    if (found !== null) position.value = found;
  }

  return { position, isLocating, error, find, show };
}

export { useMyPosition };
