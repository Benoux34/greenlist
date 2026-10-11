import { createRouter, createWebHistory } from "vue-router";
import AuthLayout from "@/features/auth/components/AuthLayout.vue";
import { useAuthStore } from "@/features/auth/store";
import LoginView from "@/features/auth/views/LoginView.vue";
import RegisterView from "@/features/auth/views/RegisterView.vue";
import HomeView from "@/features/home/views/HomeView.vue";
import AppLayout from "./AppLayout.vue";

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
      component: AuthLayout,
      meta: { public: true },
      children: [{ path: "", name: "login", component: LoginView }],
    },
    {
      path: "/register",
      component: AuthLayout,
      meta: { public: true },
      children: [{ path: "", name: "register", component: RegisterView }],
    },
    {
      path: "/",
      component: AppLayout,
      children: [{ path: "", name: "home", component: HomeView }],
    },
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
