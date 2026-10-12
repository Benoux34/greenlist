import { createApp } from "vue";
import { createPinia } from "pinia";
import App from "./App.vue";
import { router } from "./router";
import { useAuthStore } from "@/features/auth/store";
import { setSessionLostHandler } from "@/shared/api/http";
import "@/assets/main.css";

const app = createApp(App).use(createPinia()).use(router);

setSessionLostHandler(() => {
  const auth = useAuthStore();
  if (auth.user === null) return;

  auth.user = null;
  void router.replace({ name: "login" });
});

app.mount("#app");
