import type { Map } from "maplibre-gl";
import type { Coordinates } from "../types";

const DESKTOP = "(min-width: 64rem)";
const DETAIL_PANEL = 408;

function listPanelEdge(): number {
  const panel = document.getElementById("observations-panel");

  return panel !== null && panel.offsetWidth > 0
    ? panel.getBoundingClientRect().right
    : 0;
}

function mapPadding() {
  return window.matchMedia(DESKTOP).matches
    ? { top: 110, bottom: 60, left: listPanelEdge() + 40, right: 80 }
    : { top: 150, bottom: 130, left: 40, right: 40 };
}

function detailOffset(): [number, number] {
  if (!window.matchMedia(DESKTOP).matches)
    return [0, -Math.round(window.innerHeight * 0.22)];

  const visibleCenter = (listPanelEdge() + window.innerWidth - DETAIL_PANEL) / 2;

  return [Math.round(visibleCenter - window.innerWidth / 2), 0];
}

function flyTo(map: Map, { latitude, longitude }: Coordinates, zoom: number): void {
  map.flyTo({
    center: [longitude, latitude],
    zoom: Math.max(map.getZoom(), zoom),
    padding: mapPadding(),
  });
}

function focusPoint(map: Map, { latitude, longitude }: Coordinates): void {
  map.flyTo({
    center: [longitude, latitude],
    zoom: Math.max(map.getZoom(), 14),
    offset: detailOffset(),
  });
}

function revealPoint(map: Map, { latitude, longitude }: Coordinates): void {
  map.easeTo({
    center: [longitude, latitude],
    offset: detailOffset(),
    duration: 500,
  });
}

export { flyTo, focusPoint, mapPadding, revealPoint };
