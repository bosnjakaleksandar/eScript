<script>
import Subjects from "../services/api/Subjects";

export default {
  components: {},
  data() {
    return {
      subjects: [],
      isLoading: false,
      errorMessage: "",
      subjectIcons: {
        1: "fa-solid fa-layer-group",
        2: "fa-solid fa-business-time",
        3: "fa-solid fa-network-wired",
        4: "fa-solid fa-language",
        5: "fa-solid fa-shield-halved",
        6: "fa-solid fa-databasefa-solid fa-database",
        7: "fa-solid fa-rectangle-ad",
        8: "fa-solid fa-chart-column",
        9: "fa-solid fa-square-root-variable",
        10: "fa-solid fa-chess-knight",
        default: "fa-solid fa-book",
      },
    };
  },
  methods: {
    async fetchSubjects() {
      this.isLoading = true;
      this.errorMessage = "";
      try {
        const response = await Subjects.getSubjects();
        if (response && response.success) {
          this.subjects = response.subjects || [];
        } else {
          this.errorMessage = "Failed to load subjects.";
          console.error(
            "Error fetching subjects:",
            response ? response.error : "Unknown error"
          );
        }
      } catch (error) {
        this.errorMessage = "Network error while loading subjects.";
        console.error("Network error fetching subjects:", error);
      } finally {
        this.isLoading = false;
      }
    },
    getIconForSubject(subjectId) {
      return this.subjectIcons[subjectId] || this.subjectIcons.default;
    },
    navigateToSubjectNotes(subjectId) {
      if (this.$router && this.$router.hasRoute("SubjectNotes")) {
        this.$router.push({
          name: "SubjectNotes",
          params: { subjectId: subjectId },
        });
      } else {
        console.error(
          `Route 'SubjectNotes' not found or router not available! Cannot navigate for subject ID: ${subjectId}`
        );
        this.errorMessage = "Navigation error: Link configuration is missing.";
      }
    },
  },
  mounted() {
    this.fetchSubjects();
  },
};
</script>

<template>
  <div class="subjects-list-container">
    <h2 class="border-bottom mb-3 pb-2">Subjects</h2>

    <div v-if="isLoading" class="loading-message text-center py-5">
      <div
        class="spinner-border text-primary"
        role="status"
        style="width: 2rem; height: 2rem"
      >
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2 text-muted">Loading subjects...</p>
    </div>

    <div
      v-if="errorMessage"
      class="alert alert-danger mx-auto mt-4"
      role="alert"
      style="max-width: 90%"
    >
      {{ errorMessage }}
    </div>

    <div
      v-if="!isLoading && !errorMessage && subjects.length > 0"
      class="subjects-grid"
    >
      <div
        v-for="subject in subjects"
        :key="subject.id"
        class="subject-card"
        @click="navigateToSubjectNotes(subject.id)"
        role="button"
        tabindex="0"
        @keydown.enter="navigateToSubjectNotes(subject.id)"
        @keydown.space.prevent="navigateToSubjectNotes(subject.id)"
      >
        <div class="subject-icon-wrapper">
          <i
            :class="['subject-icon', getIconForSubject(subject.id)]"
            aria-hidden="true"
          ></i>
        </div>
        <div class="subject-info">
          <h5 class="subject-name">{{ subject.name }}</h5>
          <p class="subject-year" v-if="subject.year">
            Year: {{ subject.year }}
          </p>
        </div>
      </div>
    </div>

    <div
      v-if="!isLoading && !errorMessage && subjects.length === 0"
      class="no-subjects-message text-center py-5 text-muted"
    >
      <i class="fas fa-box-open fa-2x mb-3"></i>
      <p>No subjects found at the moment.</p>
    </div>
  </div>
</template>

<style scoped>
.subjects-list-container {
  max-width: 100%;
  margin: 0 auto;
  padding: 15px;
}

h2 {
  color: #343a40;
  font-weight: 600;
  padding-top: 15px;
}

.loading-message,
.no-subjects-message {
  padding: 40px 15px;
}

.subjects-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 25px;
}

.subject-card {
  background-color: #fff;
  border-radius: 10px;
  padding: 25px;
  text-align: center;
  cursor: pointer;
  transition: transform 0.25s ease-in-out, box-shadow 0.25s ease-in-out;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 190px;
  box-shadow: 0 0.4rem 1rem rgba(0, 0, 0, 0.08);
}

.subject-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 0.75rem 1.5rem rgba(0, 74, 173, 0.15);
}

.subject-icon-wrapper {
  margin-bottom: 20px;
}

.subject-icon {
  font-size: 3rem;
  color: #0056b3;
}

.subject-name {
  font-size: 1.15rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 8px;
}

.subject-year {
  font-size: 0.85rem;
  color: #777;
  margin-bottom: 0;
}

.alert {
  margin-top: 25px;
  margin-bottom: 25px;
}
</style>
