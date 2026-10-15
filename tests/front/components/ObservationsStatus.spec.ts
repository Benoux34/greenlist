import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";
import ObservationsStatus from "@/features/observations/components/ObservationsStatus.vue";

const props = { total: 3, matching: 3, failed: false, drafting: false };

describe("ObservationsStatus", () => {
  it("says the observations are loading", () => {
    expect(mount(ObservationsStatus, { props: { ...props, total: null } }).text()).toBe(
      "Chargement de vos observations…",
    );
  });

  it("offers to retry after a failure", async () => {
    const wrapper = mount(ObservationsStatus, { props: { ...props, total: null, failed: true } });

    expect(wrapper.text()).toContain("La carte n'a pas pu être chargée");
    await wrapper.get("button").trigger("click");
    expect(wrapper.emitted("retry")).toHaveLength(1);
  });

  it("invites to add a first observation, except while adding one", async () => {
    const empty = mount(ObservationsStatus, { props: { ...props, total: 0, matching: 0 } });
    expect(empty.text()).toContain("Votre carte est vide");
    await empty.get("button").trigger("click");
    expect(empty.emitted("create")).toHaveLength(1);

    expect(mount(ObservationsStatus, { props: { ...props, total: 0, matching: 0, drafting: true } }).text()).toBe("");
  });

  it("says when the filters hide everything", () => {
    expect(mount(ObservationsStatus, { props: { ...props, matching: 0 } }).text()).toBe(
      "Aucune observation ne correspond.",
    );
    expect(mount(ObservationsStatus, { props }).text()).toBe("");
  });
});
