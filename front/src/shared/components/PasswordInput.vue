<script setup lang="ts">
import { ref } from "vue";
import BaseInput from "./BaseInput.vue";

defineOptions({ inheritAttrs: false });

const model = defineModel<string>({ required: true });

const visible = ref(false);
</script>

<template>
  <div class="relative">
    <BaseInput
      v-model="model"
      v-bind="$attrs"
      :type="visible ? 'text' : 'password'"
      class="pr-12"
    />
    <button
      type="button"
      class="absolute inset-y-0 right-0 flex w-11 cursor-pointer items-center justify-center rounded-r-xl text-ink-muted transition-colors hover:text-ink focus-visible:text-ink focus-visible:outline-none"
      :aria-label="visible ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
      :aria-pressed="visible"
      @click="visible = !visible"
    >
      <svg
        viewBox="0 0 24 24"
        class="size-5"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
      >
        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" />
        <circle cx="12" cy="12" r="3" />
        <path v-if="visible" d="M4 4l16 16" />
      </svg>
    </button>
  </div>
</template>
