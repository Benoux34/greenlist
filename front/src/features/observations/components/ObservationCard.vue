<script setup lang="ts">
import { computed } from "vue";
import { firstPhotoUrl, formatObservedAt, speciesName } from "../format";
import type { Observation } from "../types";
import PhotoPlaceholder from "./PhotoPlaceholder.vue";

const { observation } = defineProps<{
  observation: Observation;
  selected: boolean;
}>();

const emit = defineEmits<{
  select: [];
  highlight: [on: boolean];
  edit: [];
  remove: [];
}>();

const label = computed(
  () => `${speciesName(observation)} (${formatObservedAt(observation)})`,
);

const ICON =
  "flex size-9 shrink-0 cursor-pointer items-center justify-center rounded-full text-ink-muted transition-colors hover:bg-surface focus-visible:ring-2 focus-visible:ring-accent/40 focus-visible:outline-none";
</script>

<template>
  <div
    class="flex items-center gap-0.5 rounded-2xl pr-1.5 transition-colors"
    :class="selected ? 'bg-accent-soft' : 'hover:bg-paper-muted'"
    @mouseenter="emit('highlight', true)"
    @mouseleave="emit('highlight', false)"
  >
    <button
      type="button"
      class="flex min-w-0 flex-1 cursor-pointer items-center gap-3 rounded-2xl p-2 text-left focus-visible:ring-2 focus-visible:ring-accent/40 focus-visible:outline-none"
      @click="emit('select')"
      @focus="emit('highlight', true)"
      @blur="emit('highlight', false)"
    >
      <img
        v-if="firstPhotoUrl(observation)"
        :src="firstPhotoUrl(observation) ?? undefined"
        alt=""
        loading="lazy"
        class="size-12 shrink-0 rounded-xl bg-paper-muted object-cover"
      />
      <PhotoPlaceholder v-else class="size-12 shrink-0 rounded-xl" />

      <span class="min-w-0 flex-1">
        <span class="block truncate text-sm font-medium">
          {{ speciesName(observation) }}
        </span>
        <span class="block text-xs text-ink-muted">
          {{ formatObservedAt(observation) }}
        </span>
      </span>
    </button>

    <button
      type="button"
      :class="[ICON, 'hover:text-ink']"
      :aria-label="`Modifier ${label}`"
      @click="emit('edit')"
    >
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
        <path d="M4 20h4L19 9l-4-4L4 16z" />
        <path d="m13.5 6.5 4 4" />
      </svg>
    </button>

    <button
      type="button"
      :class="[ICON, 'hover:text-status-cr']"
      :aria-label="`Supprimer ${label}`"
      @click="emit('remove')"
    >
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
        <path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3" />
      </svg>
    </button>
  </div>
</template>
