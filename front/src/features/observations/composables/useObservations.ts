import { useAsyncData } from "@/shared/composables/useAsyncData";
import { fetchMapObservations } from "../api";
import type { Observation } from "../types";

function useObservations() {
  const { data, hasFailed, reload } = useAsyncData(fetchMapObservations, []);

  function put(observation: Observation): void {
    const list = data.value ?? [];

    data.value = list.some(({ id }) => id === observation.id)
      ? list.map((other) => (other.id === observation.id ? observation : other))
      : [...list, observation];
  }

  function remove(observation: Observation): void {
    data.value = (data.value ?? []).filter(({ id }) => id !== observation.id);
  }

  return { observations: data, hasFailed, reload, put, remove };
}

export { useObservations };
