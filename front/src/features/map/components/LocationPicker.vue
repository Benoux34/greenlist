<script setup lang="ts">
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import { onBeforeUnmount, onMounted, useTemplateRef, watch } from "vue";
import type { Coordinates } from "../entities";

const position = defineModel<Coordinates | null>({ required: true });

const container = useTemplateRef<HTMLDivElement>("container");

const FRANCE: L.LatLngTuple = [46.6, 2.5];
const CLOSE_UP = 15;
const TILES = "https://tile.openstreetmap.org/{z}/{x}/{y}.png";
const ATTRIBUTION =
  '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>';

const PIN = L.divIcon({
  className: "",
  iconSize: [44, 44],
  html: '<span class="flex size-full items-center justify-center"><span class="size-5 rounded-full border-2 border-paper bg-ink shadow-md"></span></span>',
});

let map: L.Map | undefined;
let marker: L.Marker | undefined;

function pin(latlng: L.LatLng): void {
  if (map === undefined) return;

  marker ??= L.marker(latlng, { icon: PIN, draggable: true, keyboard: false })
    .on("dragend", () => marker && place(marker.getLatLng()))
    .addTo(map);
  marker.setLatLng(latlng);
}

function place(latlng: L.LatLng): void {
  const { lat, lng } = latlng.wrap();
  pin(L.latLng(lat, lng));
  position.value = { latitude: lat, longitude: lng };
}

onMounted(() => {
  if (container.value === null) return;

  const created = L.map(container.value, { scrollWheelZoom: false });
  map = created.setView(FRANCE, 5);
  L.tileLayer(TILES, { attribution: ATTRIBUTION, maxZoom: 19 }).addTo(created);

  created.on("click", (event) => place(event.latlng));
  created.on("focus", () => created.scrollWheelZoom.enable());
  created.on("blur", () => created.scrollWheelZoom.disable());

  watch(
    position,
    (value) => {
      if (value === null) {
        marker?.remove();
        marker = undefined;
        return;
      }

      const latlng = L.latLng(value.latitude, value.longitude);
      if (marker?.getLatLng().equals(latlng)) return;

      pin(latlng);
      created.setView(latlng, Math.max(created.getZoom(), CLOSE_UP));
    },
    { immediate: true },
  );
});

onBeforeUnmount(() => map?.remove());
</script>

<template>
  <div class="isolate border border-paper-border">
    <div
      ref="container"
      role="region"
      aria-label="Carte du lieu de l'observation"
      class="h-64 sm:h-80"
    ></div>
  </div>
</template>

<style>
.leaflet-container {
  background: var(--color-paper-muted);
  font-family: inherit;
}

.leaflet-container a {
  color: inherit;
}

.leaflet-tile-pane {
  filter: saturate(0.65);
}

.leaflet-touch .leaflet-bar {
  border: 1px solid var(--color-paper-border-strong);
}

.leaflet-touch .leaflet-bar,
.leaflet-touch .leaflet-bar a:first-child,
.leaflet-touch .leaflet-bar a:last-child {
  border-radius: 0;
}
</style>
