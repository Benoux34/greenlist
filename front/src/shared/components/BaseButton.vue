<script setup lang="ts">
import { RouterLink, type RouteLocationRaw } from "vue-router";

const { size = "md", to } = defineProps<{
  size?: "md" | "lg";
  to?: RouteLocationRaw;
}>();
</script>

<template>
  <component
    :is="to === undefined ? 'button' : RouterLink"
    :to="to"
    class="inline-flex shrink-0 cursor-pointer items-center justify-center gap-1.5 rounded-full bg-accent text-sm font-medium whitespace-nowrap text-surface transition-colors outline-none select-none hover:bg-accent-strong focus-visible:ring-2 focus-visible:ring-accent/40 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-40"
    :class="size === 'lg' ? 'h-11 px-5' : 'h-9 px-4'"
  >
    <slot />
  </component>
</template>
