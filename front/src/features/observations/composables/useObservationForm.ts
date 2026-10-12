import { nextTick, reactive, ref, watch } from "vue";
import { useRouter } from "vue-router";
import { ApiError } from "@/shared/api/http";
import { useGeolocation } from "@/shared/composables/useGeolocation";
import { fieldErrors, type FieldErrors } from "@/shared/utils/fieldErrors";
import type { SpeciesSuggestion } from "@/features/species/entities";
import type { Coordinates } from "@/features/map/entities";
import { addObservationPhoto, createObservation } from "../api";
import type { Observation } from "../entities";
import { observationSchema } from "../schemas";

function localNow(): string {
  const now = new Date();
  now.setMinutes(now.getMinutes() - now.getTimezoneOffset());

  return now.toISOString().slice(0, 16);
}

function useObservationForm() {
  const router = useRouter();
  const { isLocating, error: locationError, locate } = useGeolocation();

  const latestMoment = localNow();

  const form = reactive({
    species: null as SpeciesSuggestion | null,
    observedAt: latestMoment,
    position: null as Coordinates | null,
    photos: [] as Blob[],
    count: "",
    notes: "",
  });
  const errors = ref<FieldErrors<typeof form>>({});
  const formError = ref<string | null>(null);
  const isSubmitting = ref(false);

  let created: Observation | null = null;

  for (const field of Object.keys(form) as (keyof typeof form)[])
    watch(
      () => form[field],
      () => {
        delete errors.value[field];
      },
    );

  async function useMyPosition(): Promise<void> {
    const coordinates = await locate();
    if (coordinates === null) return;

    form.position = {
      latitude: coordinates.latitude,
      longitude: coordinates.longitude,
    };
  }

  async function submit(): Promise<void> {
    formError.value = null;
    const parsed = observationSchema.safeParse(form);
    errors.value = parsed.success ? {} : fieldErrors(parsed.error);

    if (!parsed.success) {
      await nextTick();
      document
        .querySelector('[aria-invalid="true"]')
        ?.scrollIntoView({ block: "center", behavior: "smooth" });
      return;
    }

    isSubmitting.value = true;
    try {
      created ??= await createObservation({
        inatId: parsed.data.species.inatId,
        observedAt: parsed.data.observedAt.toISOString(),
        latitude: parsed.data.position.latitude,
        longitude: parsed.data.position.longitude,
        count: parsed.data.count,
        notes: parsed.data.notes === "" ? null : parsed.data.notes,
      });

      for (const photo of [...form.photos]) {
        await addObservationPhoto(created.id, photo);
        form.photos = form.photos.filter((other) => other !== photo);
      }

      await router.push({ name: "observations" });
    } catch (error) {
      formError.value =
        created !== null
          ? "L'observation est enregistrée, mais une photo n'a pas pu être envoyée. Réessayez."
          : error instanceof ApiError && error.status === 503
            ? "Le service d'identification des espèces ne répond pas. Réessayez dans un instant."
            : "L'observation n'a pas pu être enregistrée. Réessayez.";
    } finally {
      isSubmitting.value = false;
    }
  }

  return {
    form,
    errors,
    formError,
    isSubmitting,
    isLocating,
    locationError,
    latestMoment,
    useMyPosition,
    submit,
  };
}

export { useObservationForm };
