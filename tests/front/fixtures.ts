import type { Observation } from "@/features/observations/types";

let sequence = 0;

function observation(overrides: Partial<Observation> = {}): Observation {
  sequence += 1;

  return {
    id: `obs-${sequence}`,
    species: {
      inatId: 42069,
      scientificName: "Vulpes vulpes",
      vernacularName: "Renard roux",
      category: "fauna",
    },
    observedAt: "2026-10-15T12:00:00+00:00",
    latitude: 45.76,
    longitude: 4.83,
    count: null,
    notes: null,
    photos: [],
    ...overrides,
  };
}

export { observation };
