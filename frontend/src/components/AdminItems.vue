<script>
export default {
  data() {
    return {
      userRole: "",
      totalUsers: 0,
      subjectsCount: 0,
      subjects: [],
      selectedCategory: null,

      newSubjectName: "",
      selectedYear: null,

      scriptText: "",

      errorMessage: "",
      successMessage: "",
      isLoading: false,
    };
  },
  methods: {
    async fetchDashboardData() {
      try {
        this.isLoading = true;
        const response = await fetch(
          "http://localhost:8002/get-dashboard-data.php",
          {
            credentials: "include",
          }
        );
        const data = await response.json();
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
        const response = await fetch("http://localhost:8002/get-subjects.php", {
          credentials: "include",
        });
        const data = await response.json();

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

        const response = await fetch(
          "http://localhost:8002/create-subject.php",
          {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            credentials: "include",
            body: JSON.stringify({
              name: this.newSubjectName,
              year: this.selectedYear,
            }),
          }
        );

        const data = await response.json();

        if (data.success) {
          this.subjects.push(data.subject);
          this.subjectsCount++;

          this.newSubjectName = "";
          this.selectedYear = null;

          this.successMessage = "Subject added successfully!";
        } else {
          this.errorMessage = data.error || "Failed to add subject";

          if (data.debug) {
            console.log("Debug info:", data.debug);
          }
        }
      } catch (err) {
        console.error("Submit error:", err);
        this.errorMessage = "Network error while adding subject";
      } finally {
        this.isLoading = false;
      }
    },

    async submitScript() {
      if (!this.scriptText || !this.selectedCategory) {
        this.errorMessage = "Please fill in all required fields";
        return;
      }

      try {
        this.isLoading = true;
        this.errorMessage = "";
        this.successMessage = "";

        const response = await fetch("http://localhost:8002/create-note.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          credentials: "include",
          body: JSON.stringify({
            content: this.scriptText,
            subject_id: this.selectedCategory,
          }),
        });

        const data = await response.json();

        if (data.success) {
          this.scriptText = "";
          this.selectedCategory = null;

          this.successMessage = "Script submitted successfully!";
        } else {
          this.errorMessage = data.error || "Failed to submit script";
        }
      } catch (err) {
        console.error("Submit error:", err);
        this.errorMessage = "Network error while submitting script";
      } finally {
        this.isLoading = false;
      }
    },

    checkUserRole() {
      const userData = localStorage.getItem("user");
      if (userData) {
        const user = JSON.parse(userData);
        this.userRole = user.role || "";
      }
    },
  },

  mounted() {
    this.checkUserRole();
    this.fetchDashboardData();
    this.fetchSubjects();
  },
};
</script>

<template>
  <div id="main-content" class="content bg-body-tertiary">
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm p-3 rounded">
      <div class="container-fluid">
        <a class="navbar-brand"></a>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <router-link class="nav-link d-flex" to="/myscripts">
                <i class="fa-solid fa-scroll fs-4"></i>
                <span class="nav-text ms-2">My Scripts</span>
              </router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link d-flex" to="/bookmarks">
                <i class="fa-solid fa-bookmark fs-4"></i>
                <span class="nav-text ms-2">Bookmarks</span>
              </router-link>
            </li>
            <li class="nav-item">
              <router-link class="btn btn-danger" to="/">Log Out</router-link>
            </li>
          </ul>
        </div>
      </div>
    </nav>

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

    <!-- DASHBOARD STATS -->
    <div class="container-fluid">
      <h1 class="ps-3 pt-4">
        {{ userRole === "admin" ? "Admin Dashboard" : "Dashboard" }}
      </h1>
      <div class="row dash-row">
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
      </div>

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

      <!-- SCRIPT SUBMIT FORM -->
      <div class="row big-card-row">
        <div class="col-md-12">
          <div class="card border-0 shadow mb-3">
            <div class="card-body">
              <h5 class="mb-3">Add New Script</h5>
              <form @submit.prevent="submitScript">
                <div class="mb-3">
                  <label for="scriptInput" class="form-label">
                    A place to exchange knowledge through scripts
                  </label>
                  <textarea
                    class="form-control"
                    id="scriptInput"
                    v-model="scriptText"
                    rows="10"
                    placeholder="Write your script here..."
                    required
                  ></textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label">Select Subject:</label>
                  <div class="checks mb-3">
                    <div v-if="subjects.length === 0" class="text-muted">
                      No subjects available. Please add subjects first.
                    </div>
                    <div
                      v-for="subject in subjects"
                      :key="subject.id"
                      class="form-check"
                    >
                      <input
                        class="form-check-input"
                        type="radio"
                        :id="'radio' + subject.id"
                        :value="subject.id"
                        v-model="selectedCategory"
                        name="subject"
                        required
                      />
                      <label
                        class="form-check-label"
                        :for="'radio' + subject.id"
                      >
                        {{ subject.name }} (Year {{ subject.year }})
                      </label>
                    </div>
                  </div>
                </div>
                <button
                  type="submit"
                  class="btn btn-primary"
                  :disabled="isLoading || subjects.length === 0"
                >
                  {{ isLoading ? "Submitting..." : "Submit Script" }}
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
#main-content {
  flex: 1;
  transition: all 0.3s ease;
  border-top-left-radius: 10px;
  border-bottom-left-radius: 10px;
  overflow-y: auto;
  padding-bottom: 2rem;
}
#main-content a {
  text-decoration: none;
}
.dash-row {
  margin-left: 20px !important;
  margin-right: 20px !important;
}
.dash-card {
  padding: 20px;
  margin-top: 5%;
  margin-bottom: 5%;
}
.dash-card i {
  font-size: 50px;
  color: #ad004a;
}
.dash-card h2 {
  opacity: 70%;
}
.big-card-row {
  margin-left: 20px !important;
  margin-right: 20px !important;
}
.form-check {
  margin-bottom: 0.5rem;
}
</style>
