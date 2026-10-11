<script setup lang="ts">
import { RouterView, useRouter } from "vue-router";
import { useAuthStore } from "@/features/auth/store";
import AppLogo from "@/shared/components/AppLogo.vue";

const auth = useAuthStore();
const router = useRouter();

async function logout(): Promise<void> {
  await auth.logout();
  await router.replace({ name: "login" });
}
</script>

<template>
  <div class="flex min-h-screen flex-col bg-paper text-ink">
    <header class="w-full border-b border-paper-border">
      <div
        class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5 md:px-4"
      >
        <AppLogo />

        <div class="flex items-center gap-4 text-sm">
          <span class="text-ink-muted">{{ auth.user?.firstName }}</span>
          <button
            type="button"
            class="inline-flex cursor-pointer items-center border border-paper-border bg-transparent px-3 py-1.5 text-xs text-ink-muted transition-colors hover:border-paper-border-strong hover:text-ink"
            @click="logout"
          >
            Se déconnecter
          </button>
        </div>
      </div>
    </header>

    <main class="flex flex-1 flex-col overflow-x-clip">
      <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col px-6 md:px-4">
        <RouterView />
      </div>
    </main>
  </div>
</template>
