import { expect, type Page } from "@playwright/test";

// A blank map style: the runs never depend on the OpenFreeMap tiles. The
// layers are the ones the app recolours when the style loads.
const STYLE = {
  version: 8,
  glyphs: "https://tiles.openfreemap.org/fonts/{fontstack}/{range}.pbf",
  sources: { empty: { type: "geojson", data: { type: "FeatureCollection", features: [] } } },
  layers: [
    { id: "background", type: "background" },
    { id: "water", type: "fill", source: "empty" },
    { id: "waterway_river", type: "line", source: "empty" },
    { id: "waterway_other", type: "line", source: "empty" },
  ],
};

const PASSWORD = "motdepasse123";

async function offlineMap(page: Page): Promise<void> {
  await page.route("https://tiles.openfreemap.org/**", (route) =>
    route.request().url().includes("/styles/")
      ? route.fulfill({ json: STYLE })
      : route.fulfill({ contentType: "application/x-protobuf", body: Buffer.alloc(0) }),
  );
}

async function waitForMap(page: Page): Promise<void> {
  await expect(page.getByRole("region", { name: "Carte de mes observations" })).toBeVisible();
  await expect(page.getByText("Chargement de la carte…")).toBeHidden();
}

async function signUp(page: Page): Promise<string> {
  const email = `e2e-${Date.now()}-${Math.random().toString(36).slice(2, 8)}@example.com`;

  await offlineMap(page);
  await page.goto("/register");
  await page.getByLabel("Prénom").fill("Camille");
  await page.getByLabel("Adresse électronique").fill(email);
  await page.getByLabel("Mot de passe", { exact: true }).fill(PASSWORD);
  await page.getByRole("button", { name: "Créer mon compte" }).click();
  await waitForMap(page);

  return email;
}

export { PASSWORD, offlineMap, signUp, waitForMap };
