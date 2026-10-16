<script setup lang="ts">
import { onMounted, reactive, ref, useTemplateRef } from "vue";
import BaseButton from "@/shared/components/BaseButton.vue";
import FormField from "@/shared/components/FormField.vue";
import { fieldErrors, type FieldErrors } from "@/shared/utils/fieldErrors";
import { resetCodeSchema } from "../schemas";
import { ApiError } from "@/shared/api/http";
import { checkResetCode, requestPasswordReset } from "../api";

const { email } = defineProps<{ email: string }>();

const emit = defineEmits<{ submit: [code: string]; back: [] }>();

const form = reactive({ code: "" });
const errors = ref<FieldErrors<typeof form>>({});
const resent = ref(false);
const formError = ref<string | null>(null);
const submitting = ref(false);
const input = useTemplateRef<HTMLInputElement>("input");

onMounted(() => input.value?.focus());

async function submit(): Promise<void> {
  formError.value = null;
  const parsed = resetCodeSchema.safeParse(form);
  errors.value = parsed.success ? {} : fieldErrors(parsed.error);
  if (!parsed.success) return;

  submitting.value = true;
  try {
    await checkResetCode(email, parsed.data.code);
    emit("submit", parsed.data.code);
  } catch (error) {
    if (error instanceof ApiError && error.status === 422)
      errors.value = { code: "Code incorrect ou expiré." };
    else formError.value = "La vérification a échoué. Réessayez.";
  } finally {
    submitting.value = false;
  }
}

async function resend(): Promise<void> {
  await requestPasswordReset(email).catch(() => {});
  resent.value = true;
}
</script>

<template>
  <h1 class="font-serif text-3xl font-semibold tracking-tight">
    Saisissez le code
  </h1>
  <p class="mt-2 text-sm leading-relaxed text-ink-muted">
    Nous avons envoyé un code à 6 caractères à
    <strong class="font-medium break-words text-ink">{{ email }}</strong
    >.
  </p>

  <form novalidate class="mt-7 space-y-4" @submit.prevent="submit">
    <FormField
      id="reset-code"
      label="Code reçu par e-mail"
      :error="errors.code"
    >
      <input
        id="reset-code"
        ref="input"
        v-model="form.code"
        type="text"
        maxlength="6"
        autocomplete="one-time-code"
        autocapitalize="characters"
        spellcheck="false"
        placeholder="••••••"
        :aria-invalid="!!errors.code"
        class="h-14 w-full rounded-xl border border-paper-border bg-surface text-center font-mono text-2xl tracking-[0.5em] text-ink uppercase transition-colors outline-none placeholder:text-ink-faint focus:border-accent focus:ring-2 focus:ring-accent/20 aria-invalid:border-status-cr"
      />
    </FormField>

    <p
      v-if="formError"
      role="alert"
      class="rounded-2xl bg-status-cr/10 px-4 py-3 text-sm text-status-cr"
    >
      {{ formError }}
    </p>

    <BaseButton
      type="submit"
      size="lg"
      class="mt-2 w-full"
      :disabled="submitting"
    >
      {{ submitting ? "Vérification…" : "Vérifier le code" }}
    </BaseButton>
  </form>

  <p class="mt-5 text-sm leading-relaxed text-ink-muted">
    Rien reçu ? Regardez dans les indésirables,
    <button
      type="button"
      class="cursor-pointer font-medium text-accent underline-offset-4 hover:underline"
      @click="resend"
    >
      renvoyez le code
    </button>
    ou
    <button
      type="button"
      class="cursor-pointer font-medium text-accent underline-offset-4 hover:underline"
      @click="emit('back')"
    >
      changez d'adresse</button
    >.
  </p>
  <p v-if="resent" role="status" class="mt-2 text-sm text-accent">
    Un nouveau code vient de partir (un par minute au maximum).
  </p>
</template>
