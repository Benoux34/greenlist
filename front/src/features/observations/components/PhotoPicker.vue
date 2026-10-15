<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { compressImage } from "@/shared/utils/compressImage";
import { observationPhotoUrl } from "../api";

const photos = defineModel<Blob[]>({ required: true });
const kept = defineModel<string[]>("kept", { required: true });

const { max, observationId } = defineProps<{
  id: string;
  max: number;
  observationId: string | null;
}>();

const ACCEPTED = ["image/jpeg", "image/png"];

const error = ref<string | null>(null);
const isReading = ref(false);

const previews = computed(() =>
  photos.value.map((photo) => URL.createObjectURL(photo)),
);

const revoke = (urls: string[]) =>
  urls.forEach((url) => URL.revokeObjectURL(url));

watch(previews, (_, previous) => revoke(previous));
onBeforeUnmount(() => revoke(previews.value));

const count = computed(() => kept.value.length + photos.value.length);

const items = computed(() => [
  ...(observationId === null
    ? []
    : kept.value.map((name) => ({
        url: observationPhotoUrl(observationId, name),
        remove: () => {
          error.value = null;
          kept.value = kept.value.filter((other) => other !== name);
        },
      }))),
  ...previews.value.map((url, index) => ({ url, remove: () => remove(index) })),
]);

async function add(event: Event): Promise<void> {
  const input = event.target as HTMLInputElement;
  const chosen = [...(input.files ?? [])];
  input.value = "";

  const accepted = chosen.filter((file) => ACCEPTED.includes(file.type));
  const room = max - count.value;

  error.value =
    accepted.length < chosen.length
      ? "Seuls les fichiers JPG et PNG sont acceptés."
      : accepted.length > room
        ? `${max} photos au maximum.`
        : null;

  const added: Blob[] = [];
  isReading.value = true;
  for (const file of accepted.slice(0, room)) {
    try {
      added.push(await compressImage(file));
    } catch {
      error.value = `« ${file.name} » n'a pas pu être lue.`;
    }
  }
  isReading.value = false;

  photos.value = [...photos.value, ...added];
}

function remove(index: number): void {
  error.value = null;
  photos.value = photos.value.filter((_, position) => position !== index);
}
</script>

<template>
  <div>
    <ul v-if="items.length > 0" class="mb-2.5 grid grid-cols-3 gap-2">
      <li v-for="(item, index) in items" :key="item.url" class="relative">
        <img
          :src="item.url"
          alt=""
          class="aspect-square w-full rounded-xl object-cover ring-1 ring-ink/5"
        />
        <button
          type="button"
          class="absolute top-0 right-0 flex size-10 cursor-pointer items-start justify-end p-1.5"
          :aria-label="`Retirer la photo ${index + 1}`"
          @click="item.remove()"
        >
          <span
            class="flex size-7 items-center justify-center rounded-full bg-ink/80 text-surface"
          >
            <svg
              viewBox="0 0 24 24"
              class="size-3.5"
              fill="none"
              stroke="currentColor"
              stroke-width="2.4"
              stroke-linecap="round"
              aria-hidden="true"
            >
              <path d="M6 6l12 12M18 6L6 18" />
            </svg>
          </span>
        </button>
      </li>
    </ul>

    <label
      v-if="count < max"
      class="flex h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-paper-border-strong px-4 text-sm font-medium text-ink transition-colors focus-within:border-accent focus-within:ring-2 focus-within:ring-accent/20 hover:bg-paper-muted/60"
      :class="{ 'pointer-events-none opacity-40': isReading }"
    >
      <svg
        viewBox="0 0 24 24"
        class="size-4"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
      >
        <path
          d="M4 8a2 2 0 0 1 2-2h1.5l1.5-2h6l1.5 2H18a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"
        />
        <circle cx="12" cy="12.5" r="3.5" />
      </svg>
      {{ count === 0 ? "Ajouter des photos" : "Ajouter une photo" }}
      <input
        :id="id"
        type="file"
        accept="image/png,image/jpeg"
        multiple
        class="sr-only"
        :disabled="isReading"
        @change="add"
      />
    </label>

    <p
      class="mt-1.5 text-xs"
      :class="error ? 'text-status-cr' : 'text-ink-faint'"
      :role="error ? 'alert' : undefined"
    >
      {{ error ?? `JPG ou PNG, ${max} photos au maximum.` }}
    </p>
  </div>
</template>
