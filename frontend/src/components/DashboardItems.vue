<script>
import DashboardData from "../services/api/DashboardData";
import Subjects from "../services/api/Subjects";

export default {
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
    };
  },
  methods: {
    async fetchDashboardData() {
      try {
        this.isLoading = true;
        const response = await DashboardData.getDashboardData();
        if (response && response.success) {
          this.totalUsers = response.totalUsers;
          this.subjectsCount = response.totalSubjects;
        } else {
          console.error(
            "Error fetching metrics:",
            response ? response.error : "No response"
          );
          this.errorMessage =
            "Failed to load dashboard data" +
            (response && response.error ? `: ${response.error}` : "");
        }
      } catch (err) {
        console.error("Fetch error in fetchDashboardData:", err);
        this.errorMessage = "Network error while loading dashboard data";
        if (err.response) {
          console.error("Server responded with status:", err.response.status);
          console.error("Response data:", err.response.data);
        } else if (err.request) {
          console.error("No response received:", err.request);
        } else {
          console.error("Error setting up request:", err.message);
        }
      } finally {
        this.isLoading = false;
      }
    },

    async fetchSubjects() {
      try {
        this.isLoading = true;
        const response = await Subjects.getSubjects();

        if (response && response.success) {
          this.subjects = response.subjects;
        } else {
          console.error(
            "Error fetching subjects:",
            response ? response.error : "No response"
          );
          this.errorMessage =
            "Failed to load subjects" +
            (response && response.error ? `: ${response.error}` : "");
          if (response && response.debug) {
            console.log("Debug info:", response.debug);
          }
        }
      } catch (err) {
        console.error("Fetch error in fetchSubjects:", err);
        this.errorMessage = "Network error while loading subjects";
      } finally {
        this.isLoading = false;
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
        console.error("Error parsing user data from localStorage:", e);
        this.userName = "User";
        this.userRole = "";
        localStorage.removeItem("user");
      }
    },
  },

  mounted() {
    this.checkUserRole();
    this.fetchDashboardData();
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
      <p class="mt-2">Loading data...</p>
    </div>

    <div v-else class="container-fluid">
      <div class="row mb-4">
        <div class="col-12">
          <div class="welcome-content rounded-4">
            <div class="date-time">
              {{ formattedDate }}
            </div>
            <div class="title">
              <h1>Welcome back, {{ userName }}!</h1>
              <p>Always stay updated in your student portal</p>
            </div>
            <img
              src="../../public/img/Student.png"
              class="img-fluid"
              alt="Student illustration"
            />
          </div>
        </div>
      </div>

      <div class="row mt-5 align-items-start">
        <div class="col-md-9">
          <div class="row align-items-end mb-3">
            <div class="col">
              <h2>
                {{ userRole === "admin" ? "Admin Dashboard" : "Dashboard" }}
              </h2>
            </div>
          </div>
          <div class="row dash-row">
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="card dash-card text-center justify-content-center">
                <i class="fa-solid fa-users mb-2"></i>
                <h4 class="fs-2">{{ totalUsers }}</h4>
                <h5 class="fs-5">Registered Users</h5>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="card dash-card text-center justify-content-center">
                <i class="fa-solid fa-envelopes-bulk mb-2"></i>
                <h4 class="fs-2">{{ subjectsCount }}</h4>
                <h5 class="fs-5">Created Subjects</h5>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="card dash-card text-center justify-content-center">
                <i class="fa-solid fa-book mb-2"></i>
                <h4 class="fs-2">0</h4>
                <h5 class="fs-5">Active Courses</h5>
              </div>
            </div>
          </div>
          <div class="row mt-4">
            <div class="col-md-6 mb-4">
              <div class="card p-3 h-100">
                <h5>Predmet 1</h5>
                <p class="small text-muted">
                  Опис или додатне информације о предмету 1.
                </p>
              </div>
            </div>
            <div class="col-md-6 mb-4">
              <div class="card p-3 h-100">
                <h5>Predmet 2</h5>
                <p class="small text-muted">
                  Опис или додатне информације о предмету 2.
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="row align-items-end mb-3">
            <div class="col">
              <h2 class="text-md-start">Best Students</h2>
            </div>
          </div>
          <div class="students">
            <div class="row mb-3">
              <div class="col-4 student">
                <img
                  src="../../public/img/Aliah Lane.jpg"
                  class="img-fluid rounded-circle"
                  alt="Aliah Lane"
                />
              </div>
              <div class="col-4 student">
                <img
                  src="../../public/img/Nic Fassbender.jpg"
                  class="img-fluid rounded-circle"
                  alt="Nic Fassbender"
                />
              </div>
              <div class="col-4 student">
                <img
                  src="../../public/img/Lola Sanders.jpg"
                  class="img-fluid rounded-circle"
                  alt="Lola Sanders"
                />
              </div>
            </div>
            <div class="row">
              <h5 class="mt-3">Last Script</h5>
              <div class="card border-0 shadow rounded-3 p-3">
                <h6>Script Title Example</h6>
                <p class="small">
                  This is a brief description of the last script or recent
                  activity shown here.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.main-content {
  width: 100%;
}

.welcome-content {
  position: relative;
  min-height: 270px;
  height: auto;
  background: linear-gradient(to right, rgba(0, 74, 173, 1) 0%, #3a8fd5 100%);
  width: 100%;
  padding: 30px 40px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  overflow: hidden;
  color: white;
}
.welcome-content img {
  position: absolute;
  bottom: 0;
  right: 20px;
  max-height: 110%;
  width: auto;
  pointer-events: none;
}
.title,
.date-time {
  position: relative;
  z-index: 1;
}
.date-time {
  font-size: 0.9rem;
  opacity: 0.8;
  margin-bottom: 5%;
}
.title h1 {
  font-size: clamp(1.8rem, 3vw, 2.5rem);
  margin-bottom: 5px;
}
.title p {
  font-size: clamp(1rem, 1.5vw, 1.1rem);
  opacity: 0.9;
}

.dash-card {
  box-shadow: 0 0.4rem 1rem rgba(0, 0, 0, 0.08);
  border-radius: 15px;
  border: 0;
  aspect-ratio: 1/1;
  transition: all 0.3s ease;
  background-color: #fff;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 20px;
  height: 100%;
}

.dash-card i {
  font-size: clamp(40px, 8vw, 60px);
  color: rgba(0, 74, 173, 1);
  margin-bottom: 15px;
}

.dash-card h4 {
  font-size: clamp(1.8rem, 5vw, 2.2rem);
  font-weight: 600;
}

.dash-card h5 {
  font-size: clamp(0.9rem, 2vw, 1rem);
  opacity: 0.7;
  font-weight: 400;
}

.dash-card:hover {
  transform: translateY(-5px) scale(1.02);
  box-shadow: 0 0.75rem 1.5rem rgba(0, 74, 173, 0.15);
}

.students img {
  border: 3px solid rgba(0, 74, 173, 1);
}

.student {
  padding: 3px;
}

.card {
  box-shadow: 0 0.4rem 1rem rgba(0, 0, 0, 0.08);
  border-radius: 15px;
  border: 0;
}

@media (max-width: 992px) {
  .dash-row .col-lg-4 {
    flex: 0 0 50%;
    max-width: 50%;
  }
}

@media (max-width: 767.98px) {
  .main-content {
    width: 95%;
  }
  .welcome-content {
    padding: 20px;
    text-align: center;
  }
  .welcome-content img {
    position: static;
    display: block;
    max-height: 100px;
    opacity: 0.6;
    margin: 15px auto 0;
  }
  .title h1 {
    font-size: 1.6rem;
  }
  .title p {
    font-size: 1rem;
  }
  .dash-card i {
    font-size: 45px;
  }
  .dash-card h4 {
    font-size: 1.8rem;
  }
  .dash-card h5 {
    font-size: 0.9rem;
  }
  .row.align-items-start > [class*="col-"] {
    width: 100%;
    flex: 0 0 100%;
    max-width: 100%;
    margin-bottom: 2rem;
  }
  .students .row.mb-3 {
    justify-content: center;
  }
  .students .col-4.student {
    flex: 0 0 30%;
    max-width: 30%;
  }
}

@media (max-width: 576px) {
  .dash-row > [class*="col-"] {
    flex: 0 0 100%;
    max-width: 100%;
  }
  .col-md-9 > .row.mt-4 > .col-md-6 {
    flex: 0 0 100%;
    max-width: 100%;
  }
}
</style>
