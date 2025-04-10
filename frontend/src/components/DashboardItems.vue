<script>
import Subjects from "../services/api/Subjects";
import createNote from "../services/api/CreateNote";

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

      scriptText: "",

      errorMessage: "",
      successMessage: "",
      isLoading: false,
    };
  },
  methods: {
    async fetchSubjects() {
      try {
        this.isLoading = true;
        const response = await Subjects.getSubjects();
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

    async submitScript() {
      if (!this.scriptText || !this.selectedCategory) {
        this.errorMessage = "Please fill in all required fields";
        return;
      }

      try {
        this.isLoading = true;
        this.errorMessage = "";
        this.successMessage = "";

        const response = await createNote.createNote();
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
  },

  mounted() {
    this.fetchSubjects();
  },
};
</script>
<template>
  <div id="main-content" class="content bg-body-tertiary">
    <nav class="navbar navbar-expand-lg bg-white shadow-sm p-3 rounded">
      <div class="container-fluid">
        <a></a>
        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarNav"
          aria-controls="navbarNav"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <router-link class="nav-link" to="#" style="display: flex">
                <i class="fa-solid fa-scroll fs-4"></i>
                <span class="nav-text ms-2">My Scripts</span>
              </router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" to="#" style="display: flex">
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

    <div class="container-fluid">
      <h1 class="ps-3 pt-4">Dashboard</h1>
      <div class="row dash-row">
        <div class="col-md-4">
          <div class="card dash-card border-0 shadow">
            <div class="row">
              <div class="col-10">
                <h2 class="fs-5">Total created scripts</h2>
              </div>
              <div class="col-2">
                <i class="fa-solid fa-book-atlas"></i>
              </div>
            </div>
            <h3 id="adjustedCount" class="fs-3">{{ totalScripts }}</h3>
          </div>
        </div>
        <div class="col-md-4">
          <router-link to="/myscripts">
            <div class="card dash-card border-0 shadow">
              <div class="row">
                <div class="col-10">
                  <h2 class="fs-5">You created</h2>
                </div>
                <div class="col-2">
                  <i class="fa-solid fa-book"></i>
                </div>
              </div>
              <h4 id="scriptCount" class="fs-3">{{ myScriptsCount }}</h4>
            </div>
          </router-link>
        </div>
        <div class="col-md-4">
          <router-link to="/bookmarks">
            <div class="card dash-card border-0 shadow">
              <div class="row">
                <div class="col-10">
                  <h2 class="fs-5">Your bookmarks</h2>
                </div>
                <div class="col-2">
                  <i class="fa-solid fa-bookmark"></i>
                </div>
              </div>
              <h5 class="fs-3">{{ bookmarksCount }}</h5>
            </div>
          </router-link>
        </div>
      </div>
      <div class="row big-card-row">
        <div class="col-md-12">
          <div class="card border-0 shadow mb-3">
            <div class="card-body">
              <form @submit.prevent="handleSubmit">
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
                  ></textarea>
                </div>
                <div class="checks mb-3">
                  <div v-for="subject in subjects" :key="subject.id">
                    <input
                      class="form-radio-input me-1"
                      type="radio"
                      :id="'radio' + subject.id"
                      :value="subject.id"
                      v-model="selectedCategory"
                      name="platform"
                    />
                    <label
                      class="form-radio-label me-2"
                      :for="'radio' + subject.id"
                    >
                      {{ subject.name }}
                    </label>
                  </div>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
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
</style>
