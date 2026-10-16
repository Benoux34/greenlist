import { expect, test } from "@playwright/test";
import { PASSWORD, offlineMap, signUp, waitForMap } from "./helpers";

test("a friend joins through an invitation link, then gets removed", async ({ page, browser }, testInfo) => {
  await signUp(page);
  await page.getByRole("button", { name: "Compte de Camille" }).click();
  await page.getByRole("button", { name: "Mes amis" }).click();
  const friends = page.getByRole("region", { name: "Mes amis" });
  await expect(friends.getByText("Vous n'avez pas encore d'amis.")).toBeVisible();

  await friends.getByRole("button", { name: "Inviter un ami" }).click();
  const link = await friends.getByLabel("Lien d'invitation").inputValue();
  expect(link).toMatch(/\/invitation\/[a-f0-9]{32}$/);

  const guest = await (await browser.newContext(testInfo.project.use)).newPage();
  await offlineMap(guest);
  await guest.goto(link);
  await expect(guest).toHaveURL(/\/login\?redirect=/);
  await guest.getByRole("link", { name: "Créer un compte" }).click();
  await guest.getByLabel("Prénom").fill("Léo");
  await guest.getByLabel("Adresse électronique").fill(`e2e-${Date.now()}-guest@example.com`);
  await guest.getByLabel("Mot de passe", { exact: true }).fill(PASSWORD);
  await guest.getByRole("button", { name: "Créer mon compte" }).click();
  await waitForMap(guest);

  await guest.getByRole("dialog", { name: "Camille vous invite" }).getByRole("button", { name: "Accepter" }).click();
  await expect(guest.getByRole("region", { name: "Mes amis" }).getByText("Camille")).toBeVisible();
  await expect(guest).toHaveURL(/\/$/);

  await friends.getByRole("button", { name: "Fermer mes amis" }).click();
  await page.getByRole("button", { name: "Compte de Camille" }).click();
  await page.getByRole("button", { name: "Mes amis" }).click();
  await friends.getByRole("button", { name: "Retirer Léo de mes amis" }).click();
  await page.getByRole("dialog", { name: "Retirer Léo de vos amis ?" }).getByRole("button", { name: "Retirer" }).click();
  await expect(friends.getByText("Vous n'avez pas encore d'amis.")).toBeVisible();
});
