import type {
  RouteLocationNormalizedLoaded,
  RouteLocationRaw,
} from "vue-router";

function afterSignIn(route: RouteLocationNormalizedLoaded): RouteLocationRaw {
  const { redirect } = route.query;

  return typeof redirect === "string" &&
    redirect.startsWith("/") &&
    !redirect.startsWith("//")
    ? redirect
    : { name: "home" };
}

export { afterSignIn };
