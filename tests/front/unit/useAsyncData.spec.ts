import { flushPromises } from "@vue/test-utils";
import { describe, expect, it } from "vitest";
import { nextTick, ref } from "vue";
import { useAsyncData } from "@/shared/composables/useAsyncData";

function deferred<T>() {
  let resolve!: (value: T) => void;
  let reject!: (reason: unknown) => void;
  const promise = new Promise<T>((ok, ko) => {
    resolve = ok;
    reject = ko;
  });

  return { promise, resolve, reject };
}

describe("useAsyncData", () => {
  it("keeps the answer of the latest request only", async () => {
    const id = ref(1);
    const pending = new Map<number, ReturnType<typeof deferred<string>>>();
    const { data, isLoading } = useAsyncData(() => {
      const request = deferred<string>();
      pending.set(id.value, request);
      return request.promise;
    }, [id]);

    id.value = 2;
    await nextTick();
    pending.get(2)!.resolve("second");
    pending.get(1)!.resolve("first");
    await flushPromises();

    expect(data.value).toBe("second");
    expect(isLoading.value).toBe(false);
  });

  it("flags a failure and recovers on reload", async () => {
    let fail = true;
    const { data, hasFailed, reload } = useAsyncData(
      () => (fail ? Promise.reject(new Error("down")) : Promise.resolve(42)),
      [],
    );
    await flushPromises();
    expect(hasFailed.value).toBe(true);

    fail = false;
    await reload();

    expect(hasFailed.value).toBe(false);
    expect(data.value).toBe(42);
  });
});
