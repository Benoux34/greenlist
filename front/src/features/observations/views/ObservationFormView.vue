<script setup lang="ts">
import { computed } from "vue";
import { RouterLink } from "vue-router";
import BaseButton from "@/shared/components/BaseButton.vue";
import BaseInput from "@/shared/components/BaseInput.vue";
import BaseTextarea from "@/shared/components/BaseTextarea.vue";
import FormField from "@/shared/components/FormField.vue";
import { CATEGORIES } from "@/features/species/categories";
import SpeciesAutocomplete from "@/features/species/components/SpeciesAutocomplete.vue";
import LocationPicker from "@/features/map/components/LocationPicker.vue";
import PlaceSearch from "@/features/map/components/PlaceSearch.vue";
import PhotoPicker from "../components/PhotoPicker.vue";
import { useObservationForm } from "../composables/useObservationForm";

const {
  form,
  errors,
  formError,
  isSubmitting,
  isLocating,
  locationError,
  latestMoment,
  useMyPosition,
  submit,
} = useObservationForm();

const unit = computed(() =>
  form.species === null ? "" : CATEGORIES[form.species.category].unit[1],
);

const MAX_PHOTOS = 3;

const LINK =
  "inline-flex min-h-11 items-center text-sm text-ink-muted transition-colors hover:text-ink";
</script>

<template>
  <div class="mx-auto w-full pb-6 md:pb-20 pt-6">
    <RouterLink
      :to="{ name: 'observations' }"
      :class="LINK"
      class="mb-4 underline underline-offset-4"
    >
      ← Mon carnet
    </RouterLink>

    <h1 class="mb-8 font-serif text-4xl font-medium tracking-tight md:text-5xl">
      Nouvelle observation
    </h1>

    <form novalidate class="space-y-6" @submit.prevent="submit">
      <!-- min-w-0: a fieldset otherwise refuses to be narrower than its content. -->
      <fieldset class="min-w-0">
        <legend
          class="mb-2 block text-xs font-medium tracking-wider text-ink-muted uppercase"
        >
          Espèce
        </legend>

        <SpeciesAutocomplete
          id="observation-species"
          v-model="form.species"
          :invalid="!!errors.species"
        >
          <FormField
            id="observation-count"
            label="Nombre (facultatif)"
            class="w-28 shrink-0 sm:w-40"
          >
            <BaseInput
              id="observation-count"
              v-model="form.count"
              type="text"
              inputmode="numeric"
              :placeholder="unit"
              :aria-invalid="!!errors.count"
            />
          </FormField>
        </SpeciesAutocomplete>

        <div
          v-if="errors.species || errors.count"
          class="mt-1.5 flex gap-3 text-xs text-status-cr"
        >
          <p class="min-w-0 flex-1">{{ errors.species }}</p>
          <p class="w-28 shrink-0 sm:w-40">{{ errors.count }}</p>
        </div>
      </fieldset>

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

      <FormField id="observation-place" label="Lieu" :error="errors.position">
        <PlaceSearch
          id="observation-place"
          class="mb-3"
          :invalid="!!errors.position"
          @choose="form.position = $event"
        >
          <button
            type="button"
            class="inline-flex h-10 shrink-0 cursor-pointer items-center gap-2 border border-paper-border px-3 text-sm text-ink transition-colors hover:border-paper-border-strong hover:bg-paper-muted/40 disabled:pointer-events-none disabled:opacity-40"
            :disabled="isLocating"
            @click="useMyPosition"
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
              <circle cx="12" cy="12" r="7" />
              <path d="M12 2v3M12 19v3M2 12h3M19 12h3" />
            </svg>
            Ma position
          </button>
        </PlaceSearch>

        <p
          v-if="locationError"
          role="alert"
          class="mb-3 text-xs text-status-cr"
        >
          {{ locationError }}
        </p>

        <LocationPicker v-model="form.position" />

        <p class="mt-1.5 text-xs text-ink-faint">
          Vous pouvez aussi placer le repère sur la carte, puis le faire glisser
          pour l'ajuster.
        </p>
      </FormField>

      <FormField id="observation-photos" label="Photos (facultatif)">
        <PhotoPicker
          id="observation-photos"
          v-model="form.photos"
          :max="MAX_PHOTOS"
        />
      </FormField>

      <FormField
        id="observation-notes"
        label="Notes (facultatif)"
        :error="errors.notes"
      >
        <BaseTextarea
          id="observation-notes"
          v-model="form.notes"
          maxlength="2000"
          placeholder="Comportement, milieu, météo…"
          :aria-invalid="!!errors.notes"
        />
      </FormField>

      <p v-if="formError" role="alert" class="text-sm text-status-cr">
        {{ formError }}
      </p>

      <div
        class="flex flex-col gap-2 sm:flex-row-reverse sm:justify-start sm:gap-4"
      >
        <BaseButton
          type="submit"
          size="lg"
          class="w-full sm:w-auto"
          :disabled="isSubmitting"
        >
          {{ isSubmitting ? "Enregistrement…" : "Enregistrer" }}
        </BaseButton>

        <RouterLink
          :to="{ name: 'observations' }"
          :class="LINK"
          class="justify-center px-4"
        >
          Annuler
        </RouterLink>
      </div>
    </form>
  </div>
</template>
