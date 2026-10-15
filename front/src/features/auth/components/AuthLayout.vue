<script setup lang="ts">
import AppLogo from "@/shared/components/AppLogo.vue";
import { GLASS } from "@/shared/utils/overlay";
import { CATEGORIES } from "@/features/species/categories";
import TopoBackdrop from "./TopoBackdrop.vue";

const year = new Date().getFullYear();

const SAMPLES = [
  {
    name: "Renard roux",
    detail: "Lisière du bois · 2 individus",
    category: "fauna",
    place: "top-0 left-0 -rotate-2",
  },
  {
    name: "Chêne pédonculé",
    detail: "Bord du chemin · 1 pied",
    category: "flora",
    place: "top-[5.5rem] left-28 rotate-1",
  },
  {
    name: "Amanite tue-mouches",
    detail: "Sous les bouleaux",
    category: "fungi",
    place: "top-44 left-8 -rotate-1",
  },
] as const;
</script>

<template>
  <div
    class="relative isolate flex min-h-dvh flex-col overflow-hidden bg-paper text-ink"
  >
    <div
      class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top_left,var(--color-accent-soft),transparent_60%)]"
    ></div>
    <TopoBackdrop
      class="absolute inset-0 -z-10 h-full w-full text-accent opacity-[0.14]"
    />

    <header class="px-4 pt-[max(1rem,env(safe-area-inset-top))] lg:px-8">
      <div class="inline-flex rounded-full py-1.5 pr-4 pl-1.5" :class="GLASS">
        <AppLogo />
      </div>
    </header>

    <main
      class="mx-auto grid w-full max-w-6xl flex-1 items-center gap-12 px-4 py-8 lg:grid-cols-[minmax(0,1fr)_27rem] lg:px-8"
    >
      <section class="hidden lg:block">
        <p class="text-sm font-semibold tracking-wider text-accent uppercase">
          Carnet d'observation de la nature
        </p>
        <p
          class="mt-4 max-w-xl font-serif text-5xl leading-[1.05] font-semibold tracking-tight text-balance"
        >
          Tout ce que vous croisez dehors, sur une seule carte.
        </p>
        <p class="mt-5 max-w-md text-lg leading-relaxed text-ink-muted">
          Un animal, un arbre, un champignon : notez-le en quelques secondes,
          photo et position comprises.
        </p>

        <ul class="relative mt-12 h-64 max-w-md" aria-hidden="true">
          <li
            v-for="sample in SAMPLES"
            :key="sample.name"
            class="absolute flex w-72 items-center gap-3 rounded-2xl px-4 py-3"
            :class="[GLASS, sample.place]"
          >
            <span
              class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-paper-muted"
            >
              <span
                class="size-3 rounded-full ring-2 ring-surface"
                :class="CATEGORIES[sample.category].dot"
              ></span>
            </span>
            <span class="min-w-0">
              <span class="block truncate text-sm font-medium">
                {{ sample.name }}
              </span>
              <span class="block truncate text-xs text-ink-muted">
                {{ CATEGORIES[sample.category].label }} · {{ sample.detail }}
              </span>
            </span>
          </li>
        </ul>
      </section>

      <div
        class="w-full max-w-md justify-self-center rounded-3xl bg-surface p-6 shadow-xl ring-1 ring-ink/5 sm:p-8 lg:max-w-none"
      >
        <slot />
      </div>
    </main>

    <footer
      class="px-4 pb-[max(1rem,env(safe-area-inset-bottom))] text-center text-xs text-ink-faint"
    >
      © {{ year }} Greenlist · Carnet d'observation de la nature
    </footer>
  </div>
</template>
