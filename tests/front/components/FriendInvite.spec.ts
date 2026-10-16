import { flushPromises, mount } from "@vue/test-utils";
import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import FriendInvite from "@/features/friends/components/FriendInvite.vue";
import { createInvitation } from "@/features/friends/api";

vi.mock("@/features/friends/api", async (importOriginal) => ({
  ...(await importOriginal<typeof import("@/features/friends/api")>()),
  createInvitation: vi.fn(),
}));

const TOKEN = "9f2c".repeat(8);
const writeText = vi.fn();

const button = (wrapper: ReturnType<typeof mount>, label: string) =>
  wrapper.findAll("button").find((candidate) => candidate.text() === label);

async function invite(wrapper: ReturnType<typeof mount>) {
  await button(wrapper, "Inviter un ami")!.trigger("click");
  await flushPromises();
}

describe("FriendInvite", () => {
  beforeEach(() => {
    Object.defineProperty(navigator, "clipboard", { value: { writeText }, configurable: true });
  });

  afterEach(() => {
    Reflect.deleteProperty(navigator, "share");
  });

  it("creates a link to send and copies it", async () => {
    vi.mocked(createInvitation).mockResolvedValue({ token: TOKEN, expiresAt: "2026-10-23T12:00:00+00:00" });
    writeText.mockResolvedValue(undefined);
    const wrapper = mount(FriendInvite);

    await invite(wrapper);
    await button(wrapper, "Copier le lien")!.trigger("click");
    await flushPromises();

    const link = `${window.location.origin}/invitation/${TOKEN}`;
    expect(wrapper.get<HTMLInputElement>("#invitation-link").element.value).toBe(link);
    expect(writeText).toHaveBeenCalledWith(link);
    expect(wrapper.get('[role="status"]').text()).toBe("Lien copié.");
  });

  it("asks to copy by hand when the clipboard refuses", async () => {
    vi.mocked(createInvitation).mockResolvedValue({ token: TOKEN, expiresAt: "2026-10-23T12:00:00+00:00" });
    writeText.mockRejectedValue(new Error("denied"));
    const wrapper = mount(FriendInvite);

    await invite(wrapper);
    await button(wrapper, "Copier le lien")!.trigger("click");
    await flushPromises();

    expect(wrapper.get('[role="status"]').text()).toBe("Copie impossible : sélectionnez le lien.");
  });

  it("opens the share sheet where the device has one", async () => {
    vi.mocked(createInvitation).mockResolvedValue({ token: TOKEN, expiresAt: "2026-10-23T12:00:00+00:00" });
    const share = vi.fn().mockRejectedValue(new DOMException("closed", "AbortError"));
    Object.defineProperty(navigator, "share", { value: share, configurable: true });
    const wrapper = mount(FriendInvite);

    await invite(wrapper);
    await button(wrapper, "Partager")!.trigger("click");
    await flushPromises();

    expect(share).toHaveBeenCalledWith(expect.objectContaining({ url: `${window.location.origin}/invitation/${TOKEN}` }));
    expect(wrapper.find('[role="alert"]').exists()).toBe(false);
  });

  it("offers no share button elsewhere", async () => {
    vi.mocked(createInvitation).mockResolvedValue({ token: TOKEN, expiresAt: "2026-10-23T12:00:00+00:00" });
    const wrapper = mount(FriendInvite);

    await invite(wrapper);

    expect(button(wrapper, "Partager")).toBeUndefined();
  });

  it("says when the link cannot be created", async () => {
    vi.mocked(createInvitation).mockRejectedValue(new Error("down"));
    const wrapper = mount(FriendInvite);

    await invite(wrapper);

    expect(wrapper.get('[role="alert"]').text()).toBe("Le lien n'a pas pu être créé. Réessayez.");
    expect(wrapper.find("#invitation-link").exists()).toBe(false);
  });
});
