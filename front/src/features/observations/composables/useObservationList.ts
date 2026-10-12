import { computed, watch } from "vue";
import {
  useRoute,
  useRouter,
  type LocationQuery,
  type LocationQueryRaw,
} from "vue-router";
import { useAsyncData } from "@/shared/composables/useAsyncData";
import { FILTERABLE_CATEGORIES } from "@/features/species/categories";
import { fetchObservations } from "../api";
import type { ObservationFilters } from "../entities";

const DAY = /^\d{4}-\d{2}-\d{2}$/;

function filtersOf(query: LocationQuery): ObservationFilters {
  const { category, from, to, page } = query;

  return {
    category: FILTERABLE_CATEGORIES.find((key) => key === category) ?? null,
    from: typeof from === "string" && DAY.test(from) ? from : null,
    to: typeof to === "string" && DAY.test(to) ? to : null,
    page: Math.max(1, Number.parseInt(String(page), 10) || 1),
  };
}

function queryOf(filters: ObservationFilters): LocationQueryRaw {
  return {
    ...(filters.category !== null && { category: filters.category }),
    ...(filters.from !== null && { from: filters.from }),
    ...(filters.to !== null && { to: filters.to }),
    ...(filters.page > 1 && { page: String(filters.page) }),
  };
}

function useObservationList() {
  const route = useRoute();
  const router = useRouter();

  const filters = computed(() => filtersOf(route.query));

  const hasActiveFilters = computed(
    () =>
      filters.value.category !== null ||
      filters.value.from !== null ||
      filters.value.to !== null,
  );

  const { data, hasFailed, isLoading, reload } = useAsyncData(
    () => fetchObservations(filters.value),
    [filters],
  );

  function applyFilters(changes: Partial<ObservationFilters>): void {
    void router.replace({
      query: queryOf({ ...filters.value, page: 1, ...changes }),
    });
  }

  function resetFilters(): void {
    applyFilters({ category: null, from: null, to: null });
  }

  function goToPage(page: number): void {
    void router.push({ query: queryOf({ ...filters.value, page }) });
    window.scrollTo({ top: 0, behavior: "smooth" });
  }

  watch(data, (page) => {
    if (page !== null && page.items.length === 0 && page.total > 0)
      void router.replace({
        query: queryOf({
          ...filters.value,
          page: Math.ceil(page.total / page.perPage),
        }),
      });
  });

  return {
    filters,
    hasActiveFilters,
    data,
    hasFailed,
    isLoading,
    reload,
    applyFilters,
    resetFilters,
    goToPage,
  };
}

export { useObservationList };
