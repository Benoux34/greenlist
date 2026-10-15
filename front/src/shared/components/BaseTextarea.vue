<script setup lang="ts">
const model = defineModel<string>({ required: true });
</script>

<template>
  <textarea
    v-model="model"
    class="block min-h-24 resize-y py-2.5 leading-relaxed w-full min-w-0 rounded-xl border border-paper-border bg-surface px-3.5 text-base text-ink transition-colors outline-none placeholder:text-ink-faint focus:border-accent focus:ring-2 focus:ring-accent/20 disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-status-cr lg:text-sm"
  ></textarea>
</template>
