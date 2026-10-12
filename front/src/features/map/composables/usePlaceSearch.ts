import { onScopeDispose, ref } from "vue";
import { searchPlaces } from "../api";
import type { PlaceSuggestion } from "../entities";

const MIN_LENGTH = 3;
const DELAY = 300;

function usePlaceSearch() {
  const suggestions = ref<PlaceSuggestion[]>([]);
  const isSearching = ref(false);
  const hasFailed = ref(false);

  let timer: ReturnType<typeof setTimeout> | undefined;
  let controller: AbortController | undefined;

  function cancel(): void {
    clearTimeout(timer);
    controller?.abort();
  }

  function search(text: string): void {
    cancel();
    hasFailed.value = false;

    const wanted = text.trim();
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
        suggestions.value = await searchPlaces(wanted, current.signal);
      } catch {
        if (current.signal.aborted) return;
        suggestions.value = [];
        hasFailed.value = true;
      }

      if (!current.signal.aborted) isSearching.value = false;
    }, DELAY);
  }

  onScopeDispose(cancel);

  return { suggestions, isSearching, hasFailed, search, minLength: MIN_LENGTH };
}

export { usePlaceSearch };
