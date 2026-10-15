import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import {
  DEFAULT_FILTERS,
  inRange,
  isFiltering,
  matchesSearch,
  periodRange,
} from "@/features/observations/filters";
import { observation } from "../fixtures";

const at = (date: Date) => observation({ observedAt: date.toISOString() });

describe("periodRange", () => {
  beforeEach(() => {
    vi.useFakeTimers();
    vi.setSystemTime(new Date(2026, 9, 15, 14, 30));
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it("counts the last 7 days from midnight, today included", () => {
    expect(periodRange("7d", "", "")).toEqual([new Date(2026, 9, 9), null]);
  });

  it("covers the current and the previous calendar year", () => {
    expect(periodRange("year", "", "")).toEqual([new Date(2026, 0, 1), null]);
    expect(periodRange("last-year", "", "")).toEqual([
      new Date(2025, 0, 1),
      new Date(2026, 0, 1),
    ]);
  });

  it("keeps the whole last day of a custom period", () => {
    const range = periodRange("custom", "2026-06-01", "2026-06-30");

    expect(inRange(at(new Date(2026, 5, 30, 23, 59)), range)).toBe(true);
    expect(inRange(at(new Date(2026, 6, 1, 0, 0)), range)).toBe(false);
    expect(inRange(at(new Date(2026, 4, 31, 23, 59)), range)).toBe(false);
  });

  it("leaves an open end when a custom date is missing", () => {
    expect(periodRange("custom", "", "")).toEqual([null, null]);
    expect(periodRange("all", "2026-06-01", "2026-06-30")).toEqual([null, null]);
  });
});

describe("matchesSearch", () => {
  const fox = observation({ notes: "Sous la pluie, près du bouleau" });

  it("ignores case and accents", () => {
    expect(matchesSearch(fox, "RENARD")).toBe(true);
    expect(matchesSearch(observation({ notes: "Forêt" }), "foret")).toBe(true);
  });

  it("looks in the scientific name, the category and the notes", () => {
    expect(matchesSearch(fox, "vulpes")).toBe(true);
    expect(matchesSearch(fox, "faune")).toBe(true);
    expect(matchesSearch(fox, "bouleau")).toBe(true);
  });

  it("needs every word to match", () => {
    expect(matchesSearch(fox, "renard pluie")).toBe(true);
    expect(matchesSearch(fox, "renard neige")).toBe(false);
  });

  it("matches everything when the text is blank", () => {
    expect(matchesSearch(fox, "   ")).toBe(true);
  });
});

describe("isFiltering", () => {
  it("is false for the default filters only", () => {
    expect(isFiltering(DEFAULT_FILTERS)).toBe(false);
    expect(isFiltering({ ...DEFAULT_FILTERS, search: "  " })).toBe(false);
    expect(isFiltering({ ...DEFAULT_FILTERS, search: "renard" })).toBe(true);
    expect(isFiltering({ ...DEFAULT_FILTERS, category: "flora" })).toBe(true);
    expect(isFiltering({ ...DEFAULT_FILTERS, period: "7d" })).toBe(true);
    expect(isFiltering({ ...DEFAULT_FILTERS, withPhotos: true })).toBe(true);
  });
});
