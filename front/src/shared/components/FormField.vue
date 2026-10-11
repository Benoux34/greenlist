<script setup lang="ts">
defineProps<{ id: string; label: string; error?: string }>();
</script>

<template>
  <div>
    <label
      :for="id"
      class="mb-2 block text-xs font-medium tracking-wider text-ink-muted uppercase"
    >
      {{ label }}
    </label>
    <slot />
    <p v-if="error" :id="`${id}-error`" class="mt-1.5 text-xs text-status-cr">
      {{ error }}
    </p>
  </div>
</template>
