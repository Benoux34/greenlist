<script setup lang="ts">
import { onMounted, ref, useTemplateRef } from "vue";
import { removeFriend } from "../api";
import type { Friend } from "../entities";

const { friend } = defineProps<{ friend: Friend }>();

const emit = defineEmits<{ removed: [friend: Friend]; cancel: [] }>();

const dialog = useTemplateRef<HTMLDialogElement>("dialog");
const isRemoving = ref(false);
const hasFailed = ref(false);

onMounted(() => dialog.value?.showModal());

async function confirm(): Promise<void> {
  isRemoving.value = true;
  hasFailed.value = false;

  try {
    await removeFriend(friend.id);
    emit("removed", friend);
  } catch {
    hasFailed.value = true;
  } finally {
    isRemoving.value = false;
  }
}
</script>

<template>
  <dialog
    ref="dialog"
    class="m-auto w-[calc(100%-2rem)] max-w-sm rounded-3xl bg-surface text-ink shadow-xl backdrop:bg-ink/30 backdrop:backdrop-blur-sm"
    aria-labelledby="remove-friend-title"
    @cancel.prevent="emit('cancel')"
    @click.self="emit('cancel')"
  >
    <div class="p-6">
      <h2
        id="remove-friend-title"
        class="font-serif text-xl font-semibold tracking-tight"
      >
        Retirer {{ friend.firstName }} de vos amis ?
      </h2>
      <p class="mt-2 text-sm leading-relaxed text-ink-muted">
        Vous ne verrez plus les observations l'un de l'autre. Pour redevenir
        amis, il faudra une nouvelle invitation.
      </p>
      <p v-if="hasFailed" role="alert" class="mt-3 text-sm text-status-cr">
        Le retrait a échoué. Réessayez.
      </p>

      <div class="mt-6 flex justify-end gap-2">
        <button
          type="button"
          class="h-11 cursor-pointer rounded-full px-4 text-sm font-medium text-ink-muted transition-colors hover:bg-paper-muted hover:text-ink focus-visible:ring-2 focus-visible:ring-accent/40 focus-visible:outline-none"
          @click="emit('cancel')"
        >
          Annuler
        </button>
        <button
          type="button"
          class="h-11 cursor-pointer rounded-full bg-status-cr px-5 text-sm font-medium text-surface transition-opacity hover:opacity-90 focus-visible:ring-2 focus-visible:ring-status-cr/40 focus-visible:outline-none disabled:opacity-50"
          :disabled="isRemoving"
          @click="confirm"
        >
          {{ isRemoving ? "Retrait…" : "Retirer" }}
        </button>
      </div>
    </div>
  </dialog>
</template>
