<script setup lang="ts">
import { computed, nextTick, ref, useId, useTemplateRef, watch } from "vue";
import BaseInput from "@/shared/components/BaseInput.vue";
import { CATEGORIES } from "../categories";
import { useSpeciesSearch } from "../composables/useSpeciesSearch";
import type { SpeciesCategory, SpeciesSuggestion } from "../types";
import CategoryPills from "./CategoryPills.vue";

const selected = defineModel<SpeciesSuggestion | null>({ required: true });

defineProps<{ id: string; invalid?: boolean }>();

const text = ref("");
const category = ref<SpeciesCategory | null>(null);
const { suggestions, isSearching, hasFailed, minLength } = useSpeciesSearch(
  text,
  category,
);

const isOpen = ref(false);
const activeIndex = ref(-1);
const listId = useId();
const input = useTemplateRef<InstanceType<typeof BaseInput>>("input");

watch(suggestions, () => {
  activeIndex.value = -1;
});

const status = computed(() => {
  if (text.value.trim().length < minLength)
    return `Tapez au moins ${minLength} lettres.`;
  if (isSearching.value) return "Recherche…";
  if (hasFailed.value)
    return "La recherche d'espèces est indisponible pour le moment.";
  if (suggestions.value.length === 0) return "Aucune espèce trouvée.";

  return null;
});

function choose(suggestion: SpeciesSuggestion): void {
  selected.value = suggestion;
  isOpen.value = false;
  text.value = "";
}

async function change(): Promise<void> {
  selected.value = null;
  await nextTick();
  (input.value?.$el as HTMLInputElement | undefined)?.focus();
}

function move(step: 1 | -1): void {
  const count = suggestions.value.length;
  if (count === 0) return;

  isOpen.value = true;
  activeIndex.value = (activeIndex.value + step + count) % count;
}

function confirm(): void {
  const suggestion = suggestions.value[activeIndex.value];
  if (isOpen.value && suggestion !== undefined) choose(suggestion);
}
</script>

<template>
  <div class="space-y-2.5">
    <div
      v-if="selected"
      class="flex h-11 items-center gap-2.5 rounded-xl border border-paper-border bg-surface pl-1.5"
    >
      <img
        v-if="selected.photoUrl"
        :src="selected.photoUrl"
        alt=""
        class="size-8 shrink-0 rounded-lg object-cover"
      />
      <span
        v-else
        class="ml-2 size-2 shrink-0 rounded-full"
        :class="CATEGORIES[selected.category].dot"
        aria-hidden="true"
      ></span>
      <p class="min-w-0 flex-1 truncate text-sm">
        <span class="font-medium">
          {{ selected.vernacularName ?? selected.scientificName }}
        </span>
        <span
          v-if="selected.vernacularName"
          class="ml-1.5 font-serif text-ink-muted italic"
        >
          {{ selected.scientificName }}
        </span>
      </p>
      <button
        type="button"
        class="h-full shrink-0 cursor-pointer px-3.5 text-sm font-medium text-accent underline-offset-4 hover:underline"
        @click="change"
      >
        Changer
      </button>
    </div>

    <template v-else>
      <CategoryPills v-model="category" />

      <BaseInput
        :id="id"
        ref="input"
        v-model="text"
        type="text"
        role="combobox"
        autocomplete="off"
        autocapitalize="off"
        spellcheck="false"
        placeholder="Chêne, mésange, cèpe…"
        aria-autocomplete="list"
        :aria-expanded="isOpen"
        :aria-controls="listId"
        :aria-activedescendant="
          activeIndex >= 0 ? `${listId}-${activeIndex}` : undefined
        "
        :aria-invalid="invalid"
        @focus="isOpen = true"
        @blur="isOpen = false"
        @input="isOpen = true"
        @keydown.down.prevent="move(1)"
        @keydown.up.prevent="move(-1)"
        @keydown.enter.prevent="confirm"
        @keydown.esc="isOpen = false"
      />

      <div
        v-show="isOpen"
        class="overflow-hidden rounded-2xl border border-paper-border bg-surface"
      >
        <p v-if="status" role="status" class="px-3.5 py-3 text-sm text-ink-muted">
          {{ status }}
        </p>

        <ul
          v-else
          :id="listId"
          role="listbox"
          aria-label="Espèces proposées"
          class="max-h-72 overflow-y-auto py-1"
        >
          <li
            v-for="(suggestion, index) in suggestions"
            :id="`${listId}-${index}`"
            :key="suggestion.inatId"
            role="option"
            :aria-selected="index === activeIndex"
            class="flex min-h-14 cursor-pointer items-center gap-3 px-3 py-2"
            :class="index === activeIndex ? 'bg-paper-muted' : ''"
            @mousedown.prevent="choose(suggestion)"
            @mousemove="activeIndex = index"
          >
            <img
              v-if="suggestion.photoUrl"
              :src="suggestion.photoUrl"
              alt=""
              loading="lazy"
              class="size-10 shrink-0 rounded-lg object-cover"
            />
            <span
              v-else
              class="size-10 shrink-0 rounded-lg bg-paper-muted"
              aria-hidden="true"
            ></span>

            <span class="min-w-0 flex-1">
              <span class="block truncate text-sm font-medium">
                {{ suggestion.vernacularName ?? suggestion.scientificName }}
              </span>
              <span
                v-if="suggestion.vernacularName"
                class="block truncate font-serif text-xs text-ink-muted italic"
              >
                {{ suggestion.scientificName }}
              </span>
            </span>

            <span
              class="size-2 shrink-0 rounded-full"
              :class="CATEGORIES[suggestion.category].dot"
              :title="CATEGORIES[suggestion.category].label"
            ></span>
          </li>
        </ul>
      </div>
    </template>
  </div>
</template>
