<script setup lang="ts">
import { reactive, ref } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import { ApiError } from "@/shared/api/http";
import BaseButton from "@/shared/components/BaseButton.vue";
import BaseInput from "@/shared/components/BaseInput.vue";
import FormField from "@/shared/components/FormField.vue";
import { fieldErrors, type FieldErrors } from "@/shared/utils/fieldErrors";
import { useAuthStore } from "../store";
import RedlistButton from "./RedlistButton.vue";
import { loginSchema } from "../schemas";

const auth = useAuthStore();
const router = useRouter();

const form = reactive({ email: "", password: "" });
const errors = ref<FieldErrors<typeof form>>({});
// Set by the API when a sign-in through Redlist comes back without a session.
const REDLIST_ERRORS: Record<string, string> = {
  email_taken:
    "Un compte existe déjà avec cette adresse. Connectez-vous avec votre mot de passe.",
  redlist: "La connexion via Redlist n'a pas abouti. Réessayez.",
};

const route = useRoute();
const formError = ref<string | null>(
  REDLIST_ERRORS[String(route.query.error)] ?? null,
);
const submitting = ref(false);

async function submit(): Promise<void> {
  formError.value = null;
  const parsed = loginSchema.safeParse(form);
  errors.value = parsed.success ? {} : fieldErrors(parsed.error);
  if (!parsed.success) return;

  submitting.value = true;
  try {
    await auth.login(parsed.data.email, parsed.data.password);
    await router.replace({ name: "home" });
  } catch (error) {
    formError.value =
      error instanceof ApiError && error.status === 401
        ? "Adresse électronique ou mot de passe incorrect."
        : "La connexion a échoué. Réessayez.";
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <h1 class="font-serif text-3xl font-semibold tracking-tight">Se connecter</h1>
  <p class="mt-2 text-sm leading-relaxed text-ink-muted">
    Retrouvez toutes vos observations sur votre carte.
  </p>

  <form novalidate class="mt-7 space-y-4" @submit.prevent="submit">
    <FormField id="login-email" label="Adresse électronique" :error="errors.email">
      <BaseInput
        id="login-email"
        v-model="form.email"
        type="email"
        placeholder="nom@exemple.fr"
        autocomplete="email"
        :aria-invalid="!!errors.email"
      />
    </FormField>

    <FormField id="login-password" label="Mot de passe" :error="errors.password">
      <BaseInput
        id="login-password"
        v-model="form.password"
        type="password"
        placeholder="••••••••••••"
        autocomplete="current-password"
        :aria-invalid="!!errors.password"
      />
    </FormField>

    <p v-if="formError" role="alert" class="rounded-2xl bg-status-cr/10 px-4 py-3 text-sm text-status-cr">
      {{ formError }}
    </p>

    <BaseButton type="submit" size="lg" class="mt-2 w-full" :disabled="submitting">
      {{ submitting ? "Connexion…" : "Se connecter" }}
    </BaseButton>
  </form>

  <RedlistButton />

  <p class="mt-6 text-center text-sm text-ink-muted">
    Pas encore de compte ?
    <RouterLink :to="{ name: 'register' }" class="font-medium text-accent underline-offset-4 hover:underline">
      Créer un compte
    </RouterLink>
  </p>
</template>
