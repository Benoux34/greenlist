import { fileURLToPath } from "node:url";
import { defineConfig, mergeConfig } from "vitest/config";
import viteConfig from "./vite.config.ts";

// The tests live in tests/front at the root of the repository, outside this
// package: their imports of vue, vitest… are resolved from here.
export default mergeConfig(
  viteConfig,
  defineConfig({
    resolve: {
      dedupe: ["vitest", "@vue/test-utils", "vue", "vue-router"],
    },
    server: { fs: { allow: [".."] } },
    test: {
      dir: fileURLToPath(new URL("../tests/front", import.meta.url)),
      environment: "happy-dom",
      restoreMocks: true,
    },
  }),
);
