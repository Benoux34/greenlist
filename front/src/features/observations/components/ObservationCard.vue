<script setup lang="ts">
import { computed } from "vue";
import { CATEGORIES } from "@/features/species/categories";
import { observationPhotoUrl } from "../api";
import type { Observation } from "../entities";

const { observation } = defineProps<{ observation: Observation }>();

const category = computed(() => CATEGORIES[observation.species.category]);

const date = computed(() =>
  new Intl.DateTimeFormat("fr-FR", {
    day: "numeric",
    month: "long",
    year: "numeric",
  }).format(new Date(observation.observedAt)),
);

const counted = computed(() => {
  const { count } = observation;
  if (count === null) return null;

  return `${count} ${category.value.unit[count > 1 ? 1 : 0]}`;
});

const photo = computed(() => {
  const [first] = observation.photos;

  return first === undefined
    ? null
    : observationPhotoUrl(observation.id, first);
});
</script>

<template>
  <article class="flex h-full flex-col border border-paper-border">
    <img
      v-if="photo"
      :src="photo"
      alt=""
      loading="lazy"
      class="aspect-3/2 w-full border-b border-paper-border bg-paper-muted object-cover"
    />
    <div
      v-else
      class="flex aspect-3/2 w-full items-center justify-center border-b border-paper-border bg-paper-muted text-paper-border-strong"
      aria-hidden="true"
    >
      <svg
        viewBox="0 0 24 24"
        class="size-8"
        fill="none"
        stroke="currentColor"
        stroke-width="1.5"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <rect x="3" y="5" width="18" height="14" />
        <circle cx="8.5" cy="10" r="1.5" />
        <path d="M21 16l-5-5-8 8" />
      </svg>
    </div>

    <div class="flex flex-1 flex-col p-5 sm:p-6">
      <div class="mb-3 flex items-center justify-between gap-3 text-xs">
        <span class="inline-flex items-center gap-1.5 font-medium text-ink-muted">
          <span
            class="h-1.5 w-1.5 rounded-full"
            :class="category.dot"
            aria-hidden="true"
          ></span>
          {{ category.label }}
        </span>

        <time :datetime="observation.observedAt" class="text-ink-faint">
          {{ date }}
        </time>
      </div>

      <h2 class="font-serif text-lg font-medium tracking-tight">
        {{
          observation.species.vernacularName ?? observation.species.scientificName
        }}
      </h2>
      <p
        v-if="observation.species.vernacularName"
        class="font-serif text-sm text-ink-muted italic"
      >
        {{ observation.species.scientificName }}
      </p>

      <p
        v-if="observation.notes"
        class="mt-3 line-clamp-2 text-sm leading-relaxed text-ink-muted"
      >
        {{ observation.notes }}
      </p>

      <p v-if="counted" class="mt-auto pt-4 text-xs text-ink-faint">
        {{ counted }}
      </p>
    </div>
  </article>
</template>
