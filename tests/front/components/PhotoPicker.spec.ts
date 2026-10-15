import { flushPromises, mount, type VueWrapper } from "@vue/test-utils";
import { beforeEach, describe, expect, it, vi } from "vitest";
import PhotoPicker from "@/features/observations/components/PhotoPicker.vue";
import { compressImage } from "@/shared/utils/compressImage";

vi.mock("@/shared/utils/compressImage", () => ({ compressImage: vi.fn() }));

function mountPicker(kept: string[] = []) {
  const wrapper: VueWrapper = mount(PhotoPicker, {
    props: {
      id: "photos",
      max: 3,
      observationId: "obs",
      modelValue: [] as Blob[],
      "onUpdate:modelValue": (photos: Blob[]) => wrapper.setProps({ modelValue: photos }),
      kept,
      "onUpdate:kept": (names: string[]) => wrapper.setProps({ kept: names }),
    },
  });

  return wrapper;
}

async function choose(wrapper: VueWrapper, files: File[]) {
  const input = wrapper.get('input[type="file"]');
  Object.defineProperty(input.element, "files", { value: files, configurable: true });
  await input.trigger("change");
  await flushPromises();
}

const jpeg = (name: string) => new File(["x"], name, { type: "image/jpeg" });

describe("PhotoPicker", () => {
  beforeEach(() => {
    vi.mocked(compressImage).mockImplementation(async (file) => file);
    URL.createObjectURL = vi.fn((blob: Blob) => `blob:${(blob as File).name}`);
    URL.revokeObjectURL = vi.fn();
  });

  it("shows the photos already saved and lets me take one out", async () => {
    const wrapper = mountPicker(["a.jpg", "b.jpg"]);

    expect(wrapper.findAll("img").map((img) => img.attributes("src"))).toEqual([
      "/api/observations/obs/photos/a.jpg",
      "/api/observations/obs/photos/b.jpg",
    ]);

    await wrapper.get('[aria-label="Retirer la photo 1"]').trigger("click");

    expect(wrapper.emitted("update:kept")).toEqual([[["b.jpg"]]]);
  });

  it("adds compressed photos up to three in all", async () => {
    const wrapper = mountPicker(["a.jpg"]);

    await choose(wrapper, [jpeg("1.jpg"), jpeg("2.jpg"), jpeg("3.jpg")]);

    expect(compressImage).toHaveBeenCalledTimes(2);
    expect(wrapper.findAll("img")).toHaveLength(3);
    expect(wrapper.text()).toContain("3 photos au maximum.");
    expect(wrapper.find("label").exists()).toBe(false);
  });

  it("only takes JPG and PNG files", async () => {
    const wrapper = mountPicker();

    await choose(wrapper, [new File(["x"], "notes.pdf", { type: "application/pdf" })]);

    expect(compressImage).not.toHaveBeenCalled();
    expect(wrapper.get('[role="alert"]').text()).toBe("Seuls les fichiers JPG et PNG sont acceptés.");
  });

  it("says which photo could not be read", async () => {
    vi.mocked(compressImage).mockRejectedValue(new Error("corrupt"));
    const wrapper = mountPicker();

    await choose(wrapper, [jpeg("abime.jpg")]);

    expect(wrapper.get('[role="alert"]').text()).toBe("« abime.jpg » n'a pas pu être lue.");
  });
});
