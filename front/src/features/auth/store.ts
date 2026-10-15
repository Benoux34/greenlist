import { ref } from "vue";
import { defineStore } from "pinia";
import { ApiError } from "@/shared/api/http";
import * as api from "./api";
import type { RegisterInput, User } from "./types";

const useAuthStore = defineStore("auth", () => {
  const user = ref<User | null>(null);
  let loading: Promise<void> | null = null;

  function load(): Promise<void> {
    loading ??= api.fetchMe().then(
      (me) => {
        user.value = me;
      },
      () => {
        user.value = null;
      },
    );

    return loading;
  }

  async function login(email: string, password: string): Promise<void> {
    await api.login(email, password);
    user.value = await api.fetchMe();
  }

  async function register(input: RegisterInput): Promise<void> {
    await api.register(input);
    await login(input.email, input.password);
  }

  async function logout(): Promise<void> {
    await api.logout().catch((error: unknown) => {
      if (!(error instanceof ApiError)) throw error;
    });
    user.value = null;
  }

  return { user, load, login, register, logout };
});

export { useAuthStore };
