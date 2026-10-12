<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{
  page: number;
  perPage: number;
  total: number;
  label: string;
  disabled?: boolean;
}>();

const emit = defineEmits<{ change: [page: number] }>();

const pageCount = computed(() => Math.ceil(props.total / props.perPage));
const first = computed(() => (props.page - 1) * props.perPage + 1);
const last = computed(() => Math.min(props.page * props.perPage, props.total));

const format = (value: number) => new Intl.NumberFormat("fr-FR").format(value);

const BUTTON =
  "inline-flex min-h-11 cursor-pointer items-center border border-paper-border bg-transparent px-4 text-xs text-ink transition-colors hover:border-paper-border-strong hover:bg-paper-muted/40 disabled:pointer-events-none disabled:opacity-40 sm:min-h-0 sm:px-3 sm:py-1.5";
</script>

<template>
  <nav
    v-if="total > perPage"
    aria-label="Pagination"
    class="border-t border-paper-border pt-6"
  >
    <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
      <p class="text-xs text-ink-muted">
        Affichage de
        <strong class="font-medium text-ink">{{ format(first) }}</strong>
        à
        <strong class="font-medium text-ink">{{ format(last) }}</strong>
        sur
        <strong class="font-medium text-ink">{{ format(total) }}</strong>
        {{ label }}
      </p>

      <div
        class="flex w-full items-center justify-between gap-2 sm:w-auto sm:justify-start"
      >
        <button
          type="button"
          :class="BUTTON"
          :disabled="page <= 1 || disabled"
          @click="emit('change', page - 1)"
        >
          Précédent
        </button>

        <span class="px-3 text-xs text-ink-muted">
          Page
          <span class="font-medium text-ink">{{ page }}</span>
          sur
          <span class="font-medium text-ink">{{ pageCount }}</span>
        </span>

        <button
          type="button"
          :class="BUTTON"
          :disabled="page >= pageCount || disabled"
          @click="emit('change', page + 1)"
        >
          Suivant
        </button>
      </div>
    </div>
  </nav>
</template>
