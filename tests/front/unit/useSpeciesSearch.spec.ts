import { flushPromises } from "@vue/test-utils";
import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { effectScope, nextTick, ref } from "vue";
import { searchSpecies } from "@/features/species/api";
import { useSpeciesSearch } from "@/features/species/composables/useSpeciesSearch";
import type { SpeciesCategory } from "@/features/species/types";

vi.mock("@/features/species/api", () => ({ searchSpecies: vi.fn() }));

const fox = { inatId: 42069, scientificName: "Vulpes vulpes", vernacularName: "Renard roux", category: "fauna" as const, photoUrl: null };

function setup() {
  const text = ref("");
  const category = ref<SpeciesCategory | null>(null);
  const scope = effectScope();
  const search = scope.run(() => useSpeciesSearch(text, category))!;

  return { text, category, scope, ...search };
}

describe("useSpeciesSearch", () => {
  beforeEach(() => {
    vi.useFakeTimers();
    vi.mocked(searchSpecies).mockResolvedValue([fox]);
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it("waits for two letters and a pause in the typing", async () => {
    const { text, suggestions, isSearching } = setup();

    text.value = "r";
    await nextTick();
    vi.advanceTimersByTime(1000);
    expect(searchSpecies).not.toHaveBeenCalled();

    text.value = "re";
    await nextTick();
    expect(isSearching.value).toBe(true);
    vi.advanceTimersByTime(299);
    expect(searchSpecies).not.toHaveBeenCalled();
    vi.advanceTimersByTime(1);
    await flushPromises();

    expect(searchSpecies).toHaveBeenCalledWith("re", null, expect.any(AbortSignal));
    expect(suggestions.value).toEqual([fox]);
    expect(isSearching.value).toBe(false);
  });

  it("cancels the previous request when the text changes", async () => {
    vi.mocked(searchSpecies).mockReturnValue(new Promise(() => {}));
    const { text, category } = setup();

    text.value = "ren";
    await nextTick();
    vi.advanceTimersByTime(300);
    const signal = vi.mocked(searchSpecies).mock.calls[0]![2];

    category.value = "fauna";
    await nextTick();

    expect(signal.aborted).toBe(true);
  });

  it("flags an unavailable service", async () => {
    vi.mocked(searchSpecies).mockRejectedValue(new Error("down"));
    const { text, suggestions, hasFailed } = setup();

    text.value = "renard";
    await nextTick();
    vi.advanceTimersByTime(300);
    await flushPromises();

    expect(hasFailed.value).toBe(true);
    expect(suggestions.value).toEqual([]);
  });

  it("stops everything when its scope ends", async () => {
    const { text, scope } = setup();

    text.value = "renard";
    await nextTick();
    scope.stop();
    vi.advanceTimersByTime(300);

    expect(searchSpecies).not.toHaveBeenCalled();
  });
});
