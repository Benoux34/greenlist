<script setup lang="ts">
import { computed, ref } from "vue";
import BaseInput from "@/shared/components/BaseInput.vue";
import { usePlaceSearch } from "../composables/usePlaceSearch";
import type { PlaceSuggestion } from "../entities";

defineProps<{ id: string; invalid?: boolean }>();

const emit = defineEmits<{ choose: [place: PlaceSuggestion] }>();

const text = ref("");
const isOpen = ref(false);
const { suggestions, isSearching, hasFailed, search, minLength } =
  usePlaceSearch();

const status = computed(() => {
  if (text.value.trim().length < minLength)
    return `Tapez au moins ${minLength} lettres.`;
  if (isSearching.value) return "Recherche…";
  if (hasFailed.value)
    return "La recherche d'adresses est indisponible. Placez le repère sur la carte.";
  if (suggestions.value.length === 0) return "Aucun lieu trouvé.";

  return null;
});

function lookUp(value: string): void {
  isOpen.value = true;
  search(value);
}

function choose(place: PlaceSuggestion): void {
  text.value = place.label;
  isOpen.value = false;
  emit("choose", place);
}

function confirm(): void {
  const first = suggestions.value[0];
  if (status.value === null && first !== undefined) choose(first);
}

function closeWhenLeft(event: FocusEvent): void {
  const next = event.relatedTarget;
  if (next instanceof Node && (event.currentTarget as Node).contains(next))
    return;

  isOpen.value = false;
}
</script>

<template>
  <div class="relative flex gap-3">
    <div class="min-w-0 flex-1" @focusout="closeWhenLeft">
      <BaseInput
        :id="id"
        v-model="text"
        type="text"
        autocomplete="off"
        enterkeyhint="search"
        placeholder="Adresse, ville, lieu-dit…"
        :aria-invalid="invalid"
        @update:model-value="lookUp"
        @focus="isOpen = true"
        @keydown.enter.prevent="confirm"
        @keydown.esc="isOpen = false"
      />

      <div
        v-show="isOpen"
        class="absolute inset-x-0 top-full z-20 mt-1 max-h-80 overflow-y-auto border border-paper-border bg-paper shadow-sm"
        @mousedown.prevent
      >
        <p v-if="status" role="status" class="px-3 py-3 text-sm text-ink-muted">
          {{ status }}
        </p>

        <ul v-else aria-label="Lieux proposés">
          <li v-for="(place, index) in suggestions" :key="index">
            <button
              type="button"
              class="flex min-h-11 w-full cursor-pointer items-center px-3 py-2 text-left text-sm outline-none hover:bg-paper-muted focus-visible:bg-paper-muted"
              @click="choose(place)"
            >
              {{ place.label }}
            </button>
          </li>
        </ul>
      </div>
    </div>

    <slot />
  </div>
</template>
