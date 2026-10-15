import { NavigationControl, type Map, type Marker } from "maplibre-gl";
import { onBeforeUnmount, onMounted, ref, watch, type ShallowRef } from "vue";
import type { Observation } from "@/features/observations/types";
import { flyTo, focusPoint, revealPoint } from "../maplibre/camera";
import {
  addObservationLayers,
  onObservationClick,
  showObservations,
  styleObservationPoints,
} from "../maplibre/layers";
import { draftMarker, locationMarker, toCoordinates } from "../maplibre/markers";
import { createMap } from "../maplibre/setup";
import type { Coordinates } from "../types";

type Options = Readonly<{
  observations: () => Observation[];
  selectedId: () => string | null;
  highlightedId: () => string | null;
  drafting: () => boolean;
  draft: () => Coordinates | null;
  myPosition: () => Coordinates | null;
  onSelect: (observation: Observation | null) => void;
  onPlace: (position: Coordinates) => void;
}>;

function useObservationsMap(
  container: Readonly<ShallowRef<HTMLDivElement | null>>,
  options: Options,
) {
  const state = ref<"loading" | "ready" | "failed">("loading");

  let map: Map | undefined;
  let loaded = false;
  let you: Marker | undefined;
  let pin: Marker | undefined;
  // A click on the map already moves the camera gently; changes coming from
  // elsewhere (list, GPS, new observation) fly to the point instead.
  let clickedId: string | null = null;
  let placedOnMap = false;

  const find = (id: string | null) =>
    options.observations().find((observation) => observation.id === id);

  const style = () =>
    map &&
    loaded &&
    styleObservationPoints(map, options.selectedId(), options.highlightedId());

  function show(list: Observation[]): void {
    if (map && loaded) showObservations(map, list);
  }

  function select(id: string | null): void {
    const observation = find(id) ?? null;
    if (map && observation) revealPoint(map, observation);
    clickedId = observation?.id ?? null;
    options.onSelect(observation);
  }

  function place(position: Coordinates): void {
    if (map && options.draft() === null) revealPoint(map, position);
    placedOnMap = true;
    options.onPlace(position);
  }

  function showSelection(id: string | null): void {
    const observation = find(id);
    if (map && observation && id !== clickedId) focusPoint(map, observation);
    clickedId = null;
  }

  function showDraft(
    position: Coordinates | null,
    previous: Coordinates | null,
  ): void {
    if (map && position === null) {
      pin?.remove();
      pin = undefined;
    } else if (map && position !== null) {
      pin ??= draftMarker(place)
        .setLngLat([position.longitude, position.latitude])
        .addTo(map);
      pin.setLngLat([position.longitude, position.latitude]);
      if (previous === null && !placedOnMap) focusPoint(map, position);
    }
    placedOnMap = false;
  }

  function showPosition(position: Coordinates | null): void {
    if (map === undefined || position === null) return;

    you ??= locationMarker()
      .setLngLat([position.longitude, position.latitude])
      .addTo(map);
    you.setLngLat([position.longitude, position.latitude]);
    flyTo(map, position, 13);
  }

  function create(): void {
    if (container.value === null) return;

    let created: Map;
    try {
      created = createMap(container.value);
    } catch {
      state.value = "failed";
      return;
    }

    map = created;
    created.addControl(new NavigationControl({ showCompass: false }), "bottom-right");

    created.on("load", () => {
      loaded = true;
      addObservationLayers(created);
      onObservationClick(created, select, () => !options.drafting());
      created.on("click", (event) => {
        if (options.drafting()) place(toCoordinates(event.lngLat));
      });
      style();
      show(options.observations());
      state.value = "ready";
    });

    created.on("error", () => {
      if (!created.isStyleLoaded()) state.value = "failed";
    });
  }

  function destroy(): void {
    map?.remove();
    map = undefined;
    loaded = false;
    you = undefined;
    pin = undefined;
  }

  function retry(): void {
    destroy();
    state.value = "loading";
    create();
  }

  onMounted(create);
  onBeforeUnmount(destroy);

  watch(options.observations, show);
  watch(options.selectedId, showSelection);
  watch(() => [options.selectedId(), options.highlightedId()], style);
  watch(options.draft, showDraft);
  watch(options.myPosition, showPosition);
  watch(options.drafting, (drafting) => {
    if (map) map.getCanvas().style.cursor = drafting ? "crosshair" : "";
  });

  return { state, retry };
}

export { useObservationsMap };
