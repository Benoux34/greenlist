import { flushPromises, mount } from "@vue/test-utils";
import { afterEach, describe, expect, it, vi } from "vitest";
import ObservationDeleteDialog from "@/features/observations/components/ObservationDeleteDialog.vue";
import { deleteObservation } from "@/features/observations/api";
import { observation } from "../fixtures";

vi.mock("@/features/observations/api", () => ({ deleteObservation: vi.fn() }));

const fox = observation({ id: "abc", observedAt: "2026-10-15T12:00:00Z" });

function mountDialog() {
  return mount(ObservationDeleteDialog, { props: { observation: fox }, attachTo: document.body });
}

const button = (wrapper: ReturnType<typeof mountDialog>, label: string) =>
  wrapper.findAll("button").find((candidate) => candidate.text() === label)!;

describe("ObservationDeleteDialog", () => {
  afterEach(() => {
    document.body.innerHTML = "";
  });

  it("opens as a modal naming the observation", () => {
    const wrapper = mountDialog();

    expect(wrapper.get<HTMLDialogElement>("dialog").element.open).toBe(true);
    expect(wrapper.text()).toContain("« Renard roux » du 15 octobre 2026 sera supprimée avec ses photos.");
  });

  it("deletes the observation once confirmed", async () => {
    vi.mocked(deleteObservation).mockResolvedValue(undefined);
    const wrapper = mountDialog();

    await button(wrapper, "Supprimer").trigger("click");
    await flushPromises();

    expect(deleteObservation).toHaveBeenCalledWith("abc");
    expect(wrapper.emitted("deleted")).toEqual([[fox]]);
  });

  it("keeps the observation and says so when the deletion fails", async () => {
    vi.mocked(deleteObservation).mockRejectedValue(new Error("down"));
    const wrapper = mountDialog();

    await button(wrapper, "Supprimer").trigger("click");
    await flushPromises();

    expect(wrapper.get('[role="alert"]').text()).toBe("La suppression a échoué. Réessayez.");
    expect(wrapper.emitted("deleted")).toBeUndefined();
  });

  it("cancels with the button or Escape", async () => {
    const wrapper = mountDialog();

    await button(wrapper, "Annuler").trigger("click");
    await wrapper.get("dialog").trigger("cancel");

    expect(wrapper.emitted("cancel")).toHaveLength(2);
    expect(deleteObservation).not.toHaveBeenCalled();
  });
});
