<script setup lang="ts">
import { useTemplateRef } from "vue";
import BaseButton from "@/shared/components/BaseButton.vue";
import type { Observation } from "@/features/observations/types";
import { useObservationsMap } from "../composables/useObservationsMap";
import type { Coordinates } from "../types";
import MapLoader from "./MapLoader.vue";

const props = defineProps<{
  observations: Observation[];
  selectedId: string | null;
  highlightedId: string | null;
  drafting: boolean;
  draft: Coordinates | null;
  myPosition: Coordinates | null;
}>();

const emit = defineEmits<{
  select: [observation: Observation | null];
  place: [position: Coordinates];
}>();

const { state, retry } = useObservationsMap(
  useTemplateRef<HTMLDivElement>("container"),
  {
    observations: () => props.observations,
    selectedId: () => props.selectedId,
    highlightedId: () => props.highlightedId,
    drafting: () => props.drafting,
    draft: () => props.draft,
    myPosition: () => props.myPosition,
    onSelect: (observation) => emit("select", observation),
    onPlace: (position) => emit("place", position),
  },
);
</script>

<template>
  <div :class="{ 'map-beside-form': draft !== null }">
    <div
      ref="container"
      role="region"
      aria-label="Carte de mes observations"
      class="h-full w-full"
    ></div>

    <Transition
      leave-active-class="transition-opacity duration-500"
      leave-to-class="opacity-0"
    >
      <MapLoader v-if="state === 'loading'" />
    </Transition>

    <div
      v-if="state === 'failed'"
      role="alert"
      class="absolute inset-0 z-[26] flex items-center justify-center bg-paper px-6"
    >
      <div class="max-w-sm text-center">
        <p class="mb-1 font-serif text-lg font-semibold">
          La carte ne s'affiche pas
        </p>
        <p class="mb-4 text-sm text-ink-muted">
          Vérifiez votre connexion, puis réessayez.
        </p>
        <BaseButton @click="retry">Réessayer</BaseButton>
      </div>
    </div>
  </div>
</template>
