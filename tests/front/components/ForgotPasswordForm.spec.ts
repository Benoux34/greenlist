import { flushPromises, mount, type VueWrapper } from "@vue/test-utils";
import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { createMemoryHistory, createRouter } from "vue-router";
import { checkResetCode, requestPasswordReset, resetPassword } from "@/features/auth/api";
import ForgotPasswordForm from "@/features/auth/components/ForgotPasswordForm.vue";
import { ApiError } from "@/shared/api/http";

vi.mock("@/features/auth/api", () => ({
  requestPasswordReset: vi.fn(),
  checkResetCode: vi.fn(),
  resetPassword: vi.fn(),
}));

const page = { render: () => null };

async function mountFlow() {
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: "/login", name: "login", component: page },
      { path: "/forgot-password", name: "forgot-password", component: page },
    ],
  });
  await router.push("/forgot-password");

  return mount(ForgotPasswordForm, { global: { plugins: [router] }, attachTo: document.body });
}

async function submit(wrapper: VueWrapper, fields: Record<string, string>) {
  for (const [selector, value] of Object.entries(fields)) await wrapper.get(selector).setValue(value);
  await wrapper.get("form").trigger("submit");
  await flushPromises();
}

const button = (wrapper: VueWrapper, label: string) =>
  wrapper.findAll("button").find((candidate) => candidate.text() === label)!;

async function reachPasswordStep(wrapper: VueWrapper) {
  await submit(wrapper, { "#reset-email": "ana@example.fr" });
  await submit(wrapper, { "#reset-code": "a1b2c3" });
}

describe("ForgotPasswordForm", () => {
  beforeEach(() => {
    vi.mocked(requestPasswordReset).mockResolvedValue(undefined);
    vi.mocked(checkResetCode).mockResolvedValue(undefined);
    vi.mocked(resetPassword).mockResolvedValue(undefined);
  });

  afterEach(() => {
    document.body.innerHTML = "";
  });

  it("asks for a code, checks it, then saves the new password", async () => {
    const wrapper = await mountFlow();
    expect(wrapper.text()).toContain("Étape 1 sur 3");

    await submit(wrapper, { "#reset-email": "ana@example.fr" });
    expect(requestPasswordReset).toHaveBeenCalledWith("ana@example.fr");
    expect(wrapper.text()).toContain("Étape 2 sur 3");
    expect(document.activeElement).toBe(wrapper.get("#reset-code").element);

    await submit(wrapper, { "#reset-code": "a1b2c3" });
    expect(checkResetCode).toHaveBeenCalledWith("ana@example.fr", "A1B2C3");
    expect(wrapper.text()).toContain("Étape 3 sur 3");

    await submit(wrapper, { "#new-password": "nouveaumotdepasse", "#new-password-confirmation": "nouveaumotdepasse" });
    expect(resetPassword).toHaveBeenCalledWith("ana@example.fr", "A1B2C3", "nouveaumotdepasse");
    expect(wrapper.get('[role="status"]').text()).toContain("Mot de passe modifié");
    expect(wrapper.get('a[href="/login"]').text()).toBe("Se connecter");
  });

  it("checks the address before asking for a code", async () => {
    const wrapper = await mountFlow();

    await submit(wrapper, { "#reset-email": "not-an-email" });

    expect(wrapper.text()).toContain("Adresse électronique invalide.");
    expect(requestPasswordReset).not.toHaveBeenCalled();
  });

  it("says when the code could not be sent", async () => {
    vi.mocked(requestPasswordReset).mockRejectedValue(new ApiError(500));
    const wrapper = await mountFlow();

    await submit(wrapper, { "#reset-email": "ana@example.fr" });

    expect(wrapper.get('[role="alert"]').text()).toBe("Le code n'a pas pu être envoyé. Réessayez dans un instant.");
    expect(wrapper.text()).toContain("Étape 1 sur 3");
  });

  it("only sends a code of 6 letters or digits, and says when it is wrong", async () => {
    const wrapper = await mountFlow();
    await submit(wrapper, { "#reset-email": "ana@example.fr" });

    await submit(wrapper, { "#reset-code": "ABC-12" });
    expect(wrapper.text()).toContain("Le code fait 6 lettres ou chiffres.");
    expect(checkResetCode).not.toHaveBeenCalled();

    vi.mocked(checkResetCode).mockRejectedValue(new ApiError(422));
    await submit(wrapper, { "#reset-code": "ABC234" });
    expect(wrapper.text()).toContain("Code incorrect ou expiré.");
    expect(wrapper.text()).toContain("Étape 2 sur 3");
  });

  it("sends another code, or goes back to the address, prefilled", async () => {
    const wrapper = await mountFlow();
    await submit(wrapper, { "#reset-email": "ana@example.fr" });

    await button(wrapper, "renvoyez le code").trigger("click");
    await flushPromises();
    expect(requestPasswordReset).toHaveBeenCalledTimes(2);
    expect(wrapper.get('[role="status"]').text()).toBe("Un nouveau code vient de partir (un par minute au maximum).");

    await button(wrapper, "changez d'adresse").trigger("click");
    expect(wrapper.get<HTMLInputElement>("#reset-email").element.value).toBe("ana@example.fr");
  });

  it("needs the same password twice, of 8 characters at least", async () => {
    const wrapper = await mountFlow();
    await reachPasswordStep(wrapper);

    await submit(wrapper, { "#new-password": "court", "#new-password-confirmation": "court" });
    expect(wrapper.text()).toContain("Au moins 8 caractères.");

    await submit(wrapper, { "#new-password": "nouveaumotdepasse", "#new-password-confirmation": "autremotdepasse" });
    expect(wrapper.text()).toContain("Les deux mots de passe ne correspondent pas.");
    expect(resetPassword).not.toHaveBeenCalled();
  });

  it("offers to start over when the code has expired", async () => {
    vi.mocked(resetPassword).mockRejectedValue(new ApiError(422));
    const wrapper = await mountFlow();
    await reachPasswordStep(wrapper);

    await submit(wrapper, { "#new-password": "nouveaumotdepasse", "#new-password-confirmation": "nouveaumotdepasse" });
    expect(wrapper.get('[role="alert"]').text()).toContain("Ce code a expiré.");

    await button(wrapper, "Recommencer").trigger("click");
    expect(wrapper.text()).toContain("Étape 1 sur 3");
    expect(wrapper.get<HTMLInputElement>("#reset-email").element.value).toBe("ana@example.fr");
  });
});
