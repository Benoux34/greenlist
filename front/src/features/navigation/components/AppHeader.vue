<script setup lang="ts">
import AppLogo from "@/shared/components/AppLogo.vue";
import BaseButton from "@/shared/components/BaseButton.vue";
import { GLASS } from "@/shared/utils/overlay";
import AccountMenu from "@/features/auth/components/AccountMenu.vue";

const emit = defineEmits<{ create: [] }>();
</script>

<template>
  <header
    class="pointer-events-none fixed inset-x-0 top-0 z-30 px-4 pt-[max(1rem,env(safe-area-inset-top))] lg:px-6"
  >
    <div class="flex items-center justify-between gap-3">
      <div class="pointer-events-auto rounded-full py-1.5 pr-4 pl-1.5" :class="GLASS">
        <AppLogo />
      </div>

      <div class="pointer-events-auto flex items-center gap-2">
        <div class="hidden lg:block">
          <BaseButton
            type="button"
            size="lg"
            class="shadow-md"
            @click="emit('create')"
          >
            <svg
              viewBox="0 0 24 24"
              class="size-4"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
              stroke-linecap="round"
              aria-hidden="true"
            >
              <path d="M12 5v14M5 12h14" />
            </svg>
            Nouvelle observation
          </BaseButton>
        </div>

        <AccountMenu />
      </div>
    </div>
  </header>
</template>
