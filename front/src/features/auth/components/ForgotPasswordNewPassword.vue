<script setup lang="ts">
import { onMounted, reactive, ref } from "vue";
import { ApiError } from "@/shared/api/http";
import BaseButton from "@/shared/components/BaseButton.vue";
import FormField from "@/shared/components/FormField.vue";
import PasswordInput from "@/shared/components/PasswordInput.vue";
import { fieldErrors, type FieldErrors } from "@/shared/utils/fieldErrors";
import { resetPassword } from "../api";
import { newPasswordSchema } from "../schemas";

const { email, code } = defineProps<{ email: string; code: string }>();

const emit = defineEmits<{ submit: []; restart: [] }>();

const form = reactive({ password: "", confirmation: "" });
const errors = ref<FieldErrors<typeof form>>({});
const formError = ref<string | null>(null);
const expired = ref(false);
const submitting = ref(false);

onMounted(() => document.getElementById("new-password")?.focus());

async function submit(): Promise<void> {
  formError.value = null;
  const parsed = newPasswordSchema.safeParse(form);
  errors.value = parsed.success ? {} : fieldErrors(parsed.error);
  if (!parsed.success) return;

  submitting.value = true;
  try {
    await resetPassword(email, code, parsed.data.password);
    emit("submit");
  } catch (error) {
    expired.value = error instanceof ApiError && error.status === 422;
    formError.value = expired.value
      ? "Ce code a expiré."
      : "Le mot de passe n'a pas pu être enregistré. Réessayez.";
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <h1 class="font-serif text-3xl font-semibold tracking-tight">
    Nouveau mot de passe
  </h1>
  <p class="mt-2 text-sm leading-relaxed text-ink-muted">
    Choisissez un mot de passe d'au moins 8 caractères.
  </p>

  <form novalidate class="mt-7 space-y-4" @submit.prevent="submit">
    <FormField
      id="new-password"
      label="Nouveau mot de passe"
      :error="errors.password"
    >
      <PasswordInput
        id="new-password"
        v-model="form.password"
        autocomplete="new-password"
        :aria-invalid="!!errors.password"
      />
    </FormField>

    <FormField
      id="new-password-confirmation"
      label="Confirmer le mot de passe"
      :error="errors.confirmation"
    >
      <PasswordInput
        id="new-password-confirmation"
        v-model="form.confirmation"
        autocomplete="new-password"
        :aria-invalid="!!errors.confirmation"
      />
    </FormField>

    <p
      v-if="formError"
      role="alert"
      class="rounded-2xl bg-status-cr/10 px-4 py-3 text-sm text-status-cr"
    >
      {{ formError }}
      <button
        v-if="expired"
        type="button"
        class="cursor-pointer font-medium underline underline-offset-4"
        @click="emit('restart')"
      >
        Recommencer
      </button>
    </p>

    <BaseButton
      type="submit"
      size="lg"
      class="mt-2 w-full"
      :disabled="submitting"
    >
      {{ submitting ? "Enregistrement…" : "Enregistrer le mot de passe" }}
    </BaseButton>
  </form>
</template>
