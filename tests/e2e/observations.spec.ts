import { expect, test, type Page } from "@playwright/test";
import { signUp } from "./helpers";

async function touchMap(page: Page, isMobile: boolean): Promise<void> {
  const map = page.getByRole("region", { name: "Carte de mes observations" });
  const box = (await map.boundingBox())!;
  const position = { x: box.width / 2, y: box.height / 3 };

  if (isMobile) await map.tap({ position });
  else await map.click({ position });
}

async function openList(page: Page, isMobile: boolean): Promise<void> {
  if (isMobile) await page.getByRole("button", { name: "Liste", exact: true }).click();
  await expect(page.locator("#observations-panel")).toBeVisible();
}

test("add, edit and delete an observation from the map", async ({ page, isMobile }) => {
  await signUp(page);

  await page.getByRole("button", { name: "Nouvelle observation" }).click();
  await expect(page.getByRole("status").filter({ hasText: "à l'endroit de l'observation" })).toBeVisible();
  await touchMap(page, isMobile);

  const form = page.getByRole("form", { name: "Nouvelle observation" });
  await expect(form).toBeVisible();
  await expect(form.getByText("Lyon, Rhône, France")).toBeVisible();
  await form.getByRole("button", { name: "Enregistrer" }).click();
  await expect(form.getByText("Choisissez une espèce.")).toBeVisible();

  await page.getByLabel("Espèce").fill("renard");
  await page.getByRole("option", { name: /Renard roux/ }).click();
  await page.getByLabel("Nombre").fill("2");
  await page.getByLabel("Notes").fill("Au bord du chemin");
  await form.getByRole("button", { name: "Enregistrer" }).click();

  const sheet = page.getByRole("article", { name: "Renard roux" });
  await expect(sheet).toBeVisible();
  await expect(sheet.getByText("2 individus")).toBeVisible();
  await expect(sheet.getByText("Le Renard roux est un mammifère omnivore.")).toBeVisible();
  await expect(sheet.getByText("Au bord du chemin")).toBeVisible();
  await sheet.getByRole("button", { name: "Fermer" }).click();

  await openList(page, isMobile);
  await page.getByRole("button", { name: /^Modifier Renard roux/ }).click();
  const editForm = page.getByRole("form", { name: "Modifier l'observation" });
  await expect(page.getByLabel("Nombre")).toHaveValue("2");
  await page.getByLabel("Nombre").fill("5");
  await editForm.getByRole("button", { name: "Enregistrer" }).click();
  await expect(sheet.getByText("5 individus")).toBeVisible();
  await sheet.getByRole("button", { name: "Fermer" }).click();

  await openList(page, isMobile);
  await page.getByRole("button", { name: /^Supprimer Renard roux/ }).click();
  const dialog = page.getByRole("dialog", { name: "Supprimer cette observation ?" });
  await dialog.getByRole("button", { name: "Supprimer" }).click();
  await expect(dialog).toBeHidden();
  if (isMobile) await page.getByRole("button", { name: "Fermer la liste" }).click();
  await expect(page.getByText("Votre carte est vide")).toBeVisible();
});
