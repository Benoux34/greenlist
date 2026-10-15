import { http } from "@/shared/api/http";
import type { Observation, ObservationDetail, ObservationInput } from "./types";

const fetchMapObservations = () => http.get<Observation[]>("/observations/map");

const fetchObservation = (id: string) =>
  http.get<ObservationDetail>(`/observations/${id}`);

const createObservation = (input: ObservationInput) =>
  http.post<Observation>("/observations", input);

const updateObservation = (id: string, input: ObservationInput) =>
  http.patch<Observation>(`/observations/${id}`, input);

const deleteObservation = (id: string) => http.delete(`/observations/${id}`);

function addObservationPhoto(id: string, photo: Blob): Promise<Observation> {
  const body = new FormData();
  body.append("photo", photo, "photo.jpg");

  return http.post<Observation>(`/observations/${id}/photos`, body);
}

const removeObservationPhoto = (id: string, name: string) =>
  http.delete<Observation>(`/observations/${id}/photos/${name}`);

const observationPhotoUrl = (id: string, name: string) =>
  `/api/observations/${id}/photos/${name}`;

export {
  fetchMapObservations,
  fetchObservation,
  createObservation,
  updateObservation,
  deleteObservation,
  addObservationPhoto,
  removeObservationPhoto,
  observationPhotoUrl,
};
