import type { SpeciesCategory } from "./types";

type CategoryConfig = Readonly<{
  label: string;
  dot: string;
  unit: readonly [string, string];
}>;

const CATEGORIES: Readonly<Record<SpeciesCategory, CategoryConfig>> = {
  fauna: {
    label: "Faune",
    dot: "bg-category-fauna",
    unit: ["individu", "individus"],
  },
  flora: {
    label: "Flore",
    dot: "bg-category-flora",
    unit: ["pied", "pieds"],
  },
  fungi: {
    label: "Champignons",
    dot: "bg-category-fungi",
    unit: ["spécimen", "spécimens"],
  },
  other: {
    label: "Autre",
    dot: "bg-ink-faint",
    unit: ["individu", "individus"],
  },
};

const FILTERABLE_CATEGORIES = [
  "fauna",
  "flora",
  "fungi",
] as const satisfies readonly SpeciesCategory[];

export { CATEGORIES, FILTERABLE_CATEGORIES };
