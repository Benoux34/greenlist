<script setup lang="ts">
import { GLASS } from "@/shared/utils/overlay";

defineProps<{ locating: boolean; error: string | null; shifted: boolean }>();

const emit = defineEmits<{ locate: [] }>();
</script>

<template>
  <button
    type="button"
    class="absolute right-4 z-10 flex size-11 cursor-pointer items-center justify-center rounded-full text-ink transition-colors hover:text-accent disabled:opacity-50 lg:top-auto lg:bottom-[11.5rem]"
    :class="[
      GLASS,
      'top-[calc(max(1rem,env(safe-area-inset-top))+7.25rem)]',
      shifted ? 'lg:right-[27rem]' : 'lg:right-6',
    ]"
    :disabled="locating"
    aria-label="Me localiser"
    @click="emit('locate')"
  >
    <svg
      viewBox="0 0 24 24"
      class="size-5"
      :class="{ 'animate-pulse': locating }"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
      stroke-linecap="round"
      aria-hidden="true"
    >
      <circle cx="12" cy="12" r="7" />
      <circle cx="12" cy="12" r="1.5" fill="currentColor" />
      <path d="M12 2v3M12 19v3M2 12h3M19 12h3" />
    </svg>
  </button>

  <p
    v-if="error"
    role="alert"
    class="absolute inset-x-4 z-10 mx-auto max-w-sm rounded-2xl px-4 py-3 text-sm text-status-cr lg:top-24 lg:right-6 lg:left-auto"
    :class="[GLASS, 'top-[calc(max(1rem,env(safe-area-inset-top))+10.75rem)]']"
  >
    {{ error }}
  </p>
</template>
