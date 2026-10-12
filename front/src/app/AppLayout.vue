<script setup lang="ts">
import { RouterLink, RouterView, useRoute } from "vue-router";
import { useAuthStore } from "@/features/auth/store";
import AppLogo from "@/shared/components/AppLogo.vue";

const auth = useAuthStore();
const route = useRoute();

const LINKS = [
  { path: "/", label: "Carte" },
  { path: "/observations", label: "Carnet" },
] as const;

function isActive(path: string): boolean {
  return path === "/" ? route.path === "/" : route.path.startsWith(path);
}

const ACTIVE = "text-ink underline decoration-status-cr underline-offset-4";
const IDLE = "text-ink-muted transition-colors hover:text-ink";
</script>

<template>
  <div class="flex min-h-screen flex-col bg-paper text-ink">
    <header class="w-full border-b border-paper-border">
      <div
        class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5 md:px-4"
      >
        <AppLogo />

        <div class="flex items-center gap-6 text-sm">
          <nav
            aria-label="Navigation principale"
            class="hidden items-center gap-6 lg:flex"
          >
            <RouterLink
              v-for="link in LINKS"
              :key="link.path"
              :to="link.path"
              :class="isActive(link.path) ? ACTIVE : IDLE"
              :aria-current="isActive(link.path) ? 'page' : undefined"
            >
              {{ link.label }}
            </RouterLink>
          </nav>

          <span class="text-ink-muted">{{ auth.user?.firstName }}</span>
        </div>
      </div>
    </header>

    <main
      class="flex flex-1 flex-col overflow-x-clip pb-[calc(4rem+env(safe-area-inset-bottom))] lg:pb-0"
    >
      <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col px-6 md:px-4">
        <RouterView />
      </div>
    </main>

    <nav
      aria-label="Navigation principale"
      class="fixed inset-x-0 bottom-0 z-10 border-t border-paper-border bg-paper/95 pb-[env(safe-area-inset-bottom)] backdrop-blur-sm lg:hidden"
    >
      <div class="mx-auto flex max-w-6xl">
        <RouterLink
          v-for="link in LINKS"
          :key="link.path"
          :to="link.path"
          class="flex h-16 flex-1 items-center justify-center text-xs font-medium tracking-wider uppercase"
          :class="isActive(link.path) ? ACTIVE : IDLE"
          :aria-current="isActive(link.path) ? 'page' : undefined"
        >
          {{ link.label }}
        </RouterLink>
      </div>
    </nav>
  </div>
</template>
