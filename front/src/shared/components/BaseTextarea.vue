<script setup lang="ts">
const model = defineModel<string>({ required: true });
</script>

<template>
  <textarea
    v-model="model"
    class="block min-h-28 w-full min-w-0 resize-y border border-paper-border bg-transparent px-3 py-2 text-sm leading-relaxed text-ink transition-colors outline-none placeholder:text-ink-faint focus-visible:border-ink disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-status-cr"
  ></textarea>
</template>
