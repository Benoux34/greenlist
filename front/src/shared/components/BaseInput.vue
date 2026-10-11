<script setup lang="ts">
const model = defineModel<string>({ required: true });
</script>

<template>
  <input
    v-model="model"
    class="h-10 w-full min-w-0 border border-paper-border bg-transparent px-3 py-2 text-sm text-ink transition-colors outline-none placeholder:text-ink-faint focus-visible:border-ink disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-status-cr"
  />
</template>
