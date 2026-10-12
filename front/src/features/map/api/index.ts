import { http } from "@/shared/api/http";
import type { PlaceSuggestion } from "../entities";

const searchPlaces = (text: string, signal: AbortSignal) =>
  http.get<PlaceSuggestion[]>(
    `/places/search?${new URLSearchParams({ q: text })}`,
    signal,
  );

export { searchPlaces };
