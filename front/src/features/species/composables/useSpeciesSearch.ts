import { onScopeDispose, ref, watch, type Ref } from "vue";
import type { Coordinates } from "@/features/map/types";
import { searchSpecies } from "../api";
import type { SpeciesCategory, SpeciesSuggestion } from "../types";

const MIN_LENGTH = 2;
const DELAY = 300;

function useSpeciesSearch(
  text: Ref<string>,
  category: Ref<SpeciesCategory | null>,
  near: () => Coordinates | null,
) {
  const suggestions = ref<SpeciesSuggestion[]>([]);
  const isSearching = ref(false);
  const hasFailed = ref(false);

  let timer: ReturnType<typeof setTimeout> | undefined;
  let controller: AbortController | undefined;

  function cancel(): void {
    clearTimeout(timer);
    controller?.abort();
  }

  watch([text, category, near], () => {
    cancel();
    hasFailed.value = false;

    const wanted = text.value.trim();
    if (wanted.length < MIN_LENGTH) {
      suggestions.value = [];
      isSearching.value = false;
      return;
    }

    isSearching.value = true;

    timer = setTimeout(async () => {
      const current = new AbortController();
      controller = current;

      try {
        suggestions.value = await searchSpecies(
          wanted,
          category.value,
          near(),
          current.signal,
        );
      } catch {
        if (current.signal.aborted) return;
        suggestions.value = [];
        hasFailed.value = true;
      }

      if (!current.signal.aborted) isSearching.value = false;
    }, DELAY);
  });

  onScopeDispose(cancel);

  return { suggestions, isSearching, hasFailed, minLength: MIN_LENGTH };
}

export { useSpeciesSearch };
