import { CATEGORIES } from "@/features/species/categories";
import type { SpeciesCategory } from "@/features/species/types";
import type { Observation } from "./types";

type Period = "all" | "7d" | "30d" | "year" | "last-year" | "custom";

const PERIODS: readonly { value: Period; label: string }[] = [
  { value: "all", label: "Toutes les dates" },
  { value: "7d", label: "7 derniers jours" },
  { value: "30d", label: "30 derniers jours" },
  { value: "year", label: "Cette année" },
  { value: "last-year", label: "L'année dernière" },
  { value: "custom", label: "Dates précises" },
];

type Range = readonly [start: Date | null, end: Date | null];

function daysAgo(days: number): Date {
  const day = new Date();
  day.setHours(0, 0, 0, 0);
  day.setDate(day.getDate() - days);

  return day;
}

function dayAfter(date: string): Date {
  const day = new Date(`${date}T00:00`);
  day.setDate(day.getDate() + 1);

  return day;
}

function periodRange(period: Period, from: string, to: string): Range {
  const year = new Date().getFullYear();

  switch (period) {
    case "7d":
      return [daysAgo(6), null];
    case "30d":
      return [daysAgo(29), null];
    case "year":
      return [new Date(year, 0, 1), null];
    case "last-year":
      return [new Date(year - 1, 0, 1), new Date(year, 0, 1)];
    case "custom":
      return [
        from === "" ? null : new Date(`${from}T00:00`),
        to === "" ? null : dayAfter(to),
      ];
    default:
      return [null, null];
  }
}

function inRange(observation: Observation, [start, end]: Range): boolean {
  const observedAt = new Date(observation.observedAt);

  return (
    (start === null || observedAt >= start) &&
    (end === null || observedAt < end)
  );
}

const normalize = (text: string) =>
  text.normalize("NFD").replace(/\p{Diacritic}/gu, "").toLowerCase();

function matchesSearch(observation: Observation, query: string): boolean {
  const words = normalize(query).split(/\s+/).filter(Boolean);
  if (words.length === 0) return true;

  const { species, notes } = observation;
  const text = normalize(
    [
      species.vernacularName,
      species.scientificName,
      CATEGORIES[species.category].label,
      notes,
    ]
      .filter(Boolean)
      .join(" "),
  );

  return words.every((word) => text.includes(word));
}

type FilterState = Readonly<{
  search: string;
  category: SpeciesCategory | null;
  period: Period;
  from: string;
  to: string;
  withPhotos: boolean;
}>;

const DEFAULT_FILTERS: FilterState = {
  search: "",
  category: null,
  period: "all",
  from: "",
  to: "",
  withPhotos: false,
};

const isFiltering = (filters: FilterState) =>
  filters.search.trim() !== "" ||
  filters.category !== null ||
  filters.period !== "all" ||
  filters.withPhotos;

export { DEFAULT_FILTERS, PERIODS, inRange, isFiltering, matchesSearch, periodRange };
export type { FilterState };
