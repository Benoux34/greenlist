import type {
  SpeciesDetail,
  SpeciesSummary,
} from "@/features/species/types";

type Observation = Readonly<{
  id: string;
  species: SpeciesSummary;
  observedAt: string;
  latitude: number;
  longitude: number;
  count: number | null;
  notes: string | null;
  photos: string[];
}>;

type ObservationDetail = Observation &
  Readonly<{
    species: SpeciesDetail;
    place: string | null;
  }>;

type ObservationInput = Readonly<{
  inatId: number;
  observedAt: string;
  latitude: number;
  longitude: number;
  count: number | null;
  notes: string | null;
}>;

export type { Observation, ObservationDetail, ObservationInput };
