import { Map, setWorkerUrl, type MapOptions } from "maplibre-gl";
import workerUrl from "maplibre-gl/dist/maplibre-gl-worker.mjs?worker&url";
import "maplibre-gl/dist/maplibre-gl.css";
import "./maplibre.css";

const STYLE = "https://tiles.openfreemap.org/styles/liberty";

const MAIN_ROAD = /^(road|bridge|tunnel)_(motorway|trunk_primary|secondary_tertiary|link)/;

setWorkerUrl(workerUrl);

const token = (name: string) =>
  getComputedStyle(document.documentElement)
    .getPropertyValue(`--color-${name}`)
    .trim();

function soften(map: Map): void {
  map.setPaintProperty("background", "background-color", token("paper"));
  map.setPaintProperty("water", "fill-color", token("map-water"));
  map.setPaintProperty("waterway_river", "line-color", token("map-water"));
  map.setPaintProperty("waterway_other", "line-color", token("map-water"));

  for (const layer of map.getStyle().layers) {
    if (layer.type !== "line" || !MAIN_ROAD.test(layer.id)) continue;

    const casing = layer.id.endsWith("_casing");
    map.setPaintProperty(
      layer.id,
      "line-color",
      token(casing ? "paper-border-strong" : "surface"),
    );
  }
}

function createMap(
  container: HTMLElement,
  options: Partial<MapOptions> = {},
): Map {
  const map = new Map({
    container,
    style: STYLE,
    center: [2.4, 46.6],
    zoom: 4.6,
    attributionControl: { compact: true },
    ...options,
  });
  map.on("style.load", () => soften(map));

  return map;
}

export { createMap, token };
