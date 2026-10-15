import { flushPromises } from "@vue/test-utils";
import { describe, expect, it, vi } from "vitest";
import { fetchMapObservations } from "@/features/observations/api";
import { useObservations } from "@/features/observations/composables/useObservations";
import { observation } from "../fixtures";

vi.mock("@/features/observations/api", () => ({ fetchMapObservations: vi.fn() }));

describe("useObservations", () => {
  it("loads, updates and removes observations", async () => {
    const first = observation();
    const second = observation();
    vi.mocked(fetchMapObservations).mockResolvedValue([first, second]);

    const { observations, put, remove } = useObservations();
    await flushPromises();
    expect(observations.value).toEqual([first, second]);

    const edited = { ...first, count: 4 };
    put(edited);
    expect(observations.value).toEqual([edited, second]);

    const added = observation();
    put(added);
    expect(observations.value).toEqual([edited, second, added]);

    remove(second);
    expect(observations.value).toEqual([edited, added]);
  });
});
