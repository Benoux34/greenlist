import { flushPromises, mount } from "@vue/test-utils";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { createMemoryHistory, createRouter, type Router } from "vue-router";
import LoginForm from "@/features/auth/components/LoginForm.vue";
import { ApiError } from "@/shared/api/http";

const login = vi.fn();
vi.mock("@/features/auth/store", () => ({ useAuthStore: () => ({ login }) }));

const page = { render: () => null };

async function mountAt(path: string): Promise<{ wrapper: ReturnType<typeof mount>; router: Router }> {
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: "/", name: "home", component: page },
      { path: "/login", name: "login", component: page },
      { path: "/register", name: "register", component: page },
      { path: "/forgot-password", name: "forgot-password", component: page },
    ],
  });
  await router.push(path);

  return { wrapper: mount(LoginForm, { global: { plugins: [router] } }), router };
}

async function fill(wrapper: ReturnType<typeof mount>, email: string, password: string) {
  await wrapper.get("#login-email").setValue(email);
  await wrapper.get("#login-password").setValue(password);
  await wrapper.get("form").trigger("submit");
  await flushPromises();
}

describe("LoginForm", () => {
  beforeEach(() => {
    login.mockReset();
  });

  it("checks the fields before signing in", async () => {
    const { wrapper } = await mountAt("/login");

    await fill(wrapper, "not-an-email", "");

    expect(wrapper.text()).toContain("Adresse électronique invalide.");
    expect(wrapper.text()).toContain("Mot de passe requis.");
    expect(login).not.toHaveBeenCalled();
  });

  it("opens the map once signed in", async () => {
    login.mockResolvedValue(undefined);
    const { wrapper, router } = await mountAt("/login");

    await fill(wrapper, "ana@example.fr", "motdepasse123");

    expect(login).toHaveBeenCalledWith("ana@example.fr", "motdepasse123");
    expect(router.currentRoute.value.name).toBe("home");
  });

  it("goes back to the invitation it was opened for", async () => {
    login.mockResolvedValue(undefined);
    const { wrapper, router } = await mountAt("/login?redirect=%2F%3Finvitation%3Dabc");

    await fill(wrapper, "ana@example.fr", "motdepasse123");

    expect(router.currentRoute.value.fullPath).toBe("/?invitation=abc");
  });

  it("keeps the way back when going to the sign-up", async () => {
    const { wrapper } = await mountAt("/login?redirect=%2F%3Finvitation%3Dabc");

    const link = wrapper.findAll("a").find((anchor) => anchor.text() === "Créer un compte");
    expect(link?.attributes("href")).toBe("/register?redirect=/?invitation=abc");
  });

  it("says when the email or the password is wrong", async () => {
    login.mockRejectedValue(new ApiError(401));
    const { wrapper } = await mountAt("/login");

    await fill(wrapper, "ana@example.fr", "wrong-password");

    expect(wrapper.get('[role="alert"]').text()).toBe("Adresse électronique ou mot de passe incorrect.");
  });

  it("links to the forgotten password page", async () => {
    const { wrapper } = await mountAt("/login");

    const link = wrapper.findAll("a").find((anchor) => anchor.text() === "Mot de passe oublié ?");
    expect(link?.attributes("href")).toBe("/forgot-password");
  });
});
