<script setup lang="ts">
import { ref } from "vue";
import { useAsyncData } from "@/shared/composables/useAsyncData";
import { ABOVE_DOCK } from "@/shared/utils/overlay";
import { fetchFriends } from "../api";
import type { Friend } from "../entities";
import FriendInvite from "./FriendInvite.vue";
import RemoveFriendDialog from "./RemoveFriendDialog.vue";

const emit = defineEmits<{ close: [] }>();

const { data: friends, hasFailed, reload } = useAsyncData(fetchFriends, []);
const toRemove = ref<Friend | null>(null);

const SINCE = new Intl.DateTimeFormat("fr-FR", { dateStyle: "long" });

function removed(friend: Friend): void {
  toRemove.value = null;
  friends.value = (friends.value ?? []).filter(({ id }) => id !== friend.id);
}
</script>

<template>
  <div
    class="absolute inset-x-3 z-20 mx-auto flex max-h-[55dvh] max-w-md flex-col lg:inset-x-auto lg:top-24 lg:right-6 lg:bottom-auto lg:max-h-[calc(100dvh-21.5rem)] lg:w-96 lg:max-w-none"
    :class="ABOVE_DOCK"
  >
    <section
      class="flex min-h-0 flex-col overflow-hidden rounded-3xl bg-surface shadow-xl ring-1 ring-ink/5"
      aria-labelledby="friends-title"
    >
      <header
        class="flex items-center justify-between gap-3 border-b border-paper-border py-3 pr-3 pl-6"
      >
        <h2
          id="friends-title"
          class="font-serif text-xl font-semibold tracking-tight"
        >
          Mes amis
        </h2>
        <button
          type="button"
          class="flex size-10 shrink-0 cursor-pointer items-center justify-center rounded-full text-ink-muted transition-colors hover:bg-paper-muted hover:text-ink"
          aria-label="Fermer mes amis"
          @click="emit('close')"
        >
          <svg
            viewBox="0 0 24 24"
            class="size-4"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            aria-hidden="true"
          >
            <path d="M6 6l12 12M18 6L6 18" />
          </svg>
        </button>
      </header>

      <div class="min-h-0 space-y-4 overflow-y-auto p-5">
        <FriendInvite />

        <p v-if="hasFailed" role="alert" class="text-sm text-ink-muted">
          Vos amis n'ont pas pu être chargés.
          <button
            type="button"
            class="cursor-pointer font-medium text-accent underline-offset-4 hover:underline"
            @click="reload"
          >
            Réessayer
          </button>
        </p>
        <p
          v-else-if="friends === null"
          role="status"
          class="text-sm text-ink-muted"
        >
          Chargement de vos amis…
        </p>
        <p v-else-if="friends.length === 0" class="text-sm text-ink-muted">
          Vous n'avez pas encore d'amis.
        </p>
        <ul v-else class="-mx-2">
          <li
            v-for="friend in friends"
            :key="friend.id"
            class="flex items-center gap-3 rounded-2xl p-2"
          >
            <span
              class="flex size-10 shrink-0 items-center justify-center rounded-full bg-accent-soft font-semibold text-accent"
              aria-hidden="true"
            >
              {{ friend.firstName.charAt(0).toUpperCase() }}
            </span>
            <span class="min-w-0 flex-1">
              <span class="block truncate font-medium">{{
                friend.firstName
              }}</span>
              <span class="block text-xs text-ink-muted">
                Amis depuis le {{ SINCE.format(new Date(friend.since)) }}
              </span>
            </span>
            <button
              type="button"
              class="flex size-9 shrink-0 cursor-pointer items-center justify-center rounded-full text-ink-muted transition-colors hover:bg-paper-muted hover:text-status-cr focus-visible:ring-2 focus-visible:ring-accent/40 focus-visible:outline-none"
              :aria-label="`Retirer ${friend.firstName} de mes amis`"
              @click="toRemove = friend"
            >
              <svg
                viewBox="0 0 24 24"
                class="size-4"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
              >
                <path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3" />
              </svg>
            </button>
          </li>
        </ul>
      </div>
    </section>

    <RemoveFriendDialog
      v-if="toRemove"
      :friend="toRemove"
      @removed="removed"
      @cancel="toRemove = null"
    />
  </div>
</template>
