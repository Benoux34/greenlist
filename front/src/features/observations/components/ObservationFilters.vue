<script setup lang="ts">
import { computed } from "vue";
import CategoryPills from "@/features/species/components/CategoryPills.vue";
import { PERIODS, type FilterState } from "../filters";

const filters = defineModel<FilterState>({ required: true });

function field<K extends keyof FilterState>(key: K) {
  return computed({
    get: () => filters.value[key],
    set: (value: FilterState[K]) => {
      filters.value = { ...filters.value, [key]: value };
    },
  });
}

const search = field("search");
const category = field("category");
const period = field("period");
const from = field("from");
const to = field("to");
const withPhotos = field("withPhotos");

const CHIP =
  "h-10 cursor-pointer rounded-full border text-sm font-medium shadow-sm transition-colors";
const ON = "border-ink bg-ink text-surface";
const OFF =
  "border-paper-border bg-surface text-ink-muted hover:border-paper-border-strong hover:text-ink";
const DATE =
  "mt-1 h-10 w-full rounded-xl border border-paper-border bg-surface px-3 text-sm text-ink outline-none focus:border-accent";
</script>

<template>
  <div class="space-y-3">
    <label class="relative block">
      <span class="sr-only">Rechercher dans mes observations</span>
      <svg
        viewBox="0 0 24 24"
        class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-ink-faint"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        aria-hidden="true"
      >
        <circle cx="11" cy="11" r="7" />
        <path d="m20 20-3.5-3.5" />
      </svg>
      <input
        v-model="search"
        type="search"
        placeholder="Espèce, nom latin, note…"
        class="h-11 w-full rounded-full border border-paper-border bg-surface pr-4 pl-10 text-sm text-ink outline-none placeholder:text-ink-faint focus:border-accent focus:ring-2 focus:ring-accent/20"
      />
    </label>

    <CategoryPills v-model="category" />

    <div class="flex flex-wrap items-center gap-2">
      <label
        class="relative min-w-[11.5rem] flex-1"
        :class="period === 'all' ? 'text-ink-muted' : 'text-surface'"
      >
        <span class="sr-only">Période</span>
        <svg
          viewBox="0 0 24 24"
          class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
          aria-hidden="true"
        >
          <rect x="3" y="5" width="18" height="16" rx="2" />
          <path d="M3 10h18M8 3v4M16 3v4" />
        </svg>
        <select
          v-model="period"
          class="w-full appearance-none pr-8 pl-9"
          :class="[CHIP, period === 'all' ? OFF : ON]"
        >
          <option
            v-for="option in PERIODS"
            :key="option.value"
            :value="option.value"
          >
            {{ option.label }}
          </option>
        </select>
        <svg
          viewBox="0 0 24 24"
          class="pointer-events-none absolute top-1/2 right-2.5 size-4 -translate-y-1/2"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
          aria-hidden="true"
        >
          <path d="m6 9 6 6 6-6" />
        </svg>
      </label>

      <button
        type="button"
        class="inline-flex shrink-0 items-center gap-2 px-3.5"
        :class="[CHIP, withPhotos ? ON : OFF]"
        :aria-pressed="withPhotos"
        @click="withPhotos = !withPhotos"
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
          <path d="M4 8h3l2-3h6l2 3h3v11H4z" />
          <circle cx="12" cy="13" r="3.5" />
        </svg>
        Avec photo
      </button>
    </div>

    <div v-if="period === 'custom'" class="grid grid-cols-2 gap-2">
      <label class="block text-xs font-medium text-ink-muted">
        Du
        <input v-model="from" type="date" :max="to || undefined" :class="DATE" />
      </label>
      <label class="block text-xs font-medium text-ink-muted">
        Au
        <input v-model="to" type="date" :min="from || undefined" :class="DATE" />
      </label>
    </div>
  </div>
</template>
