import { http } from "@/shared/api/http";
import type { Paginated } from "@/shared/api/entities";
import type {
  CreateObservationInput,
  Observation,
  ObservationFilters,
} from "../entities";

const PER_PAGE = 12;

function fetchObservations(
  filters: ObservationFilters,
): Promise<Paginated<Observation>> {
  const query = new URLSearchParams({
    page: String(filters.page),
    perPage: String(PER_PAGE),
  });

  if (filters.category !== null) query.set("category", filters.category);
  if (filters.from !== null) query.set("from", filters.from);
  if (filters.to !== null) query.set("to", filters.to);

  return http.get<Paginated<Observation>>(`/observations?${query}`);
}

const createObservation = (input: CreateObservationInput) =>
  http.post<Observation>("/observations", input);

export { fetchObservations, createObservation };
