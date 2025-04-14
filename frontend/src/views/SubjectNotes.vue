<script>
import NotesBySubjectService from "../services/api/NotesBySubject";
import SubjectsService from "../services/api/Subjects";
import Sidebar from "../components/Sidebar.vue";
import SubjectNoteList from "../components/SubjectNoteList.vue";

export default {
  name: "SubjectNotesView",
  components: {
    Sidebar,
    SubjectNoteList,
  },
  data() {
    return {
      subjectId: null,
      subject: null,
      notes: [],
      isLoadingSubject: false,
      isLoadingNotes: false,
      subjectError: "",
      notesError: "",
    };
  },
  methods: {
    async fetchSubjectDetails(id) {
      if (!id) return;
      this.isLoadingSubject = true;
      this.subjectError = "";
      try {
        const response = await SubjectsService.getSubjects();
        if (response && response.success && Array.isArray(response.subjects)) {
          const foundSubject = response.subjects.find((subj) => subj.id == id);
          if (foundSubject) {
            this.subject = foundSubject;
          } else {
            this.subjectError = `Subject with ID ${id} not found.`;
            this.subject = null;
          }
        } else {
          this.subjectError =
            response?.error || "Failed to load subjects list.";
        }
      } catch (err) {
        this.subjectError = `Network error loading subjects list: ${err.message}`;
        console.error(err);
      } finally {
        this.isLoadingSubject = false;
      }
    },
    async fetchNotesForSubject(id) {
      if (!id) return;
      this.isLoadingNotes = true;
      this.notesError = "";
      try {
        const response = await NotesBySubjectService.getNotesBySubject(id);
        if (response && response.success) {
          this.notes = response.notes || [];
        } else {
          this.notesError =
            response?.error || "Failed to load notes for this subject.";
        }
      } catch (err) {
        this.notesError = `Network error loading notes: ${err.message}`;
        console.error(err);
      } finally {
        this.isLoadingNotes = false;
      }
    },
    handleNoteRated() {
      console.log("Refreshing notes after rating...");
      if (this.subjectId) {
        this.fetchNotesForSubject(this.subjectId);
      }
    },
  },
  created() {
    const idFromRoute = this.$route.params.subjectId;
    if (idFromRoute) {
      this.subjectId = parseInt(idFromRoute, 10);
      this.fetchSubjectDetails(this.subjectId);
      this.fetchNotesForSubject(this.subjectId);
    } else {
      this.notesError = "Subject ID not found in route parameter.";
      console.error("Subject ID missing in route parameters!");
    }
  },
  watch: {
    "$route.params.subjectId"(newId, oldId) {
      if (newId && newId !== oldId) {
        this.subjectId = parseInt(newId, 10);
        this.fetchSubjectDetails(this.subjectId);
        this.fetchNotesForSubject(this.subjectId);
      }
    },
  },
};
</script>
<template>
  <div class="app-container">
    <Sidebar />
    <div class="main-content-wrapper">
      <div class="subject-notes-content-container p-2">
        <div v-if="isLoadingSubject && !subject" class="text-center py-4">
          Loading subject info...
        </div>
        <div v-else-if="subjectError" class="alert alert-danger">
          {{ subjectError }}
        </div>
        <h2 v-else-if="subject" class="border-bottom mb-4 pb-2">
          Notes for: {{ subject.name }}
          <span v-if="subject.year" class="text-muted h5"
            >(Year {{ subject.year }})</span
          >
        </h2>
        <h2 v-else class="border-bottom mb-4 pb-2">Notes for Subject</h2>

        <SubjectNoteList
          :notes="notes"
          :is-loading="isLoadingNotes"
          :error="notesError"
          @note-rated="handleNoteRated"
        />
      </div>
    </div>
  </div>
</template>
<style scoped>
.main-content-wrapper {
  margin-left: 19%;
  width: calc(100% - 19%);
  padding-top: 20px;
  padding-bottom: 20px;
  padding-right: 5%;
  min-height: 100vh;
}
.subject-notes-content-container h2 .text-muted {
  font-weight: 400;
}
h2 {
  color: #343a40;
  font-weight: 600;
  padding-top: 30px;
}
@media (max-width: 768px) {
  .main-content-wrapper {
    margin-left: calc(70px + 1%);
    width: calc(100% - (70px + 1%));
    padding-left: 2%;
    padding-right: 2%;
    padding-bottom: 70px;
  }
}

@media (max-width: 576px) {
  .main-content-wrapper {
    margin-left: 0;
    width: 100%;
    padding-left: 1%;
    padding-right: 1%;
  }
}
</style>
