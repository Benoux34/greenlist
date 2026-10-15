<script setup lang="ts">
import { RouterLink } from "vue-router";
</script>

<template>
  <RouterLink
    to="/"
    class="flex items-center gap-2 transition-opacity hover:opacity-80"
  >
    <span
      class="flex size-7 items-center justify-center rounded-full bg-accent text-surface"
      aria-hidden="true"
    >
      <svg
        viewBox="0 0 24 24"
        class="size-4"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <path d="M5 19c0-8 6-14 14-14 0 8-6 14-14 14z" />
        <path d="M5 19l8-8" />
      </svg>
    </span>
    <span class="font-serif text-lg font-semibold tracking-tight text-ink">
      Greenlist
    </span>
  </RouterLink>
</template>
