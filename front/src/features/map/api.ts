import { http } from "@/shared/api/http";
import type { Coordinates, PlaceName } from "./types";

function fetchPlaceName({ latitude, longitude }: Coordinates): Promise<PlaceName> {
  const query = new URLSearchParams({
    latitude: String(latitude),
    longitude: String(longitude),
  });

  return http.get<PlaceName>(`/places/name?${query}`);
}

export { fetchPlaceName };
