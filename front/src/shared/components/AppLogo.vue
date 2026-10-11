<script setup lang="ts">
import { RouterLink } from "vue-router";
</script>

<template>
  <RouterLink
    to="/"
    class="group flex items-baseline transition-opacity hover:opacity-80"
  >
    <p
      class="pt-1 pr-1 font-serif text-2xl font-semibold tracking-tight text-ink sm:text-3xl"
    >
      GREENLIST
    </p>
    <span
      class="h-1.5 w-1.5 rounded-full bg-status-cr transition-transform group-hover:scale-125"
    ></span>
  </RouterLink>
</template>
