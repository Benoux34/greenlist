import { expect, test } from "@playwright/test";
import { PASSWORD, offlineMap, signUp, waitForMap } from "./helpers";

test("the map is only for signed-in users", async ({ page }) => {
  await page.goto("/");

  await expect(page).toHaveURL(/\/login$/);
  await expect(page.getByRole("heading", { name: "Se connecter" })).toBeVisible();
});

test("sign up, sign out and sign back in", async ({ page }) => {
  const email = await signUp(page);
  await expect(page.getByText("Votre carte est vide")).toBeVisible();

  await page.getByRole("button", { name: "Compte de Camille" }).click();
  await page.getByRole("button", { name: "Se déconnecter" }).click();
  await expect(page).toHaveURL(/\/login$/);

  await page.getByLabel("Adresse électronique").fill(email);
  await page.getByLabel("Mot de passe", { exact: true }).fill("mauvais-mot-de-passe");
  await page.getByRole("button", { name: "Se connecter" }).click();
  await expect(page.getByRole("alert")).toHaveText("Adresse électronique ou mot de passe incorrect.");

  await offlineMap(page);
  await page.getByLabel("Mot de passe", { exact: true }).fill(PASSWORD);
  await page.getByRole("button", { name: "Se connecter" }).click();
  await waitForMap(page);
  await expect(page).toHaveURL(/\/$/);
});

test("ask for a code and type a wrong one", async ({ page }) => {
  await page.goto("/login");
  await page.getByRole("link", { name: "Mot de passe oublié ?" }).click();
  await expect(page).toHaveURL(/\/forgot-password$/);

  await page.getByLabel("Adresse électronique").fill("camille@example.com");
  await page.getByRole("button", { name: "Recevoir un code" }).click();
  await expect(page.getByText("camille@example.com")).toBeVisible();

  await page.getByLabel("Code reçu par e-mail").fill("12345");
  await page.getByRole("button", { name: "Vérifier le code" }).click();
  await expect(page.getByText("Le code fait 6 lettres ou chiffres.")).toBeVisible();

  await page.getByLabel("Code reçu par e-mail").fill("A1B2C3");
  await page.getByRole("button", { name: "Vérifier le code" }).click();
  await expect(page.getByText("Code incorrect ou expiré.")).toBeVisible();

  await page.getByRole("link", { name: "Retour à la connexion" }).click();
  await expect(page).toHaveURL(/\/login$/);
});
