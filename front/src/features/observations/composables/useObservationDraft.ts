import { ref } from "vue";
import type { Coordinates } from "@/features/map/types";
import type { Observation } from "../types";

function useObservationDraft(locate: () => Promise<Coordinates | null>) {
  const isDrafting = ref(false);
  const editing = ref<Observation | null>(null);
  const position = ref<Coordinates | null>(null);

  function start(observation: Observation | null = null): void {
    editing.value = observation;
    position.value = observation && {
      latitude: observation.latitude,
      longitude: observation.longitude,
    };
    isDrafting.value = true;
  }

  function cancel(): void {
    isDrafting.value = false;
    editing.value = null;
    position.value = null;
  }

  function place(at: Coordinates): void {
    if (isDrafting.value) position.value = at;
  }

  async function placeAtMyPosition(): Promise<void> {
    const found = await locate();
    if (found !== null) place(found);
  }

  return {
    isDrafting,
    editing,
    position,
    start,
    cancel,
    place,
    placeAtMyPosition,
  };
}

export { useObservationDraft };
