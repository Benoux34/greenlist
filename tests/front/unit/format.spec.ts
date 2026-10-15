import { describe, expect, it } from "vitest";
import {
  classification,
  firstPhotoUrl,
  formatCoordinates,
  formatCount,
  formatObservedAt,
  inaturalistUrl,
  photoUrls,
  speciesName,
} from "@/features/observations/format";
import type { SpeciesDetail } from "@/features/species/types";
import { observation } from "../fixtures";

describe("observation formatting", () => {
  it("writes coordinates with their hemisphere", () => {
    expect(formatCoordinates({ latitude: 45.123456, longitude: 6.5 })).toBe(
      "45,1235° N, 6,5° E",
    );
    expect(formatCoordinates({ latitude: -33.9, longitude: -70.6 })).toBe(
      "33,9° S, 70,6° O",
    );
  });

  it("counts with the unit of the category", () => {
    expect(formatCount(observation({ count: 1 }))).toBe("1 individu");
    expect(formatCount(observation({ count: 3 }))).toBe("3 individus");
    expect(
      formatCount(
        observation({
          count: 2,
          species: { inatId: 56133, scientificName: "Quercus robur", vernacularName: null, category: "flora" },
        }),
      ),
    ).toBe("2 pieds");
    expect(formatCount(observation({ count: null }))).toBeNull();
  });

  it("prefers the French name", () => {
    expect(speciesName(observation())).toBe("Renard roux");
    expect(
      speciesName(
        observation({
          species: { inatId: 1, scientificName: "Amanita muscaria", vernacularName: null, category: "fungi" },
        }),
      ),
    ).toBe("Amanita muscaria");
  });

  it("writes the date in French", () => {
    expect(formatObservedAt(observation({ observedAt: "2026-10-15T12:00:00+00:00" }))).toBe(
      "15 octobre 2026",
    );
  });

  it("points photos to the authenticated API", () => {
    const withPhotos = observation({ id: "abc", photos: ["one.jpg", "two.png"] });

    expect(photoUrls(withPhotos)).toEqual([
      "/api/observations/abc/photos/one.jpg",
      "/api/observations/abc/photos/two.png",
    ]);
    expect(firstPhotoUrl(withPhotos)).toBe("/api/observations/abc/photos/one.jpg");
    expect(firstPhotoUrl(observation())).toBeNull();
  });

  it("lists only the known ranks of the classification", () => {
    const species: SpeciesDetail = {
      inatId: 42069,
      scientificName: "Vulpes vulpes",
      vernacularName: "Renard roux",
      category: "fauna",
      kingdom: "Animalia",
      phylum: "Chordata",
      taxonClass: null,
      taxonOrder: "Carnivora",
      family: "Canidae",
      description: null,
      wikipediaUrl: null,
    };

    expect(classification(species)).toEqual([
      ["Règne", "Animalia"],
      ["Embranchement", "Chordata"],
      ["Ordre", "Carnivora"],
      ["Famille", "Canidae"],
    ]);
    expect(inaturalistUrl(species)).toBe("https://www.inaturalist.org/taxa/42069");
  });
});
