<script setup lang="ts">
import { computed } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../store";

const auth = useAuthStore();
const router = useRouter();

const initial = computed(() => auth.user?.firstName.charAt(0).toUpperCase());

async function logout(): Promise<void> {
  await auth.logout();
  await router.replace({ name: "login" });
}
</script>

<template>
  <button
    type="button"
    popovertarget="account-menu"
    class="flex size-10 cursor-pointer items-center justify-center rounded-full bg-accent text-sm font-semibold text-surface shadow-md ring-2 ring-surface transition-colors hover:bg-accent-strong"
    :aria-label="`Compte de ${auth.user?.firstName}`"
  >
    {{ initial }}
  </button>

  <div
    id="account-menu"
    popover
    class="inset-auto top-[4.5rem] right-4 m-0 w-64 rounded-2xl bg-surface p-2 text-ink shadow-xl ring-1 ring-paper-border lg:right-6"
  >
    <p class="px-3 pt-2 pb-3">
      <span class="block font-medium">{{ auth.user?.firstName }}</span>
      <span class="block truncate text-sm text-ink-muted">
        {{ auth.user?.email }}
      </span>
    </p>
    <button
      type="button"
      class="flex min-h-11 w-full cursor-pointer items-center rounded-xl px-3 text-left text-sm transition-colors hover:bg-paper-muted"
      @click="logout"
    >
      Se déconnecter
    </button>
  </div>
</template>
