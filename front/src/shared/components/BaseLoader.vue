<script setup lang="ts">
defineProps<{ label?: string }>();
</script>

<template>
  <div role="status" class="flex flex-col items-center justify-center gap-4">
    <span class="relative flex size-14 items-center justify-center">
      <span
        class="absolute size-14 rounded-full border-[3px] border-accent-soft border-t-accent motion-safe:animate-spin"
        aria-hidden="true"
      ></span>
      <svg
        viewBox="0 0 24 24"
        class="size-6 text-accent"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
      >
        <path d="M5 19c0-8 6-14 14-14 0 8-6 14-14 14z" />
        <path d="M5 19l8-8" />
      </svg>
    </span>
    <p class="text-sm font-medium text-ink-muted" :class="{ 'sr-only': !label }">
      {{ label ?? "Chargement…" }}
    </p>
  </div>
</template>
