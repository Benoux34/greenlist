<script setup lang="ts">
import { ref } from "vue";
import BaseButton from "@/shared/components/BaseButton.vue";
import BaseInput from "@/shared/components/BaseInput.vue";
import { createInvitation, invitationUrl } from "../api";

const link = ref<string | null>(null);
const isCreating = ref(false);
const hasFailed = ref(false);
const copied = ref<boolean | null>(null);
const canShare = "share" in navigator;

async function create(): Promise<void> {
  isCreating.value = true;
  hasFailed.value = false;
  copied.value = null;

  try {
    link.value = invitationUrl((await createInvitation()).token);
  } catch {
    hasFailed.value = true;
  } finally {
    isCreating.value = false;
  }
}

function share(url: string): void {
  navigator
    .share({ title: "Fauneflore", text: "Partageons nos observations.", url })
    .catch(() => {});
}

async function copy(url: string): Promise<void> {
  try {
    await navigator.clipboard.writeText(url);
    copied.value = true;
  } catch {
    copied.value = false;
  }
}
</script>

<template>
  <div class="rounded-2xl bg-paper-muted p-4">
    <template v-if="link === null">
      <p class="text-sm leading-relaxed text-ink-muted">
        Envoyez un lien à un ami. Dès qu'il l'accepte, vous voyez chacun les
        observations de l'autre.
      </p>
      <BaseButton
        type="button"
        class="mt-3"
        :disabled="isCreating"
        @click="create"
      >
        {{ isCreating ? "Création du lien…" : "Inviter un ami" }}
      </BaseButton>
    </template>

    <template v-else>
      <label for="invitation-link" class="text-sm font-medium">
        Lien d'invitation
      </label>
      <BaseInput
        id="invitation-link"
        :model-value="link"
        readonly
        class="mt-1.5"
        @focus="($event.target as HTMLInputElement).select()"
      />
      <p class="mt-1.5 text-xs text-ink-muted">
        Valable 7 jours, pour une seule personne.
      </p>

      <div class="mt-3 flex flex-wrap items-center gap-2">
        <BaseButton v-if="canShare" type="button" @click="share(link)">
          Partager
        </BaseButton>
        <button
          type="button"
          class="h-9 cursor-pointer rounded-full bg-surface px-4 text-sm font-medium ring-1 ring-paper-border transition-colors hover:bg-paper-muted focus-visible:ring-2 focus-visible:ring-accent/40 focus-visible:outline-none"
          @click="copy(link)"
        >
          Copier le lien
        </button>
        <button
          type="button"
          class="h-9 cursor-pointer rounded-full px-3 text-sm text-ink-muted underline-offset-4 hover:text-ink hover:underline"
          :disabled="isCreating"
          @click="create"
        >
          Nouveau lien
        </button>
      </div>
      <p
        v-if="copied !== null"
        role="status"
        class="mt-2 text-sm text-ink-muted"
      >
        {{
          copied ? "Lien copié." : "Copie impossible : sélectionnez le lien."
        }}
      </p>
    </template>

    <p v-if="hasFailed" role="alert" class="mt-2 text-sm text-status-cr">
      Le lien n'a pas pu être créé. Réessayez.
    </p>
  </div>
</template>
