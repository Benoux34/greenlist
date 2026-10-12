import { ref, watch, type Ref, type WatchSource } from "vue";

type AsyncData<T> = Readonly<{
  data: Ref<T | null>;
  hasFailed: Ref<boolean>;
  isLoading: Ref<boolean>;
  reload: () => Promise<void>;
}>;

function useAsyncData<T>(
  load: () => Promise<T>,
  sources: WatchSource[],
): AsyncData<T> {
  const data = ref<T | null>(null) as Ref<T | null>;
  const hasFailed = ref(false);
  const isLoading = ref(false);
  let latest = 0;

  async function reload(): Promise<void> {
    const current = ++latest;
    isLoading.value = true;
    hasFailed.value = false;

    try {
      const result = await load();
      if (current === latest) data.value = result;
    } catch {
      if (current === latest) hasFailed.value = true;
    } finally {
      if (current === latest) isLoading.value = false;
    }
  }

  watch(sources, reload, { immediate: true });

  return { data, hasFailed, isLoading, reload };
}

export { useAsyncData };
