import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";
import PasswordInput from "@/shared/components/PasswordInput.vue";

describe("PasswordInput", () => {
  it("hides the password until the eye is pressed", async () => {
    const wrapper = mount(PasswordInput, {
      props: { modelValue: "secret", id: "pw", autocomplete: "current-password" },
    });
    const input = wrapper.get("input");
    const eye = wrapper.get("button");

    expect(input.attributes()).toMatchObject({ id: "pw", type: "password", autocomplete: "current-password" });
    expect(eye.attributes()).toMatchObject({ type: "button", "aria-label": "Afficher le mot de passe", "aria-pressed": "false" });

    await eye.trigger("click");
    expect(input.attributes("type")).toBe("text");
    expect(eye.attributes()).toMatchObject({ "aria-label": "Masquer le mot de passe", "aria-pressed": "true" });

    await eye.trigger("click");
    expect(input.attributes("type")).toBe("password");
  });

  it("passes the typing to its model", async () => {
    const wrapper = mount(PasswordInput, { props: { modelValue: "" } });

    await wrapper.get("input").setValue("nouveau");

    expect(wrapper.emitted("update:modelValue")).toEqual([["nouveau"]]);
  });
});
