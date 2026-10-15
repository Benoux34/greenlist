<script setup lang="ts">
import { computed } from "vue";
import { ABOVE_DOCK } from "@/shared/utils/overlay";
import { CATEGORIES } from "@/features/species/categories";
import {
  classification,
  formatCoordinates,
  formatCount,
  formatObservedAtTime,
  inaturalistUrl,
  photoUrls,
  speciesName,
} from "../format";
import type { Observation, ObservationDetail } from "../types";
import PhotoPlaceholder from "./PhotoPlaceholder.vue";

const { observation, detail } = defineProps<{
  observation: Observation;
  detail: ObservationDetail | null;
  detailFailed: boolean;
}>();

const emit = defineEmits<{ close: [] }>();

const category = computed(() => CATEGORIES[observation.species.category]);
const photos = computed(() => photoUrls(observation));
const current = computed(() =>
  detail?.id === observation.id ? detail : null,
);

const LABEL = "mb-1.5 text-xs font-medium tracking-wider text-ink-muted uppercase";
</script>

<template>
  <div
    class="absolute inset-x-3 z-20 mx-auto flex max-h-[55dvh] max-w-md flex-col lg:inset-x-auto lg:top-24 lg:right-6 lg:bottom-auto lg:max-h-[calc(100dvh-21.5rem)] lg:w-96 lg:max-w-none"
    :class="ABOVE_DOCK"
  >
    <article
      class="relative flex min-h-0 flex-col overflow-hidden rounded-3xl bg-surface shadow-xl ring-1 ring-ink/5"
      :aria-label="speciesName(observation)"
    >
      <div class="min-h-0 overflow-y-auto">
        <div
          v-if="photos.length > 0"
          class="flex snap-x snap-mandatory gap-2 overflow-x-auto"
        >
          <img
            v-for="url in photos"
            :key="url"
            :src="url"
            alt=""
            class="aspect-[4/3] shrink-0 snap-center bg-paper-muted object-cover"
            :class="photos.length > 1 ? 'w-[85%]' : 'w-full'"
          />
        </div>
        <PhotoPlaceholder v-else class="aspect-[5/2] w-full" />

        <div class="space-y-5 p-5">
          <header>
            <p class="flex items-center gap-1.5 text-xs text-ink-muted">
              <span class="size-2 rounded-full" :class="category.dot"></span>
              {{ category.label }} · {{ formatObservedAtTime(observation) }}
            </p>
            <h2
              class="mt-2 font-serif text-2xl leading-tight font-semibold tracking-tight"
            >
              {{ speciesName(observation) }}
            </h2>
            <p
              v-if="observation.species.vernacularName"
              class="font-serif text-ink-muted italic"
            >
              {{ observation.species.scientificName }}
            </p>
            <p
              v-if="observation.count !== null"
              class="mt-3 inline-flex rounded-full bg-paper-muted px-3 py-1 text-xs font-medium"
            >
              {{ formatCount(observation) }}
            </p>
          </header>

          <section v-if="current?.species.description || (!current && !detailFailed)">
            <h3 :class="LABEL">L'espèce</h3>
            <template v-if="current">
              <p class="line-clamp-5 text-sm leading-relaxed">
                {{ current.species.description }}
              </p>
              <a
                v-if="current.species.wikipediaUrl"
                :href="current.species.wikipediaUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="mt-1.5 inline-flex min-h-11 items-center gap-1 text-xs text-ink-muted underline underline-offset-4 hover:text-ink"
              >
                Source : Wikipédia
              </a>
            </template>
            <span v-else class="block space-y-2">
              <span class="block h-3.5 w-full animate-pulse rounded-full bg-paper-muted"></span>
              <span class="block h-3.5 w-5/6 animate-pulse rounded-full bg-paper-muted"></span>
              <span class="block h-3.5 w-2/3 animate-pulse rounded-full bg-paper-muted"></span>
            </span>
          </section>

          <section>
            <h3 :class="LABEL">Lieu</h3>
            <p v-if="current || detailFailed" class="text-sm">
              {{ current?.place ?? formatCoordinates(observation) }}
            </p>
            <span
              v-else
              class="block h-4 w-2/3 animate-pulse rounded-full bg-paper-muted"
            ></span>
          </section>

          <section v-if="observation.notes">
            <h3 :class="LABEL">Notes</h3>
            <p class="text-sm leading-relaxed whitespace-pre-line">
              {{ observation.notes }}
            </p>
          </section>

          <section>
            <h3 :class="LABEL">Classification</h3>
            <dl
              v-if="current"
              class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 text-sm"
            >
              <template
                v-for="[rank, name] in classification(current.species)"
                :key="rank"
              >
                <dt class="text-ink-muted">{{ rank }}</dt>
                <dd>{{ name }}</dd>
              </template>
            </dl>
            <p v-else-if="detailFailed" class="text-sm text-ink-muted">
              Indisponible pour le moment.
            </p>
            <span
              v-else
              class="block h-16 w-full animate-pulse rounded-2xl bg-paper-muted"
            ></span>
          </section>

          <a
            v-if="current"
            :href="inaturalistUrl(current.species)"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex min-h-11 items-center gap-1.5 text-sm font-medium text-accent underline-offset-4 hover:underline"
          >
            Voir l'espèce sur iNaturalist
            <svg
              viewBox="0 0 24 24"
              class="size-4"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              aria-hidden="true"
            >
              <path d="M7 17 17 7M9 7h8v8" />
            </svg>
          </a>
        </div>
      </div>

      <button
        type="button"
        class="absolute top-3 right-3 flex size-9 cursor-pointer items-center justify-center rounded-full bg-surface/90 text-ink shadow-md backdrop-blur-sm transition-colors hover:bg-surface"
        aria-label="Fermer"
        @click="emit('close')"
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
    </article>
  </div>
</template>
