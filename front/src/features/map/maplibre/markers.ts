import { Marker, type LngLat } from "maplibre-gl";
import type { Coordinates } from "../types";
import { token } from "./setup";

function locationMarker(): Marker {
  const element = document.createElement("span");
  element.className =
    "block size-4 rounded-full border-[3px] border-surface bg-location shadow-md ring-4 ring-location/25";

  return new Marker({ element });
}

function toCoordinates(lngLat: LngLat): Coordinates {
  const { lat, lng } = lngLat.wrap();

  return { latitude: lat, longitude: lng };
}

function draftMarker(onMove: (position: Coordinates) => void): Marker {
  const marker = new Marker({ color: token("accent"), draggable: true });
  marker.getElement().setAttribute("aria-label", "Lieu de la nouvelle observation");
  marker.on("dragend", () => onMove(toCoordinates(marker.getLngLat())));

  return marker;
}

export { draftMarker, locationMarker, toCoordinates };
