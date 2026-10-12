<script setup lang="ts">
import CategoryPills from "@/features/species/components/CategoryPills.vue";
import type { ObservationFilters } from "../entities";

defineProps<{ filters: ObservationFilters; hasActiveFilters: boolean }>();

const emit = defineEmits<{
  change: [changes: Partial<ObservationFilters>];
  reset: [];
}>();
</script>

<template>
  <CategoryPills
    :model-value="filters.category"
    @update:model-value="emit('change', { category: $event })"
  />
</template>
