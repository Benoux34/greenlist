type SpeciesCategory = "fauna" | "flora" | "fungi" | "other";

type SpeciesSummary = Readonly<{
  id: string;
  scientificName: string;
  vernacularName: string | null;
  category: SpeciesCategory;
}>;

type SpeciesSuggestion = Readonly<{
  inatId: number;
  scientificName: string;
  vernacularName: string | null;
  category: SpeciesCategory;
  photoUrl: string | null;
}>;

export type { SpeciesCategory, SpeciesSuggestion, SpeciesSummary };
