import {
  LngLatBounds,
  type ExpressionSpecification,
  type GeoJSONSource,
  type Map,
} from "maplibre-gl";
import type { FeatureCollection, Point } from "geojson";
import type { Observation } from "@/features/observations/types";
import { mapPadding } from "./camera";
import { token } from "./setup";

const SOURCE = "observations";

function toFeatures(
  list: Observation[],
): FeatureCollection<Point> {
  return {
    type: "FeatureCollection",
    features: list.map((observation) => ({
      type: "Feature",
      properties: {
        id: observation.id,
        category: observation.species.category,
      },
      geometry: {
        type: "Point",
        coordinates: [observation.longitude, observation.latitude],
      },
    })),
  };
}

function addObservationLayers(map: Map): void {
  map.addSource(SOURCE, {
    type: "geojson",
    data: toFeatures([]),
    cluster: true,
    clusterRadius: 48,
    clusterMaxZoom: 14,
  });

  map.addLayer({
    id: "clusters",
    type: "circle",
    source: SOURCE,
    filter: ["has", "point_count"],
    paint: {
      "circle-color": token("accent"),
      "circle-radius": ["step", ["get", "point_count"], 17, 10, 21, 50, 26],
      "circle-stroke-width": 3,
      "circle-stroke-color": token("surface"),
    },
  });

  map.addLayer({
    id: "cluster-count",
    type: "symbol",
    source: SOURCE,
    filter: ["has", "point_count"],
    layout: {
      "text-field": ["get", "point_count_abbreviated"],
      "text-font": ["Noto Sans Bold"],
      "text-size": 13,
    },
    paint: { "text-color": token("surface") },
  });

  map.addLayer({
    id: "points",
    type: "circle",
    source: SOURCE,
    filter: ["!", ["has", "point_count"]],
    paint: {
      "circle-color": [
        "match",
        ["get", "category"],
        "fauna",
        token("category-fauna"),
        "flora",
        token("category-flora"),
        "fungi",
        token("category-fungi"),
        token("ink-faint"),
      ],
      "circle-radius": 7,
      "circle-stroke-width": 2.5,
      "circle-stroke-color": token("surface"),
    },
  });

  map.addLayer({
    id: "points-hit",
    type: "circle",
    source: SOURCE,
    filter: ["!", ["has", "point_count"]],
    paint: { "circle-radius": 20, "circle-opacity": 0 },
  });
}

function styleObservationPoints(
  map: Map,
  selectedId: string | null,
  highlightedId: string | null,
): void {
  const selected: ExpressionSpecification = ["==", ["get", "id"], selectedId ?? ""];
  const highlighted: ExpressionSpecification = ["==", ["get", "id"], highlightedId ?? ""];

  map.setPaintProperty("points", "circle-radius", ["case", selected, 11, highlighted, 10, 7]);
  map.setPaintProperty("points", "circle-stroke-color", ["case", selected, token("ink"), token("surface")]);
}

function showObservations(map: Map, list: Observation[]): void {
  map.getSource<GeoJSONSource>(SOURCE)?.setData(toFeatures(list));
  if (list.length === 0) return;

  const bounds = new LngLatBounds();
  for (const observation of list)
    bounds.extend([observation.longitude, observation.latitude]);
  map.fitBounds(bounds, { padding: mapPadding(), maxZoom: 13, duration: 600 });
}

function onObservationClick(
  map: Map,
  select: (id: string | null) => void,
  isEnabled: () => boolean,
): void {
  map.on("click", "clusters", async (event) => {
    if (!isEnabled()) return;

    const feature = event.features?.[0];
    const source = map.getSource<GeoJSONSource>(SOURCE);
    if (feature === undefined || source === undefined) return;

    const zoom = await source.getClusterExpansionZoom(
      feature.properties.cluster_id,
    );
    const [lng = 0, lat = 0] = (feature.geometry as Point).coordinates;
    map.easeTo({ center: [lng, lat], zoom });
  });

  map.on("click", "points-hit", (event) => {
    if (!isEnabled()) return;

    const id: unknown = event.features?.[0]?.properties.id;
    select(typeof id === "string" ? id : null);
  });

  map.on("click", (event) => {
    if (!isEnabled()) return;

    const hits = map.queryRenderedFeatures(event.point, {
      layers: ["points-hit", "clusters"],
    });
    if (hits.length === 0) select(null);
  });

  for (const layer of ["points-hit", "clusters"]) {
    map.on("mouseenter", layer, () => {
      if (isEnabled()) map.getCanvas().style.cursor = "pointer";
    });
    map.on("mouseleave", layer, () => {
      if (isEnabled()) map.getCanvas().style.cursor = "";
    });
  }
}

export {
  addObservationLayers,
  onObservationClick,
  showObservations,
  styleObservationPoints,
};
