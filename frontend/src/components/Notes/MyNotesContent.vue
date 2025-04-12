<script>
import getUserNotesService from "../../services/api/GetUserNotes";
import deleteNoteService from "../../services/api/DeleteNote";
import NoteList from "./NotesList.vue";
import AddNoteModal from "./AddNoteModal.vue";

export default {
  name: "MyNotesView",
  components: {
    NoteList,
    AddNoteModal,
  },
  data() {
    return {
      userNotes: [],
      isLoadingNotes: false,
      notesError: "",
      showAddNoteModal: false,
      isLoadingDelete: false,
      successMessage: "",
    };
  },
  methods: {
    async fetchUserNotes() {
      this.isLoadingNotes = true;
      this.notesError = "";
      try {
        const response = await getUserNotesService.getUserNotes();
        if (response && response.success) {
          this.userNotes = response.notes || [];
        } else {
          this.notesError = response?.error || "Failed to load your notes.";
        }
      } catch (error) {
        this.notesError = "Network error while loading your notes.";
        console.error("Fetch user notes network/processing error:", error);
      } finally {
        this.isLoadingNotes = false;
      }
    },
    handleNoteAdded() {
      this.fetchUserNotes();
      if (this.successMessage) this.successMessage = "";
    },
    async handleDeleteNote(noteId) {
      this.isLoadingDelete = true;
      this.notesError = "";
      this.successMessage = "";
      try {
        const response = await deleteNoteService.deleteNote(noteId);
        if (response && response.success) {
          this.successMessage =
            response.message || "Note deleted successfully!";
          await this.fetchUserNotes();
        } else {
          this.notesError = response?.error || "Failed to delete note.";
          console.error("Delete note error:", response);
        }
      } catch (err) {
        this.notesError = `Error deleting note: ${
          err.message || "Network/server error"
        }`;
        console.error("Delete note network/processing error:", err);
      } finally {
        this.isLoadingDelete = false;
        if (this.successMessage) {
          setTimeout(() => {
            this.successMessage = "";
          }, 3000);
        }
      }
    },
  },
  mounted() {
    this.fetchUserNotes();
  },
};
</script>
<template>
  <div class="my-notes-view-container p-3">
    <div
      class="notes-header d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom"
    >
      <h5>Your Notes</h5>
      <button
        @click="showAddNoteModal = true"
        class="btn btn-sm btn-primary add-note-button"
      >
        <i class="fa-solid fa-plus me-1"></i> Add Note
      </button>
    </div>

    <div
      v-if="notesError"
      class="alert alert-danger mx-auto mt-3"
      role="alert"
      style="max-width: 95%"
    >
      {{ notesError }}
    </div>
    <div
      v-if="successMessage"
      class="alert alert-success mx-auto mt-3"
      role="alert"
      style="max-width: 95%"
    >
      {{ successMessage }}
    </div>

    <NoteList
      :notes="userNotes"
      :is-loading="isLoadingNotes"
      :error="notesError"
      @delete-note="handleDeleteNote"
    />

    <AddNoteModal
      :show="showAddNoteModal"
      @update:show="showAddNoteModal = $event"
      @close="showAddNoteModal = false"
      @note-added="handleNoteAdded"
    />
  </div>
</template>

<style scoped>
.my-notes-view-container {
  width: 100%;
  margin: 0 auto;
}
.notes-header h5 {
  margin-bottom: 0;
  font-weight: 600;
  color: #333;
}
.add-note-button {
  font-size: 0.9rem;
}
</style>
