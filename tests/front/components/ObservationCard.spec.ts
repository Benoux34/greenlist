import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";
import ObservationCard from "@/features/observations/components/ObservationCard.vue";
import { observation } from "../fixtures";

describe("ObservationCard", () => {
  const fox = observation({ id: "abc", observedAt: "2026-10-15T12:00:00Z", photos: ["one.jpg"] });

  it("shows the species, the date and the first photo", () => {
    const wrapper = mount(ObservationCard, { props: { observation: fox, selected: false } });

    expect(wrapper.text()).toContain("Renard roux");
    expect(wrapper.text()).toContain("15 octobre 2026");
    expect(wrapper.get("img").attributes("src")).toBe("/api/observations/abc/photos/one.jpg");
  });

  it("offers to edit and delete instead of a category dot", async () => {
    const wrapper = mount(ObservationCard, { props: { observation: fox, selected: false } });

    await wrapper.get('[aria-label="Modifier Renard roux (15 octobre 2026)"]').trigger("click");
    await wrapper.get('[aria-label="Supprimer Renard roux (15 octobre 2026)"]').trigger("click");

    expect(wrapper.emitted("edit")).toHaveLength(1);
    expect(wrapper.emitted("remove")).toHaveLength(1);
    expect(wrapper.emitted("select")).toBeUndefined();
    expect(wrapper.find(".size-2.rounded-full").exists()).toBe(false);
  });

  it("selects the observation and highlights it on the map", async () => {
    const wrapper = mount(ObservationCard, { props: { observation: fox, selected: true } });

    await wrapper.trigger("mouseenter");
    await wrapper.findAll("button")[0]!.trigger("click");
    await wrapper.trigger("mouseleave");

    expect(wrapper.emitted("select")).toHaveLength(1);
    expect(wrapper.emitted("highlight")).toEqual([[true], [false]]);
    expect(wrapper.classes()).toContain("bg-accent-soft");
  });

  it("falls back to a placeholder without photo", () => {
    const wrapper = mount(ObservationCard, { props: { observation: observation(), selected: false } });

    expect(wrapper.find("img").exists()).toBe(false);
  });
});
