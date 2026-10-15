<script setup lang="ts">
import { onMounted, ref, useTemplateRef } from "vue";
import { deleteObservation } from "../api";
import { formatObservedAt, speciesName } from "../format";
import type { Observation } from "../types";

const { observation } = defineProps<{ observation: Observation }>();

const emit = defineEmits<{ deleted: [observation: Observation]; cancel: [] }>();

const dialog = useTemplateRef<HTMLDialogElement>("dialog");
const isDeleting = ref(false);
const hasFailed = ref(false);

onMounted(() => dialog.value?.showModal());

async function confirm(): Promise<void> {
  isDeleting.value = true;
  hasFailed.value = false;

  try {
    await deleteObservation(observation.id);
    emit("deleted", observation);
  } catch {
    hasFailed.value = true;
  } finally {
    isDeleting.value = false;
  }
}
</script>

<template>
  <dialog
    ref="dialog"
    class="m-auto w-[calc(100%-2rem)] max-w-sm rounded-3xl bg-surface text-ink shadow-xl backdrop:bg-ink/30 backdrop:backdrop-blur-sm"
    aria-labelledby="delete-observation-title"
    @cancel.prevent="emit('cancel')"
    @click.self="emit('cancel')"
  >
    <div class="p-6">
      <h2
        id="delete-observation-title"
        class="font-serif text-xl font-semibold tracking-tight"
      >
        Supprimer cette observation ?
      </h2>
      <p class="mt-2 text-sm leading-relaxed text-ink-muted">
        « {{ speciesName(observation) }} » du
        {{ formatObservedAt(observation) }} sera supprimée avec ses photos.
        C'est définitif.
      </p>
      <p v-if="hasFailed" role="alert" class="mt-3 text-sm text-status-cr">
        La suppression a échoué. Réessayez.
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
          :disabled="isDeleting"
          @click="confirm"
        >
          {{ isDeleting ? "Suppression…" : "Supprimer" }}
        </button>
      </div>
    </div>
  </dialog>
</template>
