<script setup lang="ts">
import { ref } from "vue";
import CategoryPills from "@/features/species/components/CategoryPills.vue";
import { ABOVE_DOCK, GLASS, TOP } from "@/shared/utils/overlay";
import { DEFAULT_FILTERS, isFiltering, type FilterState } from "../filters";
import type { Observation } from "../types";
import ObservationFilters from "./ObservationFilters.vue";
import ObservationList from "./ObservationList.vue";

const filters = defineModel<FilterState>("filters", { required: true });
const open = defineModel<boolean>("open", { required: true });

defineProps<{
  observations: Observation[];
  title: string;
  selectedId: string | null;
  hidden: boolean;
}>();

const emit = defineEmits<{
  choose: [observation: Observation];
  highlight: [id: string | null];
  edit: [observation: Observation];
  remove: [observation: Observation];
}>();

const isCollapsed = ref(false);
</script>

<template>
  <div
    v-if="!open && !hidden"
    class="absolute inset-x-0 z-10 px-6 lg:hidden"
    :class="TOP"
  >
    <CategoryPills
      :model-value="filters.category"
      @update:model-value="filters = { ...filters, category: $event }"
    />
  </div>

  <aside
    id="observations-panel"
    class="absolute inset-x-3 z-[25] flex-col rounded-3xl bg-surface/90 shadow-xl ring-1 ring-ink/5 backdrop-blur-md lg:top-24 lg:right-auto lg:bottom-6 lg:left-6 lg:z-10 lg:w-[26rem]"
    :class="[
      open && !hidden ? 'flex' : 'hidden',
      isCollapsed || hidden ? 'lg:hidden' : 'lg:flex',
      TOP,
      ABOVE_DOCK,
    ]"
  >
    <div class="flex items-start justify-between gap-3 px-5 pt-5 pb-3">
      <div>
        <p class="font-serif text-2xl font-semibold tracking-tight">
          Mes observations
        </p>
        <p
          class="mt-1 flex min-h-5 flex-wrap items-baseline gap-x-2 text-sm text-ink-muted"
        >
          {{ title }}
          <button
            v-if="isFiltering(filters)"
            type="button"
            class="cursor-pointer text-accent underline-offset-4 hover:underline"
            @click="filters = { ...DEFAULT_FILTERS }"
          >
            Effacer les filtres
          </button>
        </p>
      </div>
      <button
        type="button"
        class="flex size-9 shrink-0 cursor-pointer items-center justify-center rounded-full text-ink-muted transition-colors hover:bg-paper-muted hover:text-ink lg:hidden"
        aria-label="Fermer la liste"
        @click="open = false"
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
      <button
        type="button"
        class="hidden size-9 shrink-0 cursor-pointer items-center justify-center rounded-full text-ink-muted transition-colors hover:bg-paper-muted hover:text-ink lg:flex"
        aria-label="Masquer la liste"
        aria-controls="observations-panel"
        :aria-expanded="true"
        @click="isCollapsed = true"
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
          <path d="m11 17-5-5 5-5M18 17l-5-5 5-5" />
        </svg>
      </button>
    </div>
    <ObservationFilters v-model="filters" class="px-5 pb-4" />
    <ObservationList
      class="min-h-0 flex-1 overflow-y-auto px-3 pb-3"
      :observations="observations"
      :selected-id="selectedId"
      @select="emit('choose', $event)"
      @highlight="emit('highlight', $event)"
      @edit="emit('edit', $event)"
      @remove="emit('remove', $event)"
    />
  </aside>

  <button
    v-if="isCollapsed && !hidden"
    type="button"
    class="absolute top-24 left-6 z-10 hidden h-11 cursor-pointer items-center gap-2 rounded-full px-4 text-sm font-medium text-ink transition-colors hover:text-accent lg:flex"
    :class="GLASS"
    aria-controls="observations-panel"
    :aria-expanded="false"
    @click="isCollapsed = false"
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
      <path d="M9 6h11M9 12h11M9 18h11" />
      <path d="M4.5 6h.01M4.5 12h.01M4.5 18h.01" stroke-width="3" />
    </svg>
    Mes observations
  </button>
</template>
