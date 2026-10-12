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
    class="inline-flex shrink-0 cursor-pointer items-center justify-center gap-1.5 border border-transparent bg-ink bg-clip-padding text-sm font-medium whitespace-nowrap text-paper transition-colors outline-none select-none hover:bg-ink/90 focus-visible:border-ink focus-visible:ring-1 focus-visible:ring-ink disabled:pointer-events-none disabled:opacity-40"
    :class="size === 'lg' ? 'h-11 px-4' : 'h-9 px-3'"
  >
    <slot />
  </component>
</template>
