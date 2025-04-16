import { createRouter, createWebHistory } from "vue-router";
import AuthView from "../views/Auth.vue";
import DashboardView from "../views/Dashboard.vue";
import MyNotes from "../views/MyNotes.vue";
import Subjects from "../views/Subjects.vue";
import SubjectNotesView from "../views/SubjectNotes.vue";
import RankingsView from "../views/Rankings.vue";
import UserProfileView from "../views/UserProfile.vue";
import UnauthorizedView from "../views/Unauthorized.vue";
import sessionApiService from "../services/api/sessionApiService";

const routes = [
  {
    path: "/",
    name: "Auth",
    component: AuthView,
    meta: { requiresAuth: false },
  },
  {
    path: "/dashboard",
    name: "Dashboard",
    component: DashboardView,
    meta: { requiresAuth: true },
  },
  {
    path: "/my-notes",
    name: "MyNotes",
    component: MyNotes,
    meta: { requiresAuth: true },
  },
  {
    path: "/subjects",
    name: "Subjects",
    component: Subjects,
    meta: { requiresAuth: true },
  },
  {
    path: "/profile/:userId",
    name: "UserProfile",
    component: UserProfileView,
    props: true,
    meta: { requiresAuth: true },
  },
  {
    path: "/rankings",
    name: "Rankings",
    component: RankingsView,
    meta: { requiresAuth: true },
  },
  {
    path: "/subjects/:subjectId/notes",
    name: "SubjectNotes",
    component: SubjectNotesView,
    props: true,
    meta: { requiresAuth: true },
  },
  {
    path: "/unauthorized",
    name: "Unauthorized",
    component: UnauthorizedView,
    meta: { requiresAuth: false },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  if (to.meta.requiresAuth === false || to.name === "Auth") {
    return next();
  }

  let user = null;
  try {
    const response = await sessionApiService.getLoggedUser();
    if (response.success && response.user) {
      localStorage.setItem("user", JSON.stringify(response.user));
      user = response.user;
      console.log("Session user fetched/updated:", user);
    } else {
      localStorage.removeItem("user");
    }
  } catch (error) {
    console.error("Failed to fetch session user:", error);
    localStorage.removeItem("user");
  }

  if (!user) {
    const userData = localStorage.getItem("user");
    if (userData) {
      user = JSON.parse(userData);
    }
  }

  if (to.meta.requiresAuth) {
    if (user) {
      if (to.meta.requiresAdmin) {
        if (user.role === "admin") {
          next();
        } else {
          console.warn(
            `User ${user.username} tried to access admin route ${to.path}`
          );
          next({ name: "Unauthorized" });
        }
      } else {
        next();
      }
    } else {
      console.log(`Unauthorized access attempt to ${to.path}`);
      next({ name: "Unauthorized" });
    }
  } else {
    next();
  }
});

export default router;
