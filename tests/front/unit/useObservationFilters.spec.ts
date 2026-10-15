import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { effectScope, nextTick, ref } from "vue";
import { DEFAULT_FILTERS } from "@/features/observations/filters";
import { useObservationFilters } from "@/features/observations/composables/useObservationFilters";
import type { Observation } from "@/features/observations/types";
import { observation } from "../fixtures";

const oak = { inatId: 56133, scientificName: "Quercus robur", vernacularName: "Chêne pédonculé", category: "flora" as const };

function setup(list: Observation[]) {
  const observations = ref<Observation[] | null>(list);
  const filters = effectScope().run(() => useObservationFilters(observations))!;

  return { observations, ...filters };
}

describe("useObservationFilters", () => {
  beforeEach(() => {
    vi.useFakeTimers();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it("lists every observation, newest first", () => {
    const old = observation({ observedAt: "2025-03-01T10:00:00Z" });
    const recent = observation({ observedAt: "2026-09-01T10:00:00Z" });
    const middle = observation({ observedAt: "2026-01-01T10:00:00Z" });

    const { filtered, title } = setup([old, recent, middle]);

    expect(filtered.value.map(({ id }) => id)).toEqual([recent.id, middle.id, old.id]);
    expect(title.value).toBe("3 observations");
  });

  it("combines the category and the photo filters", () => {
    const fox = observation({ photos: ["a.jpg"] });
    const bareFox = observation();
    const tree = observation({ species: oak, photos: ["b.jpg"] });
    const { filters, filtered } = setup([fox, bareFox, tree]);

    filters.value = { ...DEFAULT_FILTERS, category: "fauna", withPhotos: true };

    expect(filtered.value.map(({ id }) => id)).toEqual([fox.id]);
  });

  it("applies the search after a short pause and says how many results", async () => {
    const { filters, filtered, title } = setup([observation(), observation({ species: oak })]);

    filters.value = { ...DEFAULT_FILTERS, search: "chene" };
    await nextTick();
    expect(filtered.value).toHaveLength(2);

    vi.advanceTimersByTime(250);
    await nextTick();
    expect(filtered.value).toHaveLength(1);
    expect(title.value).toBe("1 résultat");
  });

  it("drops the search at once when it is cleared", async () => {
    const { filters, filtered } = setup([observation(), observation({ species: oak })]);
    filters.value = { ...DEFAULT_FILTERS, search: "chene" };
    vi.advanceTimersByTime(250);
    await nextTick();

    filters.value = { ...DEFAULT_FILTERS };

    expect(filtered.value).toHaveLength(2);
  });

  it("clears the filters only when they hide a new observation", () => {
    const tree = observation({ species: oak });
    const { observations, filters, makeVisible } = setup([tree]);
    filters.value = { ...DEFAULT_FILTERS, category: "flora" };

    const otherTree = observation({ species: oak });
    observations.value = [tree, otherTree];
    makeVisible(otherTree);
    expect(filters.value.category).toBe("flora");

    const fox = observation();
    observations.value = [tree, otherTree, fox];
    makeVisible({ ...fox });
    expect(filters.value).toEqual(DEFAULT_FILTERS);
  });
});
