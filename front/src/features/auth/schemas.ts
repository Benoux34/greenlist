import { z } from "zod";

const loginSchema = z.object({
  email: z.email("Adresse électronique invalide."),
  password: z.string().min(1, "Mot de passe requis."),
});

const registerSchema = z.object({
  firstName: z
    .string()
    .trim()
    .min(1, "Prénom requis.")
    .max(100, "100 caractères au maximum."),
  email: z.email("Adresse électronique invalide."),
  password: z.string().min(8, "Au moins 8 caractères."),
});

export { loginSchema, registerSchema };
