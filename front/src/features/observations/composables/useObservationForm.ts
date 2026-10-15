import { nextTick, reactive, ref, shallowRef, watch } from "vue";
import { ApiError } from "@/shared/api/http";
import { fieldErrors, type FieldErrors } from "@/shared/utils/fieldErrors";
import type { Coordinates } from "@/features/map/types";
import type { SpeciesSuggestion } from "@/features/species/types";
import {
  addObservationPhoto,
  createObservation,
  removeObservationPhoto,
  updateObservation,
} from "../api";
import type { Observation } from "../types";
import { observationSchema } from "../schemas";

function localDateTime(date: Date): string {
  const local = new Date(date);
  local.setMinutes(local.getMinutes() - local.getTimezoneOffset());

  return local.toISOString().slice(0, 16);
}

const toSuggestion = ({ species }: Observation): SpeciesSuggestion => ({
  ...species,
  photoUrl: null,
});

function useObservationForm(
  position: () => Coordinates,
  editing: Observation | null,
) {
  const latestMoment = localDateTime(new Date());

  const form = reactive({
    species: editing && toSuggestion(editing),
    observedAt: editing
      ? localDateTime(new Date(editing.observedAt))
      : latestMoment,
    photos: [] as Blob[],
    kept: editing ? [...editing.photos] : [],
    count: editing?.count?.toString() ?? "",
    notes: editing?.notes ?? "",
  });
  const errors = ref<FieldErrors<typeof form>>({});
  const formError = ref<string | null>(null);
  const isSubmitting = ref(false);
  const saved = shallowRef(editing);

  for (const field of Object.keys(form) as (keyof typeof form)[])
    watch(
      () => form[field],
      () => {
        delete errors.value[field];
      },
    );

  async function submit(): Promise<Observation | null> {
    formError.value = null;
    const parsed = observationSchema.safeParse(form);
    errors.value = parsed.success ? {} : fieldErrors(parsed.error);

    if (!parsed.success) {
      await nextTick();
      document
        .querySelector('[aria-invalid="true"]')
        ?.scrollIntoView({ block: "center", behavior: "smooth" });
      return null;
    }

    const input = {
      inatId: parsed.data.species.inatId,
      observedAt: parsed.data.observedAt.toISOString(),
      latitude: position().latitude,
      longitude: position().longitude,
      count: parsed.data.count,
      notes: parsed.data.notes === "" ? null : parsed.data.notes,
    };

    isSubmitting.value = true;
    let current = saved.value;
    try {
      current =
        current === null
          ? await createObservation(input)
          : await updateObservation(current.id, input);
      saved.value = current;
    } catch (error) {
      formError.value =
        error instanceof ApiError && error.status === 503
          ? "Le service d'identification des espèces ne répond pas. Réessayez dans un instant."
          : "L'observation n'a pas pu être enregistrée. Réessayez.";
      isSubmitting.value = false;
      return null;
    }

    try {
      for (const name of current.photos.filter((name) => !form.kept.includes(name))) {
        current = await removeObservationPhoto(current.id, name);
        saved.value = current;
      }

      for (const photo of [...form.photos]) {
        const before = current.photos;
        current = await addObservationPhoto(current.id, photo);
        saved.value = current;
        form.photos = form.photos.filter((other) => other !== photo);
        form.kept = [
          ...form.kept,
          ...current.photos.filter((name) => !before.includes(name)),
        ];
      }

      return current;
    } catch {
      formError.value =
        "L'observation est enregistrée, mais les photos n'ont pas toutes pu être mises à jour. Réessayez.";
      return null;
    } finally {
      isSubmitting.value = false;
    }
  }

  return { form, errors, formError, isSubmitting, latestMoment, saved, submit };
}

export { useObservationForm };
