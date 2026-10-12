import { http } from "@/shared/api/http";
import type { SpeciesCategory, SpeciesSuggestion } from "../entities";

function searchSpecies(
  text: string,
  category: SpeciesCategory | null,
  signal: AbortSignal,
): Promise<SpeciesSuggestion[]> {
  const query = new URLSearchParams({ q: text });
  if (category !== null) query.set("category", category);

  return http.get<SpeciesSuggestion[]>(
    `/species/autocomplete?${query}`,
    signal,
  );
}

export { searchSpecies };
