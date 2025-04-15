import { createRouter, createWebHistory } from "vue-router";
import AuthView from "../views/Auth.vue";
import DashboardView from "../views/Dashboard.vue";
import MyNotes from "../views/MyNotes.vue";
import Subjects from "../views/Subjects.vue";
import SubjectNotesView from "../views/SubjectNotes.vue";
import RankingsView from "../views/Rankings.vue";
import MyProfileView from '../views/MyProfile.vue'
import sessionApiService from "../services/api/sessionApiService";

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
    path: "/mynotes",
    name: "MyNotes",
    component: MyNotes,
  },
  {
    path: "/subjects",
    name: "Subjects",
    component: Subjects,
  },
  {
    path: "/subjects/:subjectId/notes",
    name: "SubjectNotes",
    component: SubjectNotesView,
  },
  {
    path: "/rankings",
    name: "Rankings",
    component: RankingsView,
  },
  {
    path: '/profile',
    name: 'MyProfile',
    component: MyProfileView,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  if (to.path === "/") {
    return next();
  }

  try {
    const response = await sessionApiService.getLoggedUser();
    console.log(response);

    if (response.success && response.user) {
      localStorage.setItem("user", JSON.stringify(response.user));
    }
  } catch (error) {
    console.error("Failed to fetch logged user:", error);
    localStorage.removeItem("user");
  }

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
