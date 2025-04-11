<script>
import DashboardData from "../services/api/DashboardData";
import Subjects from "../services/api/Subjects";
import addSubject from "../services/api/AddSubject";

export default {
  data() {
    return {
      userRole: "",
      userName: "",
      totalUsers: 0,
      subjectsCount: 0,
      subjects: [],
      selectedCategory: null,

      newSubjectName: "",
      selectedYear: null,

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
        const data = response;

        if (data.success) {
          this.totalUsers = data.totalUsers;
          this.subjectsCount = data.totalSubjects;
        } else {
          console.error("Error fetching metrics:", data.error);
          this.errorMessage = "Failed to load dashboard data";
        }
      } catch (err) {
        console.error("Fetch error:", err);
        this.errorMessage = "Network error while loading dashboard data";
      } finally {
        this.isLoading = false;
      }
    },

    async fetchSubjects() {
      try {
        this.isLoading = true;
        const response = await Subjects.getSubjects();
        const data = response;

        if (data.success) {
          this.subjects = data.subjects;
        } else {
          console.error("Error fetching subjects:", data.error);
          this.errorMessage = "Failed to load subjects";

          if (data.debug) {
            console.log("Debug info:", data.debug);
          }
        }
      } catch (err) {
        console.error("Fetch error:", err);
        this.errorMessage = "Network error while loading subjects";
      } finally {
        this.isLoading = false;
      }
    },

    async addSubject() {
      if (!this.newSubjectName || !this.selectedYear) {
        this.errorMessage = "Please fill in all required fields";
        return;
      }

      try {
        this.isLoading = true;
        this.errorMessage = "";
        this.successMessage = "";

        const response = await addSubject.addSubject({
          name: this.newSubjectName,
          year: this.selectedYear,
        });

        const data = response;

        if (data && data.success) {
          if (data.subject) {
            this.subjects.push(data.subject);
          } else {
            console.warn("Subject data missing in the successful response.");
          }

          this.subjectsCount++;

          this.newSubjectName = "";
          this.selectedYear = null;
          this.successMessage = "Subject added successfully!";
        } else {
          this.errorMessage =
            data && data.error
              ? data.error
              : "Failed to add subject. Unknown error format or success=false.";
          if (data && data.debug) {
            console.log("Debug info:", data.debug);
          }
        }
      } catch (err) {
        console.error("Submit error in addSubject:", err);
        this.errorMessage = `Network or processing error while adding subject: ${err.message}`;
      } finally {
        this.isLoading = false;
      }
    },

    checkUserRole() {
      const userData = localStorage.getItem("user");
      if (userData) {
        const user = JSON.parse(userData);
        this.userRole = user.role || "";
        this.userName = user.username || user.name || "";
      }
    },
  },

  mounted() {
    this.checkUserRole();
    this.fetchDashboardData();
    this.fetchSubjects();
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
      return this.now.toLocaleString("en-US", options);
    },
  },
};
</script>

<template>
  <div class="main-content content">
    <!-- Alert Messages -->
    <div class="container-fluid mt-3" v-if="errorMessage">
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ errorMessage }}
        <button
          type="button"
          class="btn-close"
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
          @click="successMessage = ''"
        ></button>
      </div>
    </div>

    <!-- Loading indicator -->
    <div class="container-fluid text-center py-3" v-if="isLoading">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <div class="container-fluid">
      <!--  WELCOME CONTENT -->
      <div class="row">
        <div class="welcome-content rounded-4">
          <div class="date-time">
            {{ formattedDate }}
          </div>
          <div class="title">
            <h2>Welcome back, {{ userName }}!</h2>
            <p>Always stay updated in your student portal</p>
          </div>
          <img src="../../public/img/Student.png" class="img-fluid" />
        </div>
      </div>
      <!-- <h1 class="ps-3 pt-4">
        {{ userRole === "admin" ? "Admin Dashboard" : "Dashboard" }}
      </h1> -->

      <!-- DASHBOARD STATS -->
      <div class="row ms-3 mt-4">
        <h3>Admin dashboard</h3>
      </div>
      <!-- <div class="row dash-row">
        <div class="col-md-4">
          <div class="card dash-card border-0 shadow">
            <div class="row">
              <div class="col-10">
                <h2 class="fs-5">Registered Users</h2>
              </div>
              <div class="col-2">
                <i class="fa-solid fa-users"></i>
              </div>
            </div>
            <h3 class="fs-3">{{ totalUsers }}</h3>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card dash-card border-0 shadow">
            <div class="row">
              <div class="col-10">
                <h2 class="fs-5">Created Subjects</h2>
              </div>
              <div class="col-2">
                <i class="fa-solid fa-envelopes-bulk"></i>
              </div>
            </div>
            <h4 class="fs-3">{{ subjectsCount }}</h4>
          </div>
        </div>
      </div> -->

      <!-- ADMIN SUBJECT CREATION -->
      <div class="row big-card-row" v-if="userRole === 'admin'">
        <div class="col-md-12">
          <div class="card border-0 shadow mb-3">
            <div class="card-body">
              <h5 class="mb-3">Add New Subject</h5>
              <form @submit.prevent="addSubject">
                <div class="mb-3">
                  <input
                    v-model="newSubjectName"
                    type="text"
                    class="form-control"
                    placeholder="Subject name"
                    required
                  />
                </div>
                <div class="mb-3">
                  <label class="form-label">Select Year:</label>
                  <div class="d-flex gap-3">
                    <div
                      v-for="year in [1, 2, 3, 4]"
                      :key="year"
                      class="form-check"
                    >
                      <input
                        type="radio"
                        class="form-check-input"
                        :id="'year' + year"
                        :value="year"
                        v-model="selectedYear"
                        required
                      />
                      <label class="form-check-label" :for="'year' + year">
                        Year {{ year }}
                      </label>
                    </div>
                  </div>
                </div>
                <button
                  class="btn btn-success"
                  type="submit"
                  :disabled="isLoading"
                >
                  {{ isLoading ? "Adding..." : "Add Subject" }}
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.main-content {
  width: 80%;
  margin-top: 2%;
}

.welcome-content {
  position: relative;
  height: 35vh;
  background: linear-gradient(to right, rgba(0, 74, 173, 1) 0%, #3a8fd5 100%);
  width: 100%;
  margin-left: 1%;
  padding: 40px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.welcome-content img {
  position: absolute;
  bottom: 0;
  right: 0;
  width: auto;
  height: auto;
}
.title,
.date-time {
  color: white;
  margin-left: 30px;
}
</style>
