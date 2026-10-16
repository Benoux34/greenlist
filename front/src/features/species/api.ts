import { http } from "@/shared/api/http";
import type { Coordinates } from "@/features/map/types";
import type { SpeciesCategory, SpeciesSuggestion } from "./types";

function searchSpecies(
  text: string,
  category: SpeciesCategory | null,
  near: Coordinates | null,
  signal: AbortSignal,
): Promise<SpeciesSuggestion[]> {
  const query = new URLSearchParams({ q: text });
  if (category !== null) query.set("category", category);
  if (near !== null) {
    query.set("latitude", String(near.latitude));
    query.set("longitude", String(near.longitude));
  }

  return http.get<SpeciesSuggestion[]>(
    `/species/autocomplete?${query}`,
    signal,
  );
}

export { searchSpecies };
