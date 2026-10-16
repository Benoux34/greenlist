<script setup lang="ts">
import { computed, onMounted, ref, useTemplateRef } from "vue";
import BaseButton from "@/shared/components/BaseButton.vue";
import { useAsyncData } from "@/shared/composables/useAsyncData";
import { useAuthStore } from "@/features/auth/store";
import { acceptInvitation, fetchInvitation } from "../api";

const { token } = defineProps<{ token: string }>();

const emit = defineEmits<{ accepted: []; close: [] }>();

const auth = useAuthStore();
const { data: invitation, hasFailed } = useAsyncData(
  () => fetchInvitation(token),
  [() => token],
);

const dialog = useTemplateRef<HTMLDialogElement>("dialog");
const isAccepting = ref(false);
const acceptFailed = ref(false);

const isOwn = computed(() => invitation.value?.inviter.id === auth.user?.id);
const canAccept = computed(() => invitation.value !== null && !isOwn.value);

const content = computed(() => {
  if (hasFailed.value)
    return {
      title: "Lien plus valable",
      text: "Cette invitation a expiré ou a déjà servi. Demandez un nouveau lien à votre ami.",
    };
  if (invitation.value === null)
    return { title: "Invitation", text: "Chargement de l'invitation…" };
  if (isOwn.value)
    return {
      title: "C'est votre lien",
      text: "Envoyez-le à un ami : c'est à lui de l'ouvrir pour devenir votre ami.",
    };

  return {
    title: `${invitation.value.inviter.firstName} vous invite`,
    text: "En acceptant, vous verrez chacun les observations de l'autre sur votre carte, sauf celles qu'il garde privées.",
  };
});

onMounted(() => dialog.value?.showModal());

async function accept(): Promise<void> {
  isAccepting.value = true;
  acceptFailed.value = false;

  try {
    await acceptInvitation(token);
    emit("accepted");
  } catch {
    acceptFailed.value = true;
  } finally {
    isAccepting.value = false;
  }
}
</script>

<template>
  <dialog
    ref="dialog"
    class="m-auto w-[calc(100%-2rem)] max-w-sm rounded-3xl bg-surface text-ink shadow-xl backdrop:bg-ink/30 backdrop:backdrop-blur-sm"
    aria-labelledby="invitation-title"
    @cancel.prevent="emit('close')"
    @click.self="emit('close')"
  >
    <div class="p-6">
      <h2
        id="invitation-title"
        class="font-serif text-xl font-semibold tracking-tight"
      >
        {{ content.title }}
      </h2>
      <p aria-live="polite" class="mt-2 text-sm leading-relaxed text-ink-muted">
        {{ content.text }}
      </p>
      <p v-if="acceptFailed" role="alert" class="mt-3 text-sm text-status-cr">
        L'invitation n'a pas pu être acceptée. Réessayez.
      </p>

      <div class="mt-6 flex justify-end gap-2">
        <button
          type="button"
          class="h-11 cursor-pointer rounded-full px-4 text-sm font-medium text-ink-muted transition-colors hover:bg-paper-muted hover:text-ink focus-visible:ring-2 focus-visible:ring-accent/40 focus-visible:outline-none"
          @click="emit('close')"
        >
          {{ canAccept ? "Ignorer" : "Fermer" }}
        </button>
        <BaseButton
          v-if="canAccept"
          type="button"
          size="lg"
          :disabled="isAccepting"
          @click="accept"
        >
          {{ isAccepting ? "Acceptation…" : "Accepter" }}
        </BaseButton>
      </div>
    </div>
  </dialog>
</template>
