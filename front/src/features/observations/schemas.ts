import { z } from "zod";
import type { SpeciesSuggestion } from "@/features/species/types";

const observationSchema = z.object({
  species: z.custom<SpeciesSuggestion>(
    (value) => value !== null && typeof value === "object",
    "Choisissez une espèce.",
  ),
  observedAt: z
    .string()
    .min(1, "Indiquez la date et l'heure.")
    .transform((value) => new Date(value))
    .refine(
      (date) => date.getTime() <= Date.now() + 5 * 60_000,
      "La date ne peut pas être dans le futur.",
    ),
  count: z
    .string()
    .trim()
    .transform((value) => (value === "" ? null : Number(value)))
    .refine(
      (value) => value === null || (Number.isInteger(value) && value > 0),
      "Entier positif.",
    ),
  notes: z.string().trim().max(2000, "2000 caractères au maximum."),
});

export { observationSchema };
