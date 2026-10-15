import { defineConfig, devices } from "@playwright/test";

// The API runs in its "test" environment: iNaturalist, Photon and Wikipedia
// are replaced by fixed answers, and the photos stay on disk.
const API = "http://127.0.0.1:8010";
const FRONT = "http://localhost:5174";

export default defineConfig({
  testDir: ".",
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  reporter: process.env.CI ? [["github"], ["html", { open: "never" }]] : "list",
  use: {
    baseURL: FRONT,
    trace: "retain-on-failure",
  },
  projects: [
    {
      name: "desktop",
      use: { ...devices["Desktop Chrome"], viewport: { width: 1280, height: 800 } },
    },
    { name: "mobile", use: { ...devices["Pixel 7"] } },
  ],
  webServer: [
    {
      command: "php -d variables_order=EGPCS -S 127.0.0.1:8010 -t public -q",
      cwd: "../../api",
      url: `${API}/api/me`,
      env: { APP_ENV: "test", PHP_CLI_SERVER_WORKERS: "4" },
      reuseExistingServer: !process.env.CI,
    },
    {
      command: "npm run dev -- --port 5174 --strictPort",
      cwd: "../../front",
      url: FRONT,
      env: { API_URL: API },
      reuseExistingServer: !process.env.CI,
    },
  ],
});
