import { computed, ref, type Ref } from "vue";
import { useDebounced } from "@/shared/composables/useDebounced";
import {
  DEFAULT_FILTERS,
  inRange,
  matchesSearch,
  periodRange,
  type FilterState,
} from "../filters";
import type { Observation } from "../types";

function useObservationFilters(
  observations: Readonly<Ref<Observation[] | null>>,
) {
  const filters = ref<FilterState>({ ...DEFAULT_FILTERS });

  const search = computed(() => filters.value.search);
  const debounced = useDebounced(search, 250);
  const query = computed(() => (search.value.trim() === "" ? "" : debounced.value));
  const range = computed(() =>
    periodRange(filters.value.period, filters.value.from, filters.value.to),
  );

  const filtered = computed(() => {
    const { category, withPhotos } = filters.value;

    return (observations.value ?? [])
      .filter(
        (observation) =>
          (category === null || observation.species.category === category) &&
          (!withPhotos || observation.photos.length > 0) &&
          inRange(observation, range.value) &&
          matchesSearch(observation, query.value),
      )
      .sort((a, b) => Date.parse(b.observedAt) - Date.parse(a.observedAt));
  });

  const title = computed(() => {
    const count = filtered.value.length;

    return query.value.trim() !== ""
      ? `${count} ${count > 1 ? "résultats" : "résultat"}`
      : `${count} ${count > 1 ? "observations" : "observation"}`;
  });

  function makeVisible(observation: Observation): void {
    if (!filtered.value.some(({ id }) => id === observation.id))
      filters.value = { ...DEFAULT_FILTERS };
  }

  return { filters, filtered, title, makeVisible };
}

export { useObservationFilters };
