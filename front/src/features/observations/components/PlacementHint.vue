<script setup lang="ts">
defineProps<{ locating: boolean }>();

const emit = defineEmits<{ locate: []; cancel: [] }>();
</script>

<template>
  <div
    role="status"
    class="absolute inset-x-3 bottom-[max(0.75rem,env(safe-area-inset-bottom))] z-30 mx-auto max-w-md rounded-3xl bg-surface/95 p-4 shadow-xl ring-1 ring-ink/5 backdrop-blur-md lg:bottom-6"
  >
    <div class="flex items-start gap-3">
      <span
        class="flex size-10 shrink-0 items-center justify-center rounded-full bg-accent-soft text-accent"
        aria-hidden="true"
      >
        <svg
          viewBox="0 0 24 24"
          class="size-5"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
        >
          <path d="M12 3v5M12 16v5M3 12h5M16 12h5" />
          <circle cx="12" cy="12" r="1.5" fill="currentColor" />
        </svg>
      </span>
      <div class="min-w-0 pt-0.5">
        <p class="font-medium">
          <span class="lg:hidden">Touchez la carte</span>
          <span class="hidden lg:inline">Cliquez sur la carte</span>
          à l'endroit de l'observation
        </p>
        <p class="mt-0.5 text-sm text-ink-muted">
          Le formulaire s'ouvrira ensuite. Vous pourrez encore déplacer le
          repère.
        </p>
      </div>
    </div>

    <div class="mt-3 flex justify-end gap-2">
      <button
        type="button"
        class="h-11 cursor-pointer rounded-full px-4 text-sm font-medium text-ink-muted transition-colors hover:bg-paper-muted hover:text-ink"
        @click="emit('cancel')"
      >
        Annuler
      </button>
      <button
        type="button"
        class="flex h-11 cursor-pointer items-center gap-2 rounded-full bg-accent-soft px-4 text-sm font-medium text-accent transition-colors hover:bg-accent hover:text-surface disabled:opacity-50"
        :disabled="locating"
        @click="emit('locate')"
      >
        <svg
          viewBox="0 0 24 24"
          class="size-4"
          :class="{ 'animate-pulse': locating }"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          aria-hidden="true"
        >
          <circle cx="12" cy="12" r="7" />
          <circle cx="12" cy="12" r="1.5" fill="currentColor" />
          <path d="M12 2v3M12 19v3M2 12h3M19 12h3" />
        </svg>
        Ma position
      </button>
    </div>
  </div>
</template>
