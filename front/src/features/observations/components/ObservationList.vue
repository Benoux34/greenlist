<script setup lang="ts">
import type { Observation } from "../types";
import ObservationCard from "./ObservationCard.vue";

defineProps<{ observations: Observation[]; selectedId: string | null }>();

const emit = defineEmits<{
  select: [observation: Observation];
  highlight: [id: string | null];
  edit: [observation: Observation];
  remove: [observation: Observation];
}>();
</script>

<template>
  <ul class="space-y-1">
    <li v-for="observation in observations" :key="observation.id">
      <ObservationCard
        :observation="observation"
        :selected="observation.id === selectedId"
        @select="emit('select', observation)"
        @highlight="emit('highlight', $event ? observation.id : null)"
        @edit="emit('edit', observation)"
        @remove="emit('remove', observation)"
      />
    </li>
  </ul>
</template>
