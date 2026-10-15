import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/features/auth/store";
import HomeView from "@/views/HomeView.vue";
import LoginView from "@/views/LoginView.vue";
import RegisterView from "@/views/RegisterView.vue";

declare module "vue-router" {
  interface RouteMeta {
    public?: boolean;
  }
}

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: "/login",
      name: "login",
      component: LoginView,
      meta: { public: true },
    },
    {
      path: "/register",
      name: "register",
      component: RegisterView,
      meta: { public: true },
    },
    { path: "/", name: "home", component: HomeView },
    { path: "/:pathMatch(.*)*", redirect: { name: "home" } },
  ],
});

router.beforeEach(async (to) => {
  const auth = useAuthStore();
  await auth.load();

  if (!to.meta.public && !auth.user) return { name: "login" };
  if (to.meta.public && auth.user) return { name: "home" };
});

export { router };
