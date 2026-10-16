import { flushPromises, mount } from "@vue/test-utils";
import { afterEach, describe, expect, it, vi } from "vitest";
import InvitationDialog from "@/features/friends/components/InvitationDialog.vue";
import { acceptInvitation, fetchInvitation } from "@/features/friends/api";

vi.mock("@/features/friends/api", () => ({ fetchInvitation: vi.fn(), acceptInvitation: vi.fn() }));
vi.mock("@/features/auth/store", () => ({ useAuthStore: () => ({ user: { id: "me" } }) }));

const TOKEN = "9f2c".repeat(8);
const fromAna = { inviter: { id: "ana", firstName: "Ana" }, expiresAt: "2026-10-23T12:00:00+00:00" };

async function mountDialog() {
  const wrapper = mount(InvitationDialog, { props: { token: TOKEN }, attachTo: document.body });
  await flushPromises();

  return wrapper;
}

const button = (wrapper: Awaited<ReturnType<typeof mountDialog>>, label: string) =>
  wrapper.findAll("button").find((candidate) => candidate.text() === label);

describe("InvitationDialog", () => {
  afterEach(() => {
    document.body.innerHTML = "";
  });

  it("names who invites and accepts", async () => {
    vi.mocked(fetchInvitation).mockResolvedValue(fromAna);
    vi.mocked(acceptInvitation).mockResolvedValue(undefined);
    const wrapper = await mountDialog();

    expect(wrapper.get<HTMLDialogElement>("dialog").element.open).toBe(true);
    expect(wrapper.get("h2").text()).toBe("Ana vous invite");
    await button(wrapper, "Accepter")!.trigger("click");
    await flushPromises();

    expect(fetchInvitation).toHaveBeenCalledWith(TOKEN);
    expect(acceptInvitation).toHaveBeenCalledWith(TOKEN);
    expect(wrapper.emitted("accepted")).toHaveLength(1);
  });

  it("says when accepting fails", async () => {
    vi.mocked(fetchInvitation).mockResolvedValue(fromAna);
    vi.mocked(acceptInvitation).mockRejectedValue(new Error("gone"));
    const wrapper = await mountDialog();

    await button(wrapper, "Accepter")!.trigger("click");
    await flushPromises();

    expect(wrapper.get('[role="alert"]').text()).toBe("L'invitation n'a pas pu être acceptée. Réessayez.");
    expect(wrapper.emitted("accepted")).toBeUndefined();
  });

  it("says when the link no longer works", async () => {
    vi.mocked(fetchInvitation).mockRejectedValue(new Error("not found"));
    const wrapper = await mountDialog();

    expect(wrapper.get("h2").text()).toBe("Lien plus valable");
    expect(button(wrapper, "Accepter")).toBeUndefined();
  });

  it("does not offer to accept one's own link", async () => {
    vi.mocked(fetchInvitation).mockResolvedValue({ ...fromAna, inviter: { id: "me", firstName: "Moi" } });
    const wrapper = await mountDialog();

    expect(wrapper.get("h2").text()).toBe("C'est votre lien");
    expect(button(wrapper, "Accepter")).toBeUndefined();
  });

  it("can be ignored", async () => {
    vi.mocked(fetchInvitation).mockResolvedValue(fromAna);
    const wrapper = await mountDialog();

    await button(wrapper, "Ignorer")!.trigger("click");
    await wrapper.get("dialog").trigger("cancel");

    expect(wrapper.emitted("close")).toHaveLength(2);
    expect(acceptInvitation).not.toHaveBeenCalled();
  });
});
