<script setup lang="ts">
import { computed } from "vue";
import { CATEGORIES } from "@/features/species/categories";
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
</script>

<template>
  <article class="flex h-full flex-col border border-paper-border p-5 sm:p-6">
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
  </article>
</template>
