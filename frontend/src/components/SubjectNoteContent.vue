<script>
import NotesBySubjectService from "../services/api/NotesBySubject.js";
import SubjectsService from "../services/api/Subjects";

import SubjectNoteList from "./SubjectNoteList.vue";

export default {
  name: "SubjectNotesView",
  components: {
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
            response?.error || "Failed to load subjects list to find details.";
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
};
</script>
<template>
  <div class="subject-notes-view-container p-3">
    <div v-if="isLoadingSubject" class="text-center py-4">
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
    <h2 v-else class="mb-4">Notes for Subject</h2>

    <SubjectNoteList
      :notes="notes"
      :is-loading="isLoadingNotes"
      :error="notesError"
    />
  </div>
</template>
<style scoped>
.subject-notes-view-container {
  max-width: 100%;
  margin: 0 autoč
}
h2 .text-muted {
  font-weight: 400;
}
</style>
