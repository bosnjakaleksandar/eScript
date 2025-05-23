<script>
import DashboardData from "../../services/api/DashboardData";
import LastNote from "../../services/api/LastNote";
import PopularSubjectsService from "../../services/api/PopularSubjects";
import RankingService from "../../services/api/Ranking";

import WelcomeBanner from "./WelcomeBanner.vue";
import StatCard from "./StatCard.vue";
import PopularSubjectsList from "./PopularSubjectsList.vue";
import LastNoteCard from "./LastNoteCard.vue";
import AddSubjectModal from "./AddSubjectModal.vue";

export default {
  name: "DashboardContent",
  components: {
    WelcomeBanner,
    StatCard,
    PopularSubjectsList,
    LastNoteCard,
    AddSubjectModal,
  },
  data() {
    return {
      userRole: "",
      userName: "",
      totalUsers: 0,
      subjectsCount: 0,
      errorMessage: "",
      successMessage: "",
      isLoading: false,
      now: new Date(),
      showAddSubjectModal: false,
      lastNote: null,
      isLoadingLastNote: false,
      lastNoteError: "",
      popularSubjects: [],
      isLoadingPopularSubjects: false,
      popularSubjectsError: "",

      rankings: [],
      isLoadingRankings: false,
      rankingsError: "",
    };
  },
  methods: {
    async fetchDashboardData() {
      this.isLoading = true;
      this.errorMessage = "";
      try {
        const response = await DashboardData.getDashboardData();
        if (response && response.success) {
          this.totalUsers = response.totalUsers;
          this.subjectsCount = response.totalSubjects;
        } else {
          this.errorMessage =
            "Failed to load dashboard data" +
            (response?.error ? `: ${response.error}` : "");
        }
      } catch (err) {
        this.errorMessage = "Network error while loading dashboard data";
      } finally {
        this.isLoading = false;
      }
    },

    async fetchLastNote() {
      this.isLoadingLastNote = true;
      this.lastNoteError = "";
      try {
        const response = await LastNote.getLastNote();
        if (response && response.success) {
          this.lastNote = response.script;
        } else {
          this.lastNoteError = response?.error || "Failed to load last script.";
        }
      } catch (err) {
        this.lastNoteError = `Network error: ${
          err.message || "Could not fetch last script."
        }`;
      } finally {
        this.isLoadingLastNote = false;
      }
    },

    async fetchPopularSubjects() {
      this.isLoadingPopularSubjects = true;
      this.popularSubjectsError = "";
      try {
        const response = await PopularSubjectsService.getPopularSubjects();
        if (response && response.success) {
          this.popularSubjects = response.subjects || [];
        } else {
          this.popularSubjectsError =
            response?.error || "Failed to load popular subjects.";
        }
      } catch (err) {
        this.popularSubjectsError = `Network error: ${
          err.message || "Could not fetch popular subjects."
        }`;
      } finally {
        this.isLoadingPopularSubjects = false;
      }
    },

    async fetchUserRankings() {
      this.isLoadingRankings = true;
      this.rankingsError = "";
      try {
        const response = await RankingService.getUserRankings();
        if (response && response.success) {
          this.rankings = response.rankings || [];
        } else {
          this.rankingsError = response?.error || "Failed to load rankings.";
          console.error("Error fetching rankings:", response);
        }
      } catch (err) {
        this.rankingsError = `Network error: ${
          err.message || "Could not fetch rankings."
        }`;
        console.error("Network error fetching rankings:", err);
      } finally {
        this.isLoadingRankings = false;
      }
    },

    checkUserRole() {
      try {
        const userData = localStorage.getItem("user");
        if (userData) {
          const user = JSON.parse(userData);
          this.userRole = user.role || "";
          this.userName =
            user.username || user.name || user.first_name || "User";
        } else {
          this.userName = "Guest";
          this.userRole = "guest";
        }
      } catch (e) {
        this.userName = "User";
        this.userRole = "";
      }
    },

    handleSubjectAdded(addedSubject) {
      console.log("Subject added:", addedSubject);
      this.subjectsCount++;
      this.fetchPopularSubjects();
    },

    getUserImageUrl(imagePath) {
      const defaultImage = "/img/DefaultAvatar.jpg";
      const backendBaseUrl = "http://localhost:8002";

      if (imagePath) {
        if (imagePath.startsWith("http")) {
          return imagePath;
        } else if (imagePath.startsWith("/")) {
          return `${backendBaseUrl}${imagePath}`;
        } else {
          return `${backendBaseUrl}/${imagePath}`;
        }
      }
      return defaultImage;
    },
  },
  mounted() {
    this.checkUserRole();
    Promise.allSettled([
      this.fetchDashboardData(),
      this.fetchLastNote(),
      this.fetchPopularSubjects(),
      this.fetchUserRankings(),
    ]);
  },
  computed: {
    formattedDate() {
      const options = {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
      };
      return this.now.toLocaleString("sr-Latn-RS", options);
    },
    dashboardTitle() {
      return this.userRole === "admin" ? "Admin Dashboard" : "Dashboard";
    },
    topRankedUsers() {
      return this.rankings.slice(0, 3);
    },
  },
};
</script>
<template>
  <div class="main-content content">
    <div class="container-fluid mt-3" v-if="errorMessage">
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ errorMessage }}
        <button
          type="button"
          class="btn-close"
          aria-label="Close"
          @click="errorMessage = ''"
        ></button>
      </div>
    </div>
    <div class="container-fluid mt-3" v-if="successMessage">
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ successMessage }}
        <button
          type="button"
          class="btn-close"
          aria-label="Close"
          @click="successMessage = ''"
        ></button>
      </div>
    </div>

    <div class="container-fluid text-center py-5" v-if="isLoading">
      <div
        class="spinner-border text-primary"
        role="status"
        style="width: 3rem; height: 3rem"
      >
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Loading dashboard data...</p>
    </div>

    <div v-else class="container-fluid">
      <WelcomeBanner :user-name="userName" :formatted-date="formattedDate" />

      <div class="row mt-5 align-items-start">
        <div class="col-md-9">
          <div class="row align-items-center mb-3">
            <div class="col d-flex align-items-center">
              <h2 class="mb-0 me-3">{{ dashboardTitle }}</h2>
              <button
                v-if="userRole === 'admin'"
                type="button"
                class="btn btn-primary btn-sm"
                @click="showAddSubjectModal = true"
              >
                <i class="fa-solid fa-plus me-1"></i> Add New Subject
              </button>
            </div>
          </div>

          <div class="row dash-row">
            <StatCard
              icon-class="fa-solid fa-users"
              :value="totalUsers"
              label="Registered Users"
            />
            <StatCard
              icon-class="fa-solid fa-envelopes-bulk"
              :value="subjectsCount"
              label="Created Subjects"
            />
            <StatCard
              icon-class="fa-solid fa-book"
              :value="0"
              label="Active Courses"
            />
          </div>

          <PopularSubjectsList
            :subjects="popularSubjects"
            :is-loading="isLoadingPopularSubjects"
            :error="popularSubjectsError"
          />
        </div>

        <div class="col-md-3">
          <div class="row align-items-end mb-3">
            <div class="col">
              <h2 class="text-md-start">Best Students</h2>
            </div>
          </div>
          <div class="students">
            <div
              v-if="isLoadingRankings"
              class="text-center text-muted small py-2"
            >
              Loading rankings...
            </div>
            <div
              v-else-if="rankingsError"
              class="text-center text-danger small py-2"
            >
              {{ rankingsError }}
            </div>
            <div
              v-else-if="topRankedUsers.length === 0"
              class="text-center text-muted small py-2"
            >
              No ranked students yet.
            </div>
            <div v-else class="row mb-3">
              <div
                v-for="user in topRankedUsers"
                :key="user.user_id"
                class="col-4 student"
              >
                <router-link
                  :to="{
                    name: 'UserProfile',
                    params: { userId: user.user_id },
                  }"
                  :title="user.author_name"
                >
                  <img
                    :src="getUserImageUrl(user.profile_image_path)"
                    class="img-fluid rounded-circle"
                    :alt="user.author_name"
                  />
                </router-link>
              </div>
            </div>

            <LastNoteCard
              :note="lastNote"
              :is-loading="isLoadingLastNote"
              :error="lastNoteError"
            />
          </div>
        </div>
      </div>
    </div>

    <AddSubjectModal
      :show="showAddSubjectModal"
      @update:show="showAddSubjectModal = $event"
      @close="showAddSubjectModal = false"
      @subject-added="handleSubjectAdded"
    />
  </div>
</template>
<style scoped>
.main-content {
  width: 100%;
}
.students img {
  border: 3px solid rgba(0, 74, 173, 1);
}

@media (max-width: 1441px) {
  .student {
    padding: 5px;
  }
}
</style>
