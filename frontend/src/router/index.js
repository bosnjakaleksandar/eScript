import { createRouter, createWebHistory } from "vue-router";
import DashboardView from "../views/Dashboard.vue";
import AdminDashboard from "../views/AdminDashboard.vue";
import AuthView from "../views/Auth.vue";

const routes = [
  {
    path: "/",
    name: "Auth",
    component: AuthView,
  },
  {
    path: "/dashboard",
    name: "Dashboard",
    component: DashboardView,
  },
  {
    path: "/admin-dashboard",
    name: "AdminDashboard",
    component: AdminDashboard,
    meta: { requiresAdmin: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const user = JSON.parse(localStorage.getItem("user"));

  if (to.meta.requiresAdmin) {
    if (user && user.role === "admin") {
      next();
    } else {
      alert("You do not have access to this page.");
      next("/");
    }
  } else {
    next();
  }
});

export default router;
