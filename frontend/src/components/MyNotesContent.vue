<script>
import Subjects from "../services/api/Subjects";
import createNote from "../services/api/CreateNote";
import getUserNotes from "../services/api/GetUserNotes";
export default {
  components: {},
  data() {
    return {
      subjects: [],
      selectedCategory: null,
      noteTitle: "",
      noteText: "",

      errorMessage: "",
      successMessage: "",
      isLoading: false,
      isLoadingNotes: false,

      userNotes: [],

      showAddNoteModal: false,

      expandedNoteId: null,
      notePreviewLength: 100,
    };
  },
  methods: {
    async fetchSubjects() {
      if (this.isLoading || this.subjects.length > 0) return;
      this.isLoading = true;
      this.errorMessage = "";
      try {
        const response = await Subjects.getSubjects();
        if (response && response.success) {
          this.subjects = response.subjects || [];
        } else {
          this.errorMessage = `Failed to load subjects for modal${
            response?.error ? ": " + response.error : "."
          }`;
        }
      } catch (err) {
        this.errorMessage = "Network error while loading subjects for modal.";
      } finally {
        this.isLoading = false;
      }
    },

    async fetchUserNotes() {
      this.isLoadingNotes = true;
      this.errorMessage = "";
      try {
        const response = await getUserNotes.getUserNotes();
        if (response && response.success) {
          this.userNotes = response.notes || [];
        } else {
          this.errorMessage = `Failed to load your notes${
            response?.error ? ": " + response.error : "."
          }`;
        }
      } catch (error) {
        this.errorMessage = "Network error while loading your notes.";
      } finally {
        this.isLoadingNotes = false;
      }
    },

    async submitNote() {
      if (!this.noteText || !this.selectedCategory || !this.noteTitle) {
        this.errorMessage =
          "Please fill in all required fields (title, note text and subject category).";
        this.successMessage = "";
        return;
      }

      this.isLoading = true;
      this.errorMessage = "";
      this.successMessage = "";

      try {
        const response = await createNote.createNote({
          title: this.noteTitle,
          content: this.noteText,
          subject_id: this.selectedCategory,
        });

        if (response && response.success) {
          this.successMessage = "Note submitted successfully!";
          this.expandedNoteId = null;
          await this.fetchUserNotes();
          ха;
          setTimeout(() => {
            this.noteTitle = "";
            this.noteText = "";
            this.selectedCategory = null;
            this.closeAddNoteModal();
            this.successMessage = "";
          }, 1500);
        } else {
          this.errorMessage =
            response?.error || "Failed to submit note. Please try again.";
          if (response) console.log("Error response data:", response);
        }
      } catch (err) {
        console.error("Submit note network/processing error:", err);
        this.errorMessage = `Error submitting note: ${
          err.message || "Network or server error"
        }. Please try again.`;
      } finally {
        this.isLoading = false;
      }
    },

    openAddNoteModal() {
      this.errorMessage = "";
      this.successMessage = "";
      this.noteTitle = "";
      this.noteText = "";
      this.selectedCategory = null;
      if (this.subjects.length === 0) {
        this.fetchSubjects();
      }
      this.showAddNoteModal = true;
    },

    closeAddNoteModal() {
      this.showAddNoteModal = false;
    },

    toggleNoteExpansion(noteId) {
      this.expandedNoteId = this.expandedNoteId === noteId ? null : noteId;
    },

    shouldTruncate(content) {
      return (
        typeof content === "string" && content.length > this.notePreviewLength
      );
    },
  },
  computed: {
    processedUserNotes() {
      return this.userNotes.map((note) => {
        const isExpanded = this.expandedNoteId === note.id;
        const requiresTruncation = this.shouldTruncate(note.content);
        let displayContent = note.content;

        if (!isExpanded && requiresTruncation) {
          let preview = note.content.substring(0, this.notePreviewLength);
          if (
            note.content.length > this.notePreviewLength &&
            note.content[this.notePreviewLength] !== " "
          ) {
            const lastSpace = preview.lastIndexOf(" ");
            if (lastSpace > this.notePreviewLength / 2) {
              preview = preview.substring(0, lastSpace);
            }
          }
          displayContent = preview + "...";
        }

        return {
          ...note,
          isExpanded: isExpanded,
          requiresTruncation: requiresTruncation,
          displayContent: displayContent,
        };
      });
    },
  },
  mounted() {
    this.fetchUserNotes();
  },
};
</script>

<template>
  <div class="my-notes-view-container">
    <div class="notes-header">
      <h5>Your Notes</h5>
      <button
        @click="openAddNoteModal"
        class="btn btn-sm btn-primary add-note-button"
      >
        <i class="fa-solid fa-plus me-1"></i> Add Note
      </button>
    </div>

    <div
      v-if="errorMessage && !showAddNoteModal"
      class="alert alert-danger mx-auto mt-3"
      role="alert"
      style="max-width: 95%"
    >
      {{ errorMessage }}
    </div>

    <div v-if="isLoadingNotes" class="loading-message text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2 text-muted">Loading your notes...</p>
    </div>

    <div v-else class="user-notes">
      <div
        v-if="userNotes.length === 0 && !isLoadingNotes"
        class="text-muted no-notes-message"
      >
        <i class="fas fa-sticky-note fa-2x mb-3"></i>
        <p>
          You haven't created any notes yet.<br />Click "Add Note" to create
          one.
        </p>
      </div>
      <div v-else class="list-group">
        <div
          v-for="note in processedUserNotes"
          :key="note.id"
          class="list-group-item list-group-item-action flex-column align-items-start note-item"
          :class="{ 'is-expanded': note.isExpanded }"
          @click="toggleNoteExpansion(note.id)"
          role="button"
          tabindex="0"
          @keydown.enter="toggleNoteExpansion(note.id)"
          @keydown.space.prevent="toggleNoteExpansion(note.id)"
        >
          <div class="d-flex w-100 justify-content-between note-header-line">
            <h6 class="mb-1 note-title">{{ note.title }}</h6>
            <small class="text-muted note-date">{{
              new Date(note.created_at).toLocaleDateString()
            }}</small>
          </div>

          <transition name="fade" mode="out-in">
            <p
              class="mb-1 note-content"
              :key="note.id + (note.isExpanded ? '-expanded' : '-collapsed')"
            >
              {{ note.displayContent }}
              <span
                v-if="!note.isExpanded && note.requiresTruncation"
                class="read-more-indicator"
              >
                (click to read more)
              </span>
            </p>
          </transition>
        </div>
      </div>
    </div>
  </div>

  <transition name="modal-fade">
    <div
      class="modal-overlay"
      v-if="showAddNoteModal"
      @click.self="closeAddNoteModal"
    >
      <div
        class="modal-content"
        role="dialog"
        aria-modal="true"
        aria-labelledby="addNoteModalTitle"
      >
        <button
          class="modal-close-btn"
          @click="closeAddNoteModal"
          aria-label="Close modal"
        >
          &times;
        </button>

        <div class="add-new-note-modal">
          <h5 class="mb-3 modal-title" id="addNoteModalTitle">Add New Note</h5>
          <div
            v-if="errorMessage && showAddNoteModal"
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
          >
            {{ errorMessage }}
            <button
              type="button"
              class="btn-close"
              @click="errorMessage = ''"
              aria-label="Close error message"
            ></button>
          </div>
          <div
            v-if="successMessage && showAddNoteModal"
            class="alert alert-success"
            role="status"
          >
            {{ successMessage }}
          </div>

          <form @submit.prevent="submitNote" novalidate>
            <div class="mb-3">
              <label for="modalNoteTitleInput" class="form-label">Title</label>
              <input
                type="text"
                class="form-control"
                id="modalNoteTitleInput"
                v-model.trim="noteTitle"
                placeholder="Enter note title"
                required
                aria-required="true"
              />
            </div>
            <div class="mb-3">
              <label for="modalNoteTextInput" class="form-label"
                >Note Content</label
              >
              <textarea
                class="form-control"
                id="modalNoteTextInput"
                v-model="noteText"
                rows="8"
                placeholder="Write your note here..."
                required
                aria-required="true"
              ></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Select Subject:</label>
              <div
                v-if="isLoading && subjects.length === 0"
                class="text-muted small mb-2"
              >
                <span
                  class="spinner-border spinner-border-sm"
                  role="status"
                  aria-hidden="true"
                ></span>
                Loading subjects...
              </div>
              <div
                v-else-if="!isLoading && subjects.length === 0 && !errorMessage"
                class="alert alert-warning small p-2"
              >
                No subjects available. You might need to add subjects first.
              </div>
              <div
                v-else-if="subjects.length > 0"
                class="checks modal-checks mb-3 border rounded p-2"
              >
                <div
                  v-for="subject in subjects"
                  :key="subject.id"
                  class="form-check"
                >
                  <input
                    class="form-check-input"
                    type="radio"
                    :id="'modalNoteRadio' + subject.id"
                    :value="subject.id"
                    v-model="selectedCategory"
                    name="modalNoteSubject"
                    required
                    aria-required="true"
                  />
                  <label
                    class="form-check-label"
                    :for="'modalNoteRadio' + subject.id"
                  >
                    {{ subject.name }}
                    <span v-if="subject.year" class="text-muted small"
                      >(Year {{ subject.year }})</span
                    >
                  </label>
                </div>
              </div>
            </div>
            <div class="modal-actions text-end">
              <button
                type="button"
                class="btn btn-secondary me-2"
                @click="closeAddNoteModal"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="btn btn-primary"
                :disabled="
                  isLoading || subjects.length === 0 || !selectedCategory
                "
              >
                <span
                  v-if="isLoading"
                  class="spinner-border spinner-border-sm"
                  role="status"
                  aria-hidden="true"
                ></span>
                {{ isLoading ? " Submitting..." : "Submit Note" }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </transition>
</template>

<style scoped>
.my-notes-view-container {
  width: 100%;
  margin: 0 auto;
  padding: 20px;
}
.add-note-button {
  font-size: 0.9rem;
}
.add-new-note-modal {
  width: 100%;
}
.notes-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #e0e0e0;
}
.notes-header h5 {
  margin-bottom: 0;
  font-weight: 600;
  color: #333;
}
.loading-message,
.no-notes-message {
  padding: 30px 20px;
  text-align: center;
  color: #6c757d;
  margin-top: 2rem;
}
.no-notes-message {
  background-color: #f8f9fa;
  border: 1px dashed #ced4da;
  border-radius: 0.375rem;
}
.no-notes-message i {
  color: #adb5bd;
}
.list-group {
  border-radius: 0.375rem;
  overflow: hidden;
}
.list-group-item.note-item {
  cursor: pointer;
  transition: background-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
  margin-bottom: 10px;
  border-radius: 6px !important;
  border: 1px solid #dee2e6;
  padding: 1rem 1.25rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}
.list-group-item.note-item:last-child {
  margin-bottom: 0;
}
.list-group-item.note-item:hover {
  background-color: #f8f9fa;
  border-color: #ced4da;
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.06);
}
.list-group-item.note-item.is-expanded {
  background-color: #f0f2f5;
  border-color: #adb5bd;
}
.note-header-line {
  margin-bottom: 0.5rem;
}
.note-title {
  font-weight: 600;
  color: #212529;
}
.note-date {
  font-size: 0.85em;
  white-space: nowrap;
  padding-left: 10px;
  color: #6c757d;
}
.note-content {
  white-space: pre-wrap;
  word-break: break-word;
  color: #495057;
  font-size: 0.95rem;
  line-height: 1.6;
  transition: all 0.3s ease-out;
}
.read-more-indicator {
  color: #0d6efd;
  font-size: 0.85em;
  margin-left: 5px;
  font-style: italic;
}
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1050;
}
.modal-content {
  background-color: #fff;
  padding: 25px 30px 30px 30px;
  border-radius: 0.5rem;
  box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.2);
  position: relative;
  width: 90%;
  max-width: 700px;
  max-height: 90vh;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}
.modal-close-btn {
  position: absolute;
  top: 12px;
  right: 15px;
  background: none;
  border: none;
  font-size: 2rem;
  font-weight: bold;
  color: #adb5bd;
  cursor: pointer;
  line-height: 1;
  padding: 0;
}
.modal-close-btn:hover {
  color: #495057;
}
.add-new-note-modal .modal-title {
  margin-bottom: 1.5rem !important;
  font-weight: 500;
  color: #343a40;
  text-align: center;
}
.modal-content .form-label {
  font-weight: 500;
  margin-bottom: 0.3rem;
  font-size: 0.9rem;
}
.modal-content .form-control,
.modal-content .form-check-input {
  font-size: 0.95rem;
}
.modal-content textarea.form-control {
  min-height: 150px;
}
.modal-checks {
  max-height: 200px;
  overflow-y: auto;
  border: 1px solid #dee2e6;
  padding: 10px 15px;
  margin-bottom: 1rem !important;
  background-color: #f8f9fa;
}
.modal-checks .form-check {
  margin-bottom: 0.6rem;
}
.modal-checks .form-check:last-child {
  margin-bottom: 0;
}
.modal-checks .form-check-label {
  font-size: 0.9rem;
}
.modal-actions {
  margin-top: 1.5rem;
  padding-top: 1rem;
  border-top: 1px solid #dee2e6;
}
.modal-actions .btn {
  font-size: 0.9rem;
  padding: 0.4rem 1rem;
}
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
.fade-enter-to,
.fade-leave-from {
  opacity: 1;
}
.spinner-border-sm {
  width: 1em;
  height: 1em;
  border-width: 0.15em;
  vertical-align: -0.125em;
}
.alert-dismissible .btn-close {
  padding: 0.75rem 1rem;
  top: 0;
  right: 0;
  z-index: 2;
}
</style>
