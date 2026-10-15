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
  await page.getByLabel("Mot de passe").fill("mauvais-mot-de-passe");
  await page.getByRole("button", { name: "Se connecter" }).click();
  await expect(page.getByRole("alert")).toHaveText("Adresse électronique ou mot de passe incorrect.");

  await offlineMap(page);
  await page.getByLabel("Mot de passe").fill(PASSWORD);
  await page.getByRole("button", { name: "Se connecter" }).click();
  await waitForMap(page);
  await expect(page).toHaveURL(/\/$/);
});
