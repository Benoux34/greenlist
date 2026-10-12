import type {
  SpeciesCategory,
  SpeciesSummary,
} from "@/features/species/entities";

type Observation = Readonly<{
  id: string;
  species: SpeciesSummary;
  observedAt: string;
  latitude: number;
  longitude: number;
  count: number | null;
  notes: string | null;
}>;

type ObservationFilters = Readonly<{
  category: SpeciesCategory | null;
  from: string | null;
  to: string | null;
  page: number;
}>;

type CreateObservationInput = Readonly<{
  inatId: number;
  observedAt: string;
  latitude: number;
  longitude: number;
  count: number | null;
  notes: string | null;
}>;

export type { Observation, ObservationFilters, CreateObservationInput };
