import { flushPromises, mount, type VueWrapper } from "@vue/test-utils";
import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import SpeciesAutocomplete from "@/features/species/components/SpeciesAutocomplete.vue";
import { searchSpecies } from "@/features/species/api";
import type { SpeciesSuggestion } from "@/features/species/types";

vi.mock("@/features/species/api", () => ({ searchSpecies: vi.fn() }));

const fox: SpeciesSuggestion = { inatId: 42069, scientificName: "Vulpes vulpes", vernacularName: "Renard roux", category: "fauna", photoUrl: null, nearby: true };
const crab: SpeciesSuggestion = { inatId: 42087, scientificName: "Cerdocyon thous", vernacularName: "Renard crabier", category: "fauna", photoUrl: null, nearby: false };

function mountAutocomplete(near: { latitude: number; longitude: number } | null = null) {
  const wrapper: VueWrapper = mount(SpeciesAutocomplete, {
    props: {
      id: "species",
      near,
      modelValue: null as SpeciesSuggestion | null,
      "onUpdate:modelValue": (value: SpeciesSuggestion | null) => wrapper.setProps({ modelValue: value }),
    },
    attachTo: document.body,
  });

  return wrapper;
}

async function type(wrapper: VueWrapper, text: string) {
  const input = wrapper.get("input");
  await input.trigger("focus");
  await input.setValue(text);
  vi.advanceTimersByTime(300);
  await flushPromises();
}

describe("SpeciesAutocomplete", () => {
  beforeEach(() => {
    vi.useFakeTimers();
    vi.mocked(searchSpecies).mockResolvedValue([fox, crab]);
  });

  afterEach(() => {
    vi.useRealTimers();
    document.body.innerHTML = "";
  });

  it("asks for two letters first", async () => {
    const wrapper = mountAutocomplete();

    await wrapper.get("input").trigger("focus");

    expect(wrapper.get('[role="status"]').text()).toBe("Tapez au moins 2 lettres.");
  });

  it("suggests species and picks one with the keyboard", async () => {
    const wrapper = mountAutocomplete();
    await type(wrapper, "renard");

    expect(wrapper.findAll('[role="option"]').map((option) => option.text())).toEqual([
      expect.stringContaining("Renard roux"),
      expect.stringContaining("Renard crabier"),
    ]);

    const input = wrapper.get("input");
    await input.trigger("keydown", { key: "ArrowDown" });
    await input.trigger("keydown", { key: "ArrowDown" });
    expect(input.attributes("aria-activedescendant")).toMatch(/-1$/);
    await input.trigger("keydown", { key: "Enter" });

    expect(wrapper.emitted("update:modelValue")).toEqual([[crab]]);
    expect(wrapper.text()).toContain("Renard crabier");
    expect(wrapper.find("input").exists()).toBe(false);
  });

  it("searches around the point and flags the species seen nearby", async () => {
    const wrapper = mountAutocomplete({ latitude: 39.6, longitude: -9.07 });

    await type(wrapper, "renard");

    expect(searchSpecies).toHaveBeenLastCalledWith("renard", null, { latitude: 39.6, longitude: -9.07 }, expect.any(AbortSignal));
    const [nearby, elsewhere] = wrapper.findAll('[role="option"]');
    expect(nearby!.text()).toContain("Près d'ici");
    expect(elsewhere!.text()).not.toContain("Près d'ici");
  });

  it("narrows the search to a category", async () => {
    const wrapper = mountAutocomplete();
    await wrapper.findAll("button").find((button) => button.text() === "Flore")!.trigger("click");

    await type(wrapper, "chene");

    expect(searchSpecies).toHaveBeenLastCalledWith("chene", "flora", null, expect.any(AbortSignal));
  });

  it("lets me change the species", async () => {
    const wrapper = mountAutocomplete();
    await wrapper.setProps({ modelValue: fox });

    await wrapper.findAll("button").find((button) => button.text() === "Changer")!.trigger("click");

    expect(wrapper.emitted("update:modelValue")?.at(-1)).toEqual([null]);
    expect(document.activeElement).toBe(wrapper.get("input").element);
  });

  it("says when the search is unavailable", async () => {
    vi.mocked(searchSpecies).mockRejectedValue(new Error("down"));
    const wrapper = mountAutocomplete();

    await type(wrapper, "renard");

    expect(wrapper.get('[role="status"]').text()).toBe("La recherche d'espèces est indisponible pour le moment.");
  });
});
