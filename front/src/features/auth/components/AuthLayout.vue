<script setup lang="ts">
import { RouterView } from "vue-router";
import AppLogo from "@/shared/components/AppLogo.vue";

const year = new Date().getFullYear();
</script>

<template>
  <div class="flex min-h-screen flex-col justify-between bg-paper p-6 text-ink">
    <header class="mx-auto flex w-full max-w-6xl items-center justify-between">
      <AppLogo />
    </header>

    <main class="my-10 flex w-full justify-center">
      <RouterView />
    </main>

    <footer class="mx-auto w-full max-w-6xl text-center text-xs text-ink-faint">
      <p>© {{ year }} FAUNEFLORE • Carnet d'observation de la nature.</p>
    </footer>
  </div>
</template>
