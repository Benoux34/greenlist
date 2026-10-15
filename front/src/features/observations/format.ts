import { CATEGORIES } from "@/features/species/categories";
import type { SpeciesDetail } from "@/features/species/types";
import type { Coordinates } from "@/features/map/types";
import { observationPhotoUrl } from "./api";
import type { Observation } from "./types";

const DATE = new Intl.DateTimeFormat("fr-FR", {
  day: "numeric",
  month: "long",
  year: "numeric",
});

const DATE_TIME = new Intl.DateTimeFormat("fr-FR", {
  dateStyle: "long",
  timeStyle: "short",
});

const DEGREES = new Intl.NumberFormat("fr-FR", { maximumFractionDigits: 4 });

const formatObservedAt = (observation: Observation) =>
  DATE.format(new Date(observation.observedAt));

const formatObservedAtTime = (observation: Observation) =>
  DATE_TIME.format(new Date(observation.observedAt));

function formatCoordinates({ latitude, longitude }: Coordinates): string {
  const north = `${DEGREES.format(Math.abs(latitude))}° ${latitude >= 0 ? "N" : "S"}`;
  const east = `${DEGREES.format(Math.abs(longitude))}° ${longitude >= 0 ? "E" : "O"}`;

  return `${north}, ${east}`;
}

const speciesName = (observation: Observation) =>
  observation.species.vernacularName ?? observation.species.scientificName;

function formatCount(observation: Observation): string | null {
  const { count } = observation;
  if (count === null) return null;

  return `${count} ${CATEGORIES[observation.species.category].unit[count > 1 ? 1 : 0]}`;
}

function firstPhotoUrl(observation: Observation): string | null {
  const [first] = observation.photos;

  return first === undefined ? null : observationPhotoUrl(observation.id, first);
}

const photoUrls = (observation: Observation) =>
  observation.photos.map((name) => observationPhotoUrl(observation.id, name));

function classification(species: SpeciesDetail): [string, string][] {
  const rows: [string, string | null][] = [
    ["Règne", species.kingdom],
    ["Embranchement", species.phylum],
    ["Classe", species.taxonClass],
    ["Ordre", species.taxonOrder],
    ["Famille", species.family],
  ];

  return rows.filter((row): row is [string, string] => row[1] !== null);
}

const inaturalistUrl = (species: SpeciesDetail) =>
  `https://www.inaturalist.org/taxa/${species.inatId}`;

export {
  formatObservedAt,
  formatObservedAtTime,
  formatCoordinates,
  speciesName,
  formatCount,
  firstPhotoUrl,
  photoUrls,
  classification,
  inaturalistUrl,
};
