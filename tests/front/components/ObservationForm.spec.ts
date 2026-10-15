import { flushPromises, mount } from "@vue/test-utils";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { fetchPlaceName } from "@/features/map/api";
import ObservationForm from "@/features/observations/components/ObservationForm.vue";
import { updateObservation } from "@/features/observations/api";
import { observation } from "../fixtures";

vi.mock("@/features/map/api", () => ({ fetchPlaceName: vi.fn() }));
vi.mock("@/features/observations/api", () => ({
  createObservation: vi.fn(),
  updateObservation: vi.fn(),
  addObservationPhoto: vi.fn(),
  removeObservationPhoto: vi.fn(),
  observationPhotoUrl: (id: string, name: string) => `/api/observations/${id}/photos/${name}`,
}));

const here = { latitude: 45.764, longitude: 4.8357 };

describe("ObservationForm", () => {
  beforeEach(() => {
    vi.mocked(fetchPlaceName).mockResolvedValue({ label: "Lyon, Rhône, France" });
  });

  it("names the place that was touched on the map", async () => {
    const wrapper = mount(ObservationForm, { props: { position: here, observation: null } });
    expect(wrapper.text()).toContain("Recherche du nom du lieu…");

    await flushPromises();

    expect(fetchPlaceName).toHaveBeenCalledWith(here);
    expect(wrapper.text()).toContain("Lyon, Rhône, France");
    expect(wrapper.text()).toContain("45,764° N, 4,8357° E");
  });

  it("falls back to the coordinates when the place has no name", async () => {
    vi.mocked(fetchPlaceName).mockRejectedValue(new Error("down"));
    const wrapper = mount(ObservationForm, { props: { position: here, observation: null } });
    await flushPromises();

    expect(wrapper.get("section p").text()).toBe("45,764° N, 4,8357° E");
  });

  it("shows what is missing instead of saving", async () => {
    const wrapper = mount(ObservationForm, { props: { position: here, observation: null } });

    await wrapper.get("form").trigger("submit");
    await flushPromises();

    expect(wrapper.get("h2").text()).toBe("Nouvelle observation");
    expect(wrapper.text()).toContain("Choisissez une espèce.");
    expect(wrapper.emitted("saved")).toBeUndefined();
  });

  it("edits an observation and hands back the saved version", async () => {
    const fox = observation({ count: 2, notes: "Au bord du lac" });
    const saved = { ...fox, count: 5 };
    vi.mocked(updateObservation).mockResolvedValue(saved);
    const wrapper = mount(ObservationForm, { props: { position: here, observation: fox } });

    expect(wrapper.get("h2").text()).toBe("Modifier l'observation");
    expect(wrapper.get<HTMLInputElement>("#observation-count").element.value).toBe("2");
    expect(wrapper.get<HTMLTextAreaElement>("#observation-notes").element.value).toBe("Au bord du lac");

    await wrapper.get("#observation-count").setValue("5");
    await wrapper.get("form").trigger("submit");
    await flushPromises();

    expect(updateObservation).toHaveBeenCalledWith(fox.id, expect.objectContaining({ count: 5, latitude: here.latitude }));
    expect(wrapper.emitted("saved")).toEqual([[saved]]);
  });

  it("closes without saving anything when nothing was sent", async () => {
    const wrapper = mount(ObservationForm, { props: { position: here, observation: observation() } });

    await wrapper.get('[aria-label="Fermer le formulaire"]').trigger("click");

    expect(wrapper.emitted("cancel")).toHaveLength(1);
    expect(updateObservation).not.toHaveBeenCalled();
  });
});
