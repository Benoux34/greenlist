type Coordinates = Readonly<{ latitude: number; longitude: number }>;

type PlaceSuggestion = Coordinates & Readonly<{ label: string }>;

export type { Coordinates, PlaceSuggestion };
