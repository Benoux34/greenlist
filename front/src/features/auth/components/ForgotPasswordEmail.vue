<script setup lang="ts">
import { reactive, ref } from "vue";
import BaseButton from "@/shared/components/BaseButton.vue";
import BaseInput from "@/shared/components/BaseInput.vue";
import FormField from "@/shared/components/FormField.vue";
import { fieldErrors, type FieldErrors } from "@/shared/utils/fieldErrors";
import { resetEmailSchema } from "../schemas";
import { requestPasswordReset } from "../api";

const { email } = defineProps<{ email: string }>();

const emit = defineEmits<{ submit: [email: string] }>();

const form = reactive({ email });
const errors = ref<FieldErrors<typeof form>>({});

const formError = ref<string | null>(null);
const submitting = ref(false);

async function submit(): Promise<void> {
  formError.value = null;
  const parsed = resetEmailSchema.safeParse(form);
  errors.value = parsed.success ? {} : fieldErrors(parsed.error);
  if (!parsed.success) return;

  submitting.value = true;
  try {
    await requestPasswordReset(parsed.data.email);
    emit("submit", parsed.data.email);
  } catch {
    formError.value =
      "Le code n'a pas pu être envoyé. Réessayez dans un instant.";
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <h1 class="font-serif text-3xl font-semibold tracking-tight">
    Mot de passe oublié
  </h1>
  <p class="mt-2 text-sm leading-relaxed text-ink-muted">
    Indiquez l'adresse de votre compte : nous vous enverrons un code à 6
    caractères pour choisir un nouveau mot de passe.
  </p>

  <form novalidate class="mt-7 space-y-4" @submit.prevent="submit">
    <FormField
      id="reset-email"
      label="Adresse électronique"
      :error="errors.email"
    >
      <BaseInput
        id="reset-email"
        v-model="form.email"
        type="email"
        placeholder="nom@exemple.fr"
        autocomplete="email"
        :aria-invalid="!!errors.email"
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
      {{ submitting ? "Envoi…" : "Recevoir un code" }}
    </BaseButton>
  </form>
</template>
