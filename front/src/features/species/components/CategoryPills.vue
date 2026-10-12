<script setup lang="ts">
import { CATEGORIES, FILTERABLE_CATEGORIES } from "../categories";
import type { SpeciesCategory } from "../entities";

const selected = defineModel<SpeciesCategory | null>({ required: true });

const PILL =
  "inline-flex min-h-11 shrink-0 cursor-pointer items-center gap-1.5 border px-3 text-xs font-medium whitespace-nowrap transition-colors sm:min-h-0 sm:py-1.5 lg:min-h-10";
const PILL_SELECTED = "border-ink bg-ink text-paper";
const PILL_IDLE =
  "border-paper-border text-ink-muted hover:border-paper-border-strong hover:text-ink";
</script>

<template>
  <div
    role="group"
    aria-label="Catégorie"
    class="-mx-6 flex gap-1.5 overflow-x-auto px-6 md:mx-0 md:flex-wrap md:px-0"
  >
    <button
      type="button"
      :class="[PILL, selected === null ? PILL_SELECTED : PILL_IDLE]"
      :aria-pressed="selected === null"
      @click="selected = null"
    >
      Tout
    </button>

    <button
      v-for="key in FILTERABLE_CATEGORIES"
      :key="key"
      type="button"
      :class="[PILL, selected === key ? PILL_SELECTED : PILL_IDLE]"
      :aria-pressed="selected === key"
      @click="selected = key"
    >
      <span
        class="h-1.5 w-1.5 rounded-full"
        :class="CATEGORIES[key].dot"
        aria-hidden="true"
      ></span>
      {{ CATEGORIES[key].label }}
    </button>
  </div>
</template>
