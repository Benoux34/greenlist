<script setup lang="ts">
import { computed, ref } from "vue";
import { RouterLink } from "vue-router";
import BaseButton from "@/shared/components/BaseButton.vue";
import ForgotPasswordCode from "./ForgotPasswordCode.vue";
import ForgotPasswordEmail from "./ForgotPasswordEmail.vue";
import ForgotPasswordNewPassword from "./ForgotPasswordNewPassword.vue";

const STEPS = ["email", "code", "password", "done"] as const;

const step = ref<(typeof STEPS)[number]>("email");
const email = ref("");
const code = ref("");

const number = computed(() => STEPS.indexOf(step.value) + 1);

function sendCode(address: string): void {
  email.value = address;
  step.value = "code";
}

function codeChecked(value: string): void {
  code.value = value;
  step.value = "password";
}
</script>

<template>
  <p
    v-if="step !== 'done'"
    class="mb-3 text-xs font-semibold tracking-wider text-accent uppercase"
  >
    Étape {{ number }} sur 3
  </p>

  <ForgotPasswordEmail
    v-if="step === 'email'"
    :email="email"
    @submit="sendCode"
  />
  <ForgotPasswordCode
    v-else-if="step === 'code'"
    :email="email"
    @submit="codeChecked"
    @back="step = 'email'"
  />
  <ForgotPasswordNewPassword
    v-else-if="step === 'password'"
    :email="email"
    :code="code"
    @submit="step = 'done'"
    @restart="step = 'email'"
  />
  <div v-else role="status">
    <span
      class="flex size-12 items-center justify-center rounded-full bg-accent-soft text-accent"
      aria-hidden="true"
    >
      <svg
        viewBox="0 0 24 24"
        class="size-6"
        fill="none"
        stroke="currentColor"
        stroke-width="2.4"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <path d="m5 12 5 5 9-10" />
      </svg>
    </span>
    <h1 class="mt-5 font-serif text-3xl font-semibold tracking-tight">
      Mot de passe modifié
    </h1>
    <p class="mt-2 text-sm leading-relaxed text-ink-muted">
      Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.
    </p>
    <BaseButton :to="{ name: 'login' }" size="lg" class="mt-7 w-full">
      Se connecter
    </BaseButton>
  </div>

  <p v-if="step !== 'done'" class="mt-6 text-center text-sm">
    <RouterLink
      :to="{ name: 'login' }"
      class="font-medium text-accent underline-offset-4 hover:underline"
    >
      Retour à la connexion
    </RouterLink>
  </p>
</template>
