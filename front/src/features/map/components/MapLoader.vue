<script setup lang="ts">
import BaseLoader from "@/shared/components/BaseLoader.vue";

defineOptions({ inheritAttrs: false });
</script>

<template>
  <BaseLoader
    label="Chargement de la carte…"
    class="absolute inset-0 z-[26] bg-paper"
  />
</template>
