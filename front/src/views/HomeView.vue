<script setup lang="ts">
import { defineAsyncComponent, ref } from "vue";
import LocateButton from "@/features/map/components/LocateButton.vue";
import MapLoader from "@/features/map/components/MapLoader.vue";
import { useMyPosition } from "@/features/map/composables/useMyPosition";
import AppHeader from "@/features/navigation/components/AppHeader.vue";
import MobileDock from "@/features/navigation/components/MobileDock.vue";
import ObservationDeleteDialog from "@/features/observations/components/ObservationDeleteDialog.vue";
import ObservationForm from "@/features/observations/components/ObservationForm.vue";
import ObservationSheet from "@/features/observations/components/ObservationSheet.vue";
import ObservationsPanel from "@/features/observations/components/ObservationsPanel.vue";
import ObservationsStatus from "@/features/observations/components/ObservationsStatus.vue";
import PlacementHint from "@/features/observations/components/PlacementHint.vue";
import { useObservationDraft } from "@/features/observations/composables/useObservationDraft";
import { useObservationFilters } from "@/features/observations/composables/useObservationFilters";
import { useObservations } from "@/features/observations/composables/useObservations";
import { useObservationSelection } from "@/features/observations/composables/useObservationSelection";
import type { Observation } from "@/features/observations/types";

const ObservationsMap = defineAsyncComponent({
  loader: () => import("@/features/map/components/ObservationsMap.vue"),
  loadingComponent: MapLoader,
  delay: 0,
});

const { observations, hasFailed, reload, put, remove } = useObservations();
const { filters, filtered, title, makeVisible } =
  useObservationFilters(observations);
const { selected, highlighted, detail, detailFailed } =
  useObservationSelection(filtered);
const {
  position: myPosition,
  isLocating,
  error: locationError,
  find: findMyPosition,
  show: showMyPosition,
} = useMyPosition();
const {
  isDrafting,
  editing,
  position: draft,
  start: startDraft,
  cancel: cancelDraft,
  place,
  placeAtMyPosition,
} = useObservationDraft(findMyPosition);

const isListOpen = ref(false);
const toDelete = ref<Observation | null>(null);

function openForm(observation: Observation | null = null): void {
  selected.value = null;
  isListOpen.value = false;
  startDraft(observation);
}

function choose(observation: Observation): void {
  isListOpen.value = false;
  selected.value = observation;
}

function saved(observation: Observation): void {
  cancelDraft();
  put(observation);
  makeVisible(observation);
  selected.value = observation;
}

function deleted(observation: Observation): void {
  toDelete.value = null;
  remove(observation);
}
</script>

<template>
  <div class="fixed inset-0 overflow-hidden">
    <h1 class="sr-only">Ma carte</h1>

    <ObservationsMap
      class="absolute inset-0"
      :observations="filtered"
      :selected-id="selected?.id ?? null"
      :highlighted-id="highlighted"
      :drafting="isDrafting"
      :draft="draft"
      :my-position="myPosition"
      @select="selected = $event"
      @place="place"
    />

    <AppHeader @create="openForm()" />

    <ObservationsPanel
      v-model:filters="filters"
      v-model:open="isListOpen"
      :observations="filtered"
      :title="observations !== null && !hasFailed ? title : ''"
      :selected-id="selected?.id ?? null"
      :hidden="isDrafting"
      @choose="choose"
      @highlight="highlighted = $event"
      @edit="openForm"
      @remove="toDelete = $event"
    />

    <LocateButton
      v-if="!isListOpen"
      :locating="isLocating"
      :error="locationError"
      :shifted="draft !== null"
      @locate="showMyPosition"
    />

    <ObservationsStatus
      :total="observations?.length ?? null"
      :matching="filtered.length"
      :failed="hasFailed"
      :drafting="isDrafting"
      @retry="reload"
      @create="openForm()"
    />

    <ObservationSheet
      v-if="selected"
      :observation="selected"
      :detail="detail"
      :detail-failed="detailFailed"
      @close="selected = null"
    />

    <PlacementHint
      v-if="isDrafting && draft === null"
      :locating="isLocating"
      @locate="placeAtMyPosition"
      @cancel="cancelDraft"
    />

    <ObservationForm
      v-if="isDrafting && draft !== null"
      :position="draft"
      :observation="editing"
      @saved="saved"
      @cancel="cancelDraft"
    />

    <ObservationDeleteDialog
      v-if="toDelete"
      :observation="toDelete"
      @deleted="deleted"
      @cancel="toDelete = null"
    />

    <MobileDock
      v-if="!isDrafting"
      :list-open="isListOpen"
      @toggle="isListOpen = !isListOpen"
      @create="openForm()"
    />
  </div>
</template>
