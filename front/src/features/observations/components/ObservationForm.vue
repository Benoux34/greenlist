<script setup lang="ts">
import { computed } from "vue";
import BaseButton from "@/shared/components/BaseButton.vue";
import BaseInput from "@/shared/components/BaseInput.vue";
import BaseTextarea from "@/shared/components/BaseTextarea.vue";
import FormField from "@/shared/components/FormField.vue";
import { useAsyncData } from "@/shared/composables/useAsyncData";
import { fetchPlaceName } from "@/features/map/api";
import type { Coordinates } from "@/features/map/types";
import SpeciesAutocomplete from "@/features/species/components/SpeciesAutocomplete.vue";
import { useObservationForm } from "../composables/useObservationForm";
import { formatCoordinates } from "../format";
import type { Observation } from "../types";
import PhotoPicker from "./PhotoPicker.vue";

const { position, observation } = defineProps<{
  position: Coordinates;
  observation: Observation | null;
}>();

const emit = defineEmits<{ saved: [observation: Observation]; cancel: [] }>();

const { form, errors, formError, isSubmitting, latestMoment, saved, submit } =
  useObservationForm(() => position, observation);

const { data: place, isLoading: isNaming } = useAsyncData(
  () => fetchPlaceName(position),
  [() => position],
);
const placeName = computed(() => (isNaming.value ? null : place.value?.label));

async function save(): Promise<void> {
  const result = await submit();
  if (result !== null) emit("saved", result);
}

function close(): void {
  if (saved.value !== null && saved.value !== observation)
    emit("saved", saved.value);
  else emit("cancel");
}
</script>

<template>
  <div
    class="absolute inset-x-3 bottom-[max(0.75rem,env(safe-area-inset-bottom))] z-30 mx-auto flex max-h-[60dvh] max-w-md flex-col lg:inset-x-auto lg:top-24 lg:right-6 lg:bottom-6 lg:max-h-none lg:w-96 lg:max-w-none"
  >
    <form
      novalidate
      class="flex min-h-0 flex-col overflow-hidden rounded-3xl bg-surface shadow-xl ring-1 ring-ink/5"
      aria-labelledby="new-observation-title"
      @submit.prevent="save"
    >
      <header
        class="flex items-center justify-between gap-3 border-b border-paper-border py-3 pr-3 pl-6"
      >
        <h2
          id="new-observation-title"
          class="font-serif text-xl font-semibold tracking-tight"
        >
          {{ observation ? "Modifier l'observation" : "Nouvelle observation" }}
        </h2>
        <button
          type="button"
          class="flex size-10 shrink-0 cursor-pointer items-center justify-center rounded-full text-ink-muted transition-colors hover:bg-paper-muted hover:text-ink"
          aria-label="Fermer le formulaire"
          @click="close"
        >
          <svg
            viewBox="0 0 24 24"
            class="size-4"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            aria-hidden="true"
          >
            <path d="M6 6l12 12M18 6L6 18" />
          </svg>
        </button>
      </header>

      <div class="min-h-0 space-y-5 overflow-y-auto px-6 py-5">
        <section aria-labelledby="new-observation-place">
          <h3
            id="new-observation-place"
            class="mb-2 text-xs font-medium tracking-wider text-ink-muted uppercase"
          >
            Lieu
          </h3>
          <p class="text-sm font-medium">
            {{ placeName ?? formatCoordinates(position) }}
          </p>
          <p class="text-xs text-ink-muted">
            <template v-if="isNaming">Recherche du nom du lieu…</template>
            <template v-else-if="placeName">
              {{ formatCoordinates(position) }}
            </template>
          </p>
          <p class="mt-1 text-xs text-ink-faint">
            Déplacez le repère ou choisissez un autre point sur la carte.
          </p>
        </section>

        <FormField id="observation-species" label="Espèce" :error="errors.species">
          <SpeciesAutocomplete
            id="observation-species"
            v-model="form.species"
            :near="position"
            :invalid="!!errors.species"
          />
        </FormField>

        <div class="grid grid-cols-[minmax(0,1fr)_6.5rem] items-start gap-3">
          <FormField
            id="observation-date"
            label="Date et heure"
            :error="errors.observedAt"
          >
            <BaseInput
              id="observation-date"
              v-model="form.observedAt"
              type="datetime-local"
              :max="latestMoment"
              :aria-invalid="!!errors.observedAt"
            />
          </FormField>

          <FormField id="observation-count" label="Nombre" :error="errors.count">
            <BaseInput
              id="observation-count"
              v-model="form.count"
              type="text"
              inputmode="numeric"
              placeholder="—"
              :aria-invalid="!!errors.count"
            />
          </FormField>
        </div>

        <FormField id="observation-notes" label="Notes" :error="errors.notes">
          <BaseTextarea
            id="observation-notes"
            v-model="form.notes"
            rows="3"
            placeholder="Comportement, habitat, météo…"
            :aria-invalid="!!errors.notes"
          />
        </FormField>

        <FormField id="observation-photos" label="Photos">
          <PhotoPicker
            id="observation-photos"
            v-model="form.photos"
            v-model:kept="form.kept"
            :observation-id="saved?.id ?? null"
            :max="3"
          />
        </FormField>
      </div>

      <footer class="border-t border-paper-border px-6 py-3">
        <p v-if="formError" role="alert" class="mb-3 text-sm text-status-cr">
          {{ formError }}
        </p>
        <div class="flex justify-end gap-2">
          <button
            type="button"
            class="h-11 cursor-pointer rounded-full px-4 text-sm font-medium text-ink-muted transition-colors hover:bg-paper-muted hover:text-ink"
            @click="close"
          >
            Annuler
          </button>
          <BaseButton type="submit" size="lg" :disabled="isSubmitting">
            {{ isSubmitting ? "Enregistrement…" : "Enregistrer" }}
          </BaseButton>
        </div>
      </footer>
    </form>
  </div>
</template>
