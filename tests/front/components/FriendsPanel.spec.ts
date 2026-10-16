import { flushPromises, mount } from "@vue/test-utils";
import { afterEach, describe, expect, it, vi } from "vitest";
import FriendsPanel from "@/features/friends/components/FriendsPanel.vue";
import { fetchFriends, removeFriend } from "@/features/friends/api";

vi.mock("@/features/friends/api", () => ({ fetchFriends: vi.fn(), removeFriend: vi.fn() }));

const leo = { id: "leo", firstName: "Léo", since: "2026-10-16T12:00:00+00:00" };

async function mountPanel() {
  const wrapper = mount(FriendsPanel, { attachTo: document.body, global: { stubs: { FriendInvite: true } } });
  await flushPromises();

  return wrapper;
}

const button = (wrapper: Awaited<ReturnType<typeof mountPanel>>, label: string) =>
  wrapper.findAll("button").find((candidate) => candidate.text() === label || candidate.attributes("aria-label") === label)!;

describe("FriendsPanel", () => {
  afterEach(() => {
    document.body.innerHTML = "";
  });

  it("lists the friends and since when", async () => {
    vi.mocked(fetchFriends).mockResolvedValue([leo]);

    const wrapper = await mountPanel();

    expect(wrapper.get("li").text()).toContain("Léo");
    expect(wrapper.get("li").text()).toContain("Amis depuis le 16 octobre 2026");
  });

  it("says when there is nobody yet", async () => {
    vi.mocked(fetchFriends).mockResolvedValue([]);

    const wrapper = await mountPanel();

    expect(wrapper.text()).toContain("Vous n'avez pas encore d'amis.");
  });

  it("offers to try again when the friends cannot be loaded", async () => {
    vi.mocked(fetchFriends).mockRejectedValueOnce(new Error("down")).mockResolvedValueOnce([leo]);
    const wrapper = await mountPanel();
    expect(wrapper.get('[role="alert"]').text()).toContain("Vos amis n'ont pas pu être chargés.");

    await button(wrapper, "Réessayer").trigger("click");
    await flushPromises();

    expect(wrapper.get("li").text()).toContain("Léo");
  });

  it("removes a friend once confirmed", async () => {
    vi.mocked(fetchFriends).mockResolvedValue([leo]);
    vi.mocked(removeFriend).mockResolvedValue(undefined);
    const wrapper = await mountPanel();

    await button(wrapper, "Retirer Léo de mes amis").trigger("click");
    expect(wrapper.get<HTMLDialogElement>("dialog").element.open).toBe(true);
    expect(wrapper.get("dialog").text()).toContain("Retirer Léo de vos amis ?");
    await button(wrapper, "Retirer").trigger("click");
    await flushPromises();

    expect(removeFriend).toHaveBeenCalledWith("leo");
    expect(wrapper.find("dialog").exists()).toBe(false);
    expect(wrapper.text()).toContain("Vous n'avez pas encore d'amis.");
  });

  it("keeps the friend when the removal fails", async () => {
    vi.mocked(fetchFriends).mockResolvedValue([leo]);
    vi.mocked(removeFriend).mockRejectedValue(new Error("down"));
    const wrapper = await mountPanel();

    await button(wrapper, "Retirer Léo de mes amis").trigger("click");
    await button(wrapper, "Retirer").trigger("click");
    await flushPromises();

    expect(wrapper.get("dialog [role='alert']").text()).toBe("Le retrait a échoué. Réessayez.");
    expect(wrapper.get("li").text()).toContain("Léo");
  });

  it("closes from its header", async () => {
    vi.mocked(fetchFriends).mockResolvedValue([]);
    const wrapper = await mountPanel();

    await button(wrapper, "Fermer mes amis").trigger("click");

    expect(wrapper.emitted("close")).toHaveLength(1);
  });
});
