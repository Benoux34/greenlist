import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";
import ObservationFilters from "@/features/observations/components/ObservationFilters.vue";
import { DEFAULT_FILTERS } from "@/features/observations/filters";

describe("ObservationFilters", () => {
  it("changes one field and keeps the others", async () => {
    const wrapper = mount(ObservationFilters, { props: { modelValue: DEFAULT_FILTERS } });

    await wrapper.get('input[type="search"]').setValue("renard");
    await wrapper.get("button[aria-pressed]:not([aria-pressed='true'])").trigger("click");

    expect(wrapper.emitted("update:modelValue")).toEqual([
      [{ ...DEFAULT_FILTERS, search: "renard" }],
      [{ ...DEFAULT_FILTERS, search: "renard", category: "fauna" }],
    ]);
  });

  it("toggles the photo filter", async () => {
    const wrapper = mount(ObservationFilters, { props: { modelValue: { ...DEFAULT_FILTERS, withPhotos: true } } });
    const toggle = wrapper.findAll("button").find((button) => button.text() === "Avec photo")!;

    expect(toggle.attributes("aria-pressed")).toBe("true");
    await toggle.trigger("click");

    expect(wrapper.emitted("update:modelValue")).toEqual([[{ ...DEFAULT_FILTERS, withPhotos: false }]]);
  });

  it("asks for dates only for a custom period", async () => {
    const wrapper = mount(ObservationFilters, { props: { modelValue: DEFAULT_FILTERS } });
    expect(wrapper.findAll('input[type="date"]')).toHaveLength(0);

    await wrapper.get("select").setValue("custom");
    expect(wrapper.emitted("update:modelValue")).toEqual([[{ ...DEFAULT_FILTERS, period: "custom" }]]);

    await wrapper.setProps({ modelValue: { ...DEFAULT_FILTERS, period: "custom" } });
    expect(wrapper.findAll('input[type="date"]')).toHaveLength(2);
  });

});
