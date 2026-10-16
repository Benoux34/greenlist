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

const resetEmailSchema = loginSchema.pick({ email: true });

const resetCodeSchema = z.object({
  code: z
    .string()
    .trim()
    .toUpperCase()
    .regex(/^[A-Z0-9]{6}$/, "Le code fait 6 lettres ou chiffres."),
});

const newPasswordSchema = z
  .object({
    password: registerSchema.shape.password,
    confirmation: z.string(),
  })
  .refine((value) => value.password === value.confirmation, {
    message: "Les deux mots de passe ne correspondent pas.",
    path: ["confirmation"],
  });

export {
  loginSchema,
  newPasswordSchema,
  registerSchema,
  resetCodeSchema,
  resetEmailSchema,
};
