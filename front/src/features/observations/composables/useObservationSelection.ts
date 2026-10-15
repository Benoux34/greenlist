import { ref, watch, type Ref } from "vue";
import { useAsyncData } from "@/shared/composables/useAsyncData";
import { fetchObservation } from "../api";
import type { Observation } from "../types";

function useObservationSelection(visible: Readonly<Ref<Observation[]>>) {
  const selected = ref<Observation | null>(null);
  const highlighted = ref<string | null>(null);

  const { data: detail, hasFailed: detailFailed } = useAsyncData(
    () =>
      selected.value === null
        ? Promise.resolve(null)
        : fetchObservation(selected.value.id),
    [() => selected.value?.id],
  );

  watch(visible, (list) => {
    if (selected.value !== null && !list.includes(selected.value))
      selected.value = null;
  });

  return { selected, highlighted, detail, detailFailed };
}

export { useObservationSelection };
