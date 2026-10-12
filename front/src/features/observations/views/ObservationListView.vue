<script setup lang="ts">
import { RouterLink } from "vue-router";
import BaseButton from "@/shared/components/BaseButton.vue";
import Pagination from "@/shared/components/Pagination.vue";
import ObservationCard from "../components/ObservationCard.vue";
import ObservationFiltersBar from "../components/ObservationFilters.vue";
import { useObservationList } from "../composables/useObservationList";

const {
  filters,
  hasActiveFilters,
  data,
  hasFailed,
  isLoading,
  reload,
  applyFilters,
  resetFilters,
  goToPage,
} = useObservationList();
</script>

<template>
  <div class="pt-8 pb-28 md:pt-12 lg:pb-12">
    <header class="mb-8 flex items-start justify-between gap-4">
      <div>
        <h1
          class="mb-3 font-serif text-4xl font-medium tracking-tight md:text-5xl"
        >
          Mon carnet
        </h1>
        <p class="min-h-7 text-base leading-relaxed text-ink-muted sm:text-lg">
          <template v-if="data !== null && !hasFailed">
            {{ new Intl.NumberFormat("fr-FR").format(data.total) }}
            {{ data.total > 1 ? "observations" : "observation" }}
          </template>
        </p>
      </div>

      <div class="hidden lg:block">
        <BaseButton :to="{ name: 'observation-new' }" size="lg">
          Nouvelle observation
        </BaseButton>
      </div>
    </header>

    <RouterLink
      :to="{ name: 'observation-new' }"
      aria-label="Nouvelle observation"
      class="fixed right-6 bottom-[calc(5rem+env(safe-area-inset-bottom))] z-10 flex size-14 items-center justify-center rounded-full bg-ink text-paper shadow-lg transition-colors hover:bg-ink/90 lg:hidden"
    >
      <svg
        viewBox="0 0 24 24"
        class="size-6"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        aria-hidden="true"
      >
        <path d="M12 5v14M5 12h14" />
      </svg>
    </RouterLink>

    <ObservationFiltersBar
      class="mb-8"
      :filters="filters"
      :has-active-filters="hasActiveFilters"
      @change="applyFilters"
      @reset="resetFilters"
    />

    <div
      v-if="hasFailed"
      role="alert"
      class="border border-paper-border bg-paper-muted/20 p-8 sm:p-12"
    >
      <h2 class="mb-3 font-serif text-2xl font-medium tracking-tight">
        Le carnet n'a pas pu être chargé
      </h2>
      <p class="mb-6 text-sm leading-relaxed text-ink-muted">
        Vérifiez votre connexion, puis réessayez.
      </p>
      <BaseButton size="lg" @click="reload">Réessayer</BaseButton>
    </div>

    <div
      v-else-if="data === null"
      role="status"
      class="flex min-h-[40vh] flex-col items-center justify-center gap-5 border border-paper-border bg-paper-muted/15 p-8 text-center"
    >
      <span
        class="size-8 animate-spin rounded-full border-2 border-paper-border border-t-ink-muted"
        aria-hidden="true"
      ></span>
      <p class="font-serif text-lg text-ink-muted italic">
        Chargement de votre carnet…
      </p>
    </div>

    <div
      v-else-if="data.total === 0"
      class="border border-paper-border bg-paper-muted/20 p-8 sm:p-12"
    >
      <template v-if="hasActiveFilters">
        <p class="text-sm text-center leading-relaxed text-ink-muted">
          Aucune observation pour ces filtres
        </p>
      </template>

      <template v-else>
        <p class="text-sm text-center leading-relaxed text-ink-muted">
          Votre carnet est vide
        </p>
      </template>
    </div>

    <template v-else>
      <ul
        class="mb-8 grid grid-cols-1 gap-4 transition-opacity sm:grid-cols-2 lg:grid-cols-3"
        :class="{ 'opacity-50': isLoading }"
        :aria-busy="isLoading"
      >
        <li v-for="observation in data.items" :key="observation.id">
          <ObservationCard :observation="observation" />
        </li>
      </ul>

      <Pagination
        :page="data.page"
        :per-page="data.perPage"
        :total="data.total"
        label="observations"
        :disabled="isLoading"
        @change="goToPage"
      />
    </template>
  </div>
</template>
