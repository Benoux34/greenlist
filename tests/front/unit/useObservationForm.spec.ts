import { beforeEach, describe, expect, it, vi } from "vitest";
import { nextTick } from "vue";
import {
  addObservationPhoto,
  createObservation,
  removeObservationPhoto,
  updateObservation,
} from "@/features/observations/api";
import { useObservationForm } from "@/features/observations/composables/useObservationForm";
import { ApiError } from "@/shared/api/http";
import { observation } from "../fixtures";

vi.mock("@/features/observations/api", () => ({
  createObservation: vi.fn(),
  updateObservation: vi.fn(),
  addObservationPhoto: vi.fn(),
  removeObservationPhoto: vi.fn(),
}));

const here = { latitude: 45.76, longitude: 4.83 };
const fox = { inatId: 42069, scientificName: "Vulpes vulpes", vernacularName: "Renard roux", category: "fauna" as const, photoUrl: null };

describe("useObservationForm", () => {
  beforeEach(() => {
    vi.mocked(updateObservation).mockImplementation(async (id, input) => observation({ id, count: input.count }));
  });

  it("explains what is missing before sending anything", async () => {
    const { form, errors, submit } = useObservationForm(() => here, null);
    form.count = "zéro";
    await nextTick();

    await expect(submit()).resolves.toBeNull();

    expect(errors.value).toEqual({ species: "Choisissez une espèce.", count: "Entier positif." });
    expect(createObservation).not.toHaveBeenCalled();
  });

  it("forgets an error as soon as the field changes", async () => {
    const { form, errors, submit } = useObservationForm(() => here, null);
    await submit();

    form.species = fox;
    await nextTick();

    expect(errors.value.species).toBeUndefined();
  });

  it("creates the observation where the map was touched, then sends the photos", async () => {
    const created = observation({ id: "new" });
    vi.mocked(createObservation).mockResolvedValue(created);
    vi.mocked(addObservationPhoto).mockResolvedValue({ ...created, photos: ["p1.jpg"] });
    const { form, saved, submit } = useObservationForm(() => here, null);
    form.species = fox;
    form.observedAt = "2026-06-01T10:30";
    form.count = "2";
    form.notes = "  ";
    const photo = new Blob(["x"], { type: "image/jpeg" });
    form.photos = [photo];

    const result = await submit();

    expect(createObservation).toHaveBeenCalledWith({
      inatId: 42069,
      observedAt: new Date("2026-06-01T10:30").toISOString(),
      latitude: 45.76,
      longitude: 4.83,
      count: 2,
      notes: null,
    });
    expect(addObservationPhoto).toHaveBeenCalledWith("new", photo);
    expect(result?.photos).toEqual(["p1.jpg"]);
    expect(saved.value).toEqual(result);
    expect(form.photos).toEqual([]);
    expect(form.kept).toEqual(["p1.jpg"]);
  });

  it("never creates the observation twice when a photo fails", async () => {
    const created = observation({ id: "new" });
    vi.mocked(createObservation).mockResolvedValue(created);
    vi.mocked(addObservationPhoto)
      .mockRejectedValueOnce(new Error("network"))
      .mockResolvedValueOnce({ ...created, photos: ["p1.jpg"] });
    const { form, formError, submit } = useObservationForm(() => here, null);
    form.species = fox;
    form.photos = [new Blob(["x"])];

    expect(await submit()).toBeNull();
    expect(formError.value).toBe(
      "L'observation est enregistrée, mais les photos n'ont pas toutes pu être mises à jour. Réessayez.",
    );

    expect(await submit()).not.toBeNull();
    expect(createObservation).toHaveBeenCalledOnce();
    expect(updateObservation).toHaveBeenCalledWith("new", expect.objectContaining({ inatId: 42069 }));
    expect(addObservationPhoto).toHaveBeenCalledTimes(2);
  });

  it("says when iNaturalist is down", async () => {
    vi.mocked(createObservation).mockRejectedValue(new ApiError(503));
    const { form, formError, submit } = useObservationForm(() => here, null);
    form.species = fox;

    await submit();

    expect(formError.value).toBe(
      "Le service d'identification des espèces ne répond pas. Réessayez dans un instant.",
    );
  });

  it("starts from the observation being edited", () => {
    const edited = observation({ count: 3, notes: "Au bord du lac", photos: ["a.jpg", "b.jpg"], observedAt: "2026-06-01T08:30:00Z" });

    const { form, saved } = useObservationForm(() => here, edited);

    expect(form.species).toEqual({ ...edited.species, photoUrl: null });
    expect(form.count).toBe("3");
    expect(form.notes).toBe("Au bord du lac");
    expect(form.kept).toEqual(["a.jpg", "b.jpg"]);
    expect(new Date(form.observedAt).toISOString()).toBe("2026-06-01T08:30:00.000Z");
    expect(saved.value).toBe(edited);
  });

  it("updates the observation and removes the photos taken out", async () => {
    const edited = observation({ id: "obs", photos: ["a.jpg", "b.jpg"] });
    vi.mocked(updateObservation).mockResolvedValue(edited);
    vi.mocked(removeObservationPhoto).mockResolvedValue({ ...edited, photos: ["b.jpg"] });
    const { form, submit } = useObservationForm(() => ({ latitude: 1, longitude: 2 }), edited);
    form.kept = ["b.jpg"];

    const result = await submit();

    expect(updateObservation).toHaveBeenCalledWith("obs", expect.objectContaining({ latitude: 1, longitude: 2 }));
    expect(removeObservationPhoto).toHaveBeenCalledExactlyOnceWith("obs", "a.jpg");
    expect(createObservation).not.toHaveBeenCalled();
    expect(result?.photos).toEqual(["b.jpg"]);
  });
});
