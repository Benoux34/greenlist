import { describe, expect, it } from "vitest";
import { loginSchema, registerSchema } from "@/features/auth/schemas";
import { observationSchema } from "@/features/observations/schemas";
import { fieldErrors } from "@/shared/utils/fieldErrors";

const species = {
  inatId: 42069,
  scientificName: "Vulpes vulpes",
  vernacularName: "Renard roux",
  category: "fauna" as const,
  photoUrl: null,
};
const valid = { species, observedAt: "2026-06-01T10:30", count: "", notes: "" };

function errorsOf(input: Record<string, unknown>) {
  const parsed = observationSchema.safeParse({ ...valid, ...input });

  return parsed.success ? {} : fieldErrors(parsed.error);
}

describe("observationSchema", () => {
  it("accepts a minimal observation and converts its fields", () => {
    const parsed = observationSchema.parse({ ...valid, count: " 3 ", notes: "  Près du ruisseau " });

    expect(parsed.count).toBe(3);
    expect(parsed.notes).toBe("Près du ruisseau");
    expect(parsed.observedAt).toBeInstanceOf(Date);
    expect(observationSchema.parse(valid).count).toBeNull();
  });

  it("needs a species and a date", () => {
    expect(errorsOf({ species: null })).toEqual({ species: "Choisissez une espèce." });
    expect(errorsOf({ observedAt: "" }).observedAt).toBe("Indiquez la date et l'heure.");
  });

  it("refuses a date in the future", () => {
    expect(errorsOf({ observedAt: "2999-01-01T00:00" })).toEqual({
      observedAt: "La date ne peut pas être dans le futur.",
    });
  });

  it("only counts with positive whole numbers", () => {
    for (const count of ["0", "-2", "1.5", "deux"])
      expect(errorsOf({ count })).toEqual({ count: "Entier positif." });
  });

  it("limits the notes to 2000 characters", () => {
    expect(errorsOf({ notes: "a".repeat(2001) })).toEqual({ notes: "2000 caractères au maximum." });
  });
});

describe("auth schemas", () => {
  it("checks the sign-in fields", () => {
    const parsed = loginSchema.safeParse({ email: "nope", password: "" });

    expect(parsed.success ? {} : fieldErrors(parsed.error)).toEqual({
      email: "Adresse électronique invalide.",
      password: "Mot de passe requis.",
    });
  });

  it("checks the sign-up fields", () => {
    const parsed = registerSchema.safeParse({ firstName: "  ", email: "ana@example.fr", password: "short" });

    expect(parsed.success ? {} : fieldErrors(parsed.error)).toEqual({
      firstName: "Prénom requis.",
      password: "Au moins 8 caractères.",
    });
  });
});
