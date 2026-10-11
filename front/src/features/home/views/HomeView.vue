<script setup lang="ts">
import { useAuthStore } from "@/features/auth/store";

const auth = useAuthStore();
</script>

<template>
  <div class="py-8 md:py-12">
    <h1 class="mb-4 font-serif text-4xl font-medium tracking-tight md:text-5xl">
      Bonjour {{ auth.user?.firstName }}
    </h1>
    <p class="text-base leading-relaxed text-ink-muted sm:text-lg">
      Votre carnet est vide pour l'instant. Les observations et la carte
      arrivent bientôt.
    </p>
  </div>
</template>
