<script setup lang="ts">
import BaseButton from "@/shared/components/BaseButton.vue";
import { ABOVE_DOCK, CARD, GLASS } from "@/shared/utils/overlay";

defineProps<{
  total: number | null;
  matching: number;
  failed: boolean;
  drafting: boolean;
}>();

const emit = defineEmits<{ retry: []; create: [] }>();
</script>

<template>
  <p
    v-if="total === null && !failed"
    role="status"
    class="absolute left-1/2 z-10 flex -translate-x-1/2 items-center gap-2 rounded-full px-4 py-2 text-sm text-ink-muted lg:top-6"
    :class="[GLASS, ABOVE_DOCK, 'lg:bottom-auto']"
  >
    <span
      class="size-4 animate-spin rounded-full border-2 border-paper-border border-t-accent"
      aria-hidden="true"
    ></span>
    Chargement de vos observations…
  </p>

  <div
    v-else-if="failed"
    role="alert"
    class="absolute inset-x-4 z-20 mx-auto max-w-sm lg:bottom-6"
    :class="[CARD, ABOVE_DOCK]"
  >
    <p class="mb-1 font-serif text-lg font-semibold">
      La carte n'a pas pu être chargée
    </p>
    <p class="mb-4 text-sm text-ink-muted">
      Vérifiez votre connexion, puis réessayez.
    </p>
    <BaseButton @click="emit('retry')">Réessayer</BaseButton>
  </div>

  <div
    v-else-if="total === 0 && !drafting"
    class="absolute inset-x-4 z-20 mx-auto max-w-sm text-center lg:bottom-6"
    :class="[CARD, ABOVE_DOCK]"
  >
    <p class="mb-1 font-serif text-lg font-semibold">Votre carte est vide</p>
    <p class="mb-4 text-sm text-ink-muted">
      Chaque observation que vous ajoutez apparaîtra ici.
    </p>
    <BaseButton type="button" size="lg" @click="emit('create')">
      Ajouter une observation
    </BaseButton>
  </div>

  <p
    v-else-if="matching === 0 && !drafting"
    class="absolute left-1/2 z-10 -translate-x-1/2 rounded-full px-4 py-2 text-sm whitespace-nowrap text-ink-muted lg:bottom-6"
    :class="[GLASS, ABOVE_DOCK]"
  >
    Aucune observation ne correspond.
  </p>
</template>
