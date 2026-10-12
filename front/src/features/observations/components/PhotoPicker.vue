<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { compressImage } from "@/shared/utils/compressImage";

const photos = defineModel<Blob[]>({ required: true });

const { max } = defineProps<{ id: string; max: number }>();

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

async function add(event: Event): Promise<void> {
  const input = event.target as HTMLInputElement;
  const chosen = [...(input.files ?? [])];
  input.value = "";

  const accepted = chosen.filter((file) => ACCEPTED.includes(file.type));
  const room = max - photos.value.length;

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
    <ul
      v-if="photos.length > 0"
      class="mb-3 grid grid-cols-3 gap-3 sm:max-w-md"
    >
      <li v-for="(url, index) in previews" :key="url" class="relative">
        <img
          :src="url"
          alt=""
          class="aspect-square w-full border border-paper-border object-cover"
        />
        <button
          type="button"
          class="absolute top-0 right-0 flex size-10 cursor-pointer items-start justify-end p-1"
          :aria-label="`Retirer la photo ${index + 1}`"
          @click="remove(index)"
        >
          <span
            class="flex size-7 items-center justify-center bg-ink/85 text-paper"
          >
            <svg
              viewBox="0 0 24 24"
              class="size-4"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
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
      v-if="photos.length < max"
      class="inline-flex min-h-11 w-full cursor-pointer items-center justify-center gap-2 border border-dashed border-paper-border-strong px-4 text-sm text-ink transition-colors focus-within:border-ink hover:bg-paper-muted/40 sm:w-auto"
      :class="{ 'pointer-events-none opacity-40': isReading }"
    >
      <svg
        viewBox="0 0 24 24"
        class="size-4"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        aria-hidden="true"
      >
        <path d="M12 5v14M5 12h14" />
      </svg>
      {{ photos.length === 0 ? "Ajouter des photos" : "Ajouter une photo" }}
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
