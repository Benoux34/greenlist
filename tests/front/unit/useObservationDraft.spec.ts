import { describe, expect, it, vi } from "vitest";
import { useObservationDraft } from "@/features/observations/composables/useObservationDraft";
import { observation } from "../fixtures";

describe("useObservationDraft", () => {
  it("starts a new observation without a place, then places it", () => {
    const draft = useObservationDraft(vi.fn());

    draft.start();
    expect(draft.isDrafting.value).toBe(true);
    expect(draft.editing.value).toBeNull();
    expect(draft.position.value).toBeNull();

    draft.place({ latitude: 45, longitude: 5 });
    expect(draft.position.value).toEqual({ latitude: 45, longitude: 5 });
  });

  it("edits an observation from where it was seen", () => {
    const fox = observation({ latitude: 48.85, longitude: 2.35 });
    const draft = useObservationDraft(vi.fn());

    draft.start(fox);

    expect(draft.editing.value).toEqual(fox);
    expect(draft.position.value).toEqual({ latitude: 48.85, longitude: 2.35 });
  });

  it("ignores a place once cancelled", () => {
    const draft = useObservationDraft(vi.fn());
    draft.start();
    draft.cancel();

    draft.place({ latitude: 45, longitude: 5 });

    expect(draft.isDrafting.value).toBe(false);
    expect(draft.position.value).toBeNull();
  });

  it("places the observation at my position when it is found", async () => {
    const locate = vi.fn().mockResolvedValueOnce(null).mockResolvedValueOnce({ latitude: 45.9, longitude: 6.8 });
    const draft = useObservationDraft(locate);
    draft.start();

    await draft.placeAtMyPosition();
    expect(draft.position.value).toBeNull();

    await draft.placeAtMyPosition();
    expect(draft.position.value).toEqual({ latitude: 45.9, longitude: 6.8 });
  });
});
