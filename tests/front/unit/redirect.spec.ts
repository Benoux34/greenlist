import { describe, expect, it } from "vitest";
import type { RouteLocationNormalizedLoaded } from "vue-router";
import { afterSignIn } from "@/features/auth/redirect";

const at = (redirect?: string) => ({ query: redirect === undefined ? {} : { redirect } }) as RouteLocationNormalizedLoaded;

describe("afterSignIn", () => {
  it("opens the map by default", () => {
    expect(afterSignIn(at())).toEqual({ name: "home" });
  });

  it("goes back to the page that asked for a session", () => {
    expect(afterSignIn(at("/?invitation=abc"))).toBe("/?invitation=abc");
  });

  it("never leaves the site", () => {
    expect(afterSignIn(at("https://example.com"))).toEqual({ name: "home" });
    expect(afterSignIn(at("//example.com"))).toEqual({ name: "home" });
  });
});
