<script setup lang="ts">
defineProps<{ listOpen: boolean }>();

const emit = defineEmits<{ toggle: []; create: [] }>();
</script>

<template>
  <nav
    aria-label="Navigation de la carte"
    class="pointer-events-none fixed inset-x-0 bottom-0 z-30 flex justify-center px-4 pb-[max(1rem,env(safe-area-inset-bottom))] lg:hidden"
  >
    <div
      class="pointer-events-auto flex items-center gap-1.5 rounded-full bg-surface/90 p-1.5 shadow-lg ring-1 ring-ink/5 backdrop-blur-md"
    >
      <button
        type="button"
        class="flex h-12 cursor-pointer items-center gap-2 rounded-full px-4 text-sm font-medium transition-colors"
        :class="listOpen ? 'bg-accent-soft text-accent' : 'text-ink'"
        :aria-expanded="listOpen"
        aria-controls="observations-panel"
        @click="emit('toggle')"
      >
        <svg
          viewBox="0 0 24 24"
          class="size-5"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          aria-hidden="true"
        >
          <path d="M9 6h11M9 12h11M9 18h11" />
          <path d="M4.5 6h.01M4.5 12h.01M4.5 18h.01" stroke-width="3" />
        </svg>
        Liste
      </button>

      <button
        type="button"
        class="flex h-12 cursor-pointer items-center gap-2 rounded-full bg-accent pr-5 pl-4 text-sm font-semibold text-surface shadow-md transition-colors hover:bg-accent-strong"
        @click="emit('create')"
      >
        <svg
          viewBox="0 0 24 24"
          class="size-5"
          fill="none"
          stroke="currentColor"
          stroke-width="2.4"
          stroke-linecap="round"
          aria-hidden="true"
        >
          <path d="M12 5v14M5 12h14" />
        </svg>
        Nouvelle observation
      </button>
    </div>
  </nav>
</template>
