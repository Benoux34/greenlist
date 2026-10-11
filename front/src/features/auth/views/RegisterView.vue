<script setup lang="ts">
import { reactive, ref } from "vue";
import { RouterLink, useRouter } from "vue-router";
import { ApiError } from "@/shared/api/http";
import BaseButton from "@/shared/components/BaseButton.vue";
import BaseInput from "@/shared/components/BaseInput.vue";
import FormField from "@/shared/components/FormField.vue";
import { fieldErrors, type FieldErrors } from "@/shared/utils/fieldErrors";
import { useAuthStore } from "../store";
import RedlistButton from "../components/RedlistButton.vue";
import { registerSchema } from "../schemas";

const auth = useAuthStore();
const router = useRouter();

const form = reactive({ firstName: "", email: "", password: "" });
const errors = ref<FieldErrors<typeof form>>({});
const formError = ref<string | null>(null);
const submitting = ref(false);

async function submit(): Promise<void> {
  formError.value = null;
  const parsed = registerSchema.safeParse(form);
  errors.value = parsed.success ? {} : fieldErrors(parsed.error);
  if (!parsed.success) return;

  submitting.value = true;
  try {
    await auth.register(parsed.data);
    await router.replace({ name: "home" });
  } catch (error) {
    if (error instanceof ApiError && error.status === 409)
      errors.value = { email: "Cette adresse est déjà utilisée." };
    else formError.value = "L'inscription a échoué. Réessayez.";
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <div
    class="w-full max-w-lg border border-paper-border bg-paper p-8 shadow-xs sm:p-10"
  >
    <h1 class="mb-2 font-serif text-3xl font-medium tracking-tight">
      Créer un compte
    </h1>
    <p class="mb-8 text-sm leading-relaxed text-ink-muted">
      Rejoignez Greenlist pour consigner vos observations et les retrouver sur
      votre carte.
    </p>

    <form novalidate @submit.prevent="submit">
      <FormField
        id="register-first-name"
        label="Prénom"
        :error="errors.firstName"
        class="mb-5"
      >
        <BaseInput
          id="register-first-name"
          v-model="form.firstName"
          type="text"
          placeholder="Alexandre"
          autocomplete="given-name"
          :aria-invalid="!!errors.firstName"
        />
      </FormField>

      <FormField
        id="register-email"
        label="Adresse électronique"
        :error="errors.email"
        class="mb-5"
      >
        <BaseInput
          id="register-email"
          v-model="form.email"
          type="email"
          placeholder="nom@exemple.fr"
          autocomplete="email"
          :aria-invalid="!!errors.email"
        />
      </FormField>

      <FormField
        id="register-password"
        label="Mot de passe"
        :error="errors.password"
        class="mb-6"
      >
        <BaseInput
          id="register-password"
          v-model="form.password"
          type="password"
          placeholder="Au moins 8 caractères"
          autocomplete="new-password"
          :aria-invalid="!!errors.password"
        />
      </FormField>

      <p v-if="formError" role="alert" class="mb-4 text-sm text-status-cr">
        {{ formError }}
      </p>

      <BaseButton
        type="submit"
        size="lg"
        class="mb-6 w-full"
        :disabled="submitting"
      >
        {{ submitting ? "Création…" : "Créer mon compte" }}
      </BaseButton>
    </form>

    <RedlistButton />

    <div
      class="border-t border-paper-border pt-5 text-center text-xs text-ink-muted"
    >
      Vous avez déjà un compte ?
      <RouterLink
        :to="{ name: 'login' }"
        class="font-medium text-ink underline underline-offset-4 transition-opacity hover:opacity-80"
      >
        Se connecter
      </RouterLink>
    </div>
  </div>
</template>
