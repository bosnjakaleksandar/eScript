<script>
import SubjectsService from "../../services/api/Subjects";
import createNoteService from "../../services/api/CreateNote";

export default {
  name: "AddNoteModal",
  props: { show: { type: Boolean, default: false } },
  emits: ["close", "note-added"],
  data() {
    return {
      subjects: [],
      selectedCategory: null,
      noteTitle: "",
      noteText: "",
      modalErrorMessage: "",
      modalSuccessMessage: "",
      formSubmitted: false,
      isLoadingSubmit: false,
      isLoadingSubjects: false,
      subjectsError: "",
    };
  },
  methods: {
    async fetchSubjectsInternal() {
      if (this.subjects.length > 0) return;
      this.isLoadingSubjects = true;
      this.subjectsError = "";
      try {
        const response = await SubjectsService.getSubjects();
        if (response && response.success) {
          this.subjects = response.subjects || [];
        } else {
          this.subjectsError = response?.error || "Failed to load subjects.";
          console.error("Error fetching subjects for modal:", response);
        }
      } catch (err) {
        this.subjectsError = "Network error loading subjects.";
        console.error("Fetch subjects network/processing error:", err);
      } finally {
        this.isLoadingSubjects = false;
      }
    },
    async submitNoteInternal() {
      this.formSubmitted = true;
      if (!this.noteText || !this.selectedCategory || !this.noteTitle) {
        this.modalErrorMessage =
          "Please fill in title, note text and select a subject.";
        this.modalSuccessMessage = "";
        return;
      }
      this.isLoadingSubmit = true;
      this.modalErrorMessage = "";
      this.modalSuccessMessage = "";
      try {
        const response = await createNoteService.createNote({
          title: this.noteTitle,
          content: this.noteText,
          subject_id: this.selectedCategory,
        });
        if (response && response.success) {
          this.modalSuccessMessage = "Note submitted successfully!";
          this.$emit("note-added");
          setTimeout(() => {
            this.closeModal(true);
          }, 1500);
        } else {
          this.modalErrorMessage = response?.error || "Failed to submit note.";
        }
      } catch (err) {
        this.modalErrorMessage = `Error submitting note: ${
          err.message || "Network/server error"
        }.`;
        console.error("Submit note network/processing error:", err);
      } finally {
        this.isLoadingSubmit = false;
      }
    },
    resetForm() {
      this.noteTitle = "";
      this.noteText = "";
      this.selectedCategory = null;
      this.modalErrorMessage = "";
      this.modalSuccessMessage = "";
      this.formSubmitted = false;
    },
    closeModal(submitted = false) {
      if (!submitted) {
        this.resetForm();
      }
      this.$emit("close");
    },
    loadSubjectsIfNeeded() {
      if (this.show && this.subjects.length === 0) {
        this.fetchSubjectsInternal();
      }
    },
  },
  watch: {
    show(newValue, oldValue) {
      if (newValue) {
        this.loadSubjectsIfNeeded();
        if (this.modalSuccessMessage) this.modalSuccessMessage = "";
        if (this.modalErrorMessage) this.modalErrorMessage = "";
      } else {
        if (!this.modalSuccessMessage) {
          this.resetForm();
        }
      }
    },
  },
};
</script>
<template>
  <transition name="modal-fade">
    <div class="modal-overlay" v-if="show" @click.self="closeModal">
      <div
        class="modal-content"
        role="dialog"
        aria-modal="true"
        aria-labelledby="addNoteModalTitle"
      >
        <button
          class="modal-close-btn"
          @click="closeModal"
          aria-label="Close modal"
        >
          &times;
        </button>
        <div class="add-new-note-modal">
          <h5 class="mb-3 modal-title text-center" id="addNoteModalTitle">
            Add New Note
          </h5>
          <div
            v-if="modalErrorMessage"
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
          >
            {{ modalErrorMessage }}
            <button
              type="button"
              class="btn-close"
              @click="modalErrorMessage = ''"
              aria-label="Close error message"
            ></button>
          </div>
          <div
            v-if="modalSuccessMessage"
            class="alert alert-success"
            role="status"
          >
            {{ modalSuccessMessage }}
          </div>
          <form @submit.prevent="submitNoteInternal" novalidate>
            <div class="mb-3">
              <label for="modalNoteTitleInput" class="form-label">Title</label>
              <input
                id="modalNoteTitleInput"
                v-model.trim="noteTitle"
                type="text"
                class="form-control"
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
                id="modalNoteTextInput"
                v-model="noteText"
                rows="8"
                class="form-control"
                placeholder="Write your note here..."
                required
                aria-required="true"
              ></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Select Subject:</label>
              <div v-if="isLoadingSubjects" class="text-muted small mb-2">
                <span
                  class="spinner-border spinner-border-sm"
                  role="status"
                  aria-hidden="true"
                ></span>
                Loading subjects...
              </div>
              <div
                v-else-if="subjects.length === 0"
                class="alert alert-warning small p-2"
              >
                {{
                  subjectsError || "No subjects available. Add subjects first."
                }}
              </div>
              <div
                v-else
                class="checks modal-checks mb-3 border rounded p-2 bg-light"
              >
                <div
                  v-for="subject in subjects"
                  :key="subject.id"
                  class="form-check"
                >
                  <input
                    type="radio"
                    class="form-check-input"
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
              <div
                v-if="!selectedCategory && formSubmitted"
                class="text-danger small mt-1"
              >
                Please select a year.
              </div>
            </div>
            <div class="modal-actions text-end">
              <button
                type="button"
                class="btn btn-secondary me-2"
                @click="closeModal"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="btn btn-primary"
                :disabled="
                  isLoadingSubmit ||
                  isLoadingSubjects ||
                  subjects.length === 0 ||
                  !selectedCategory
                "
              >
                <span
                  v-if="isLoadingSubmit"
                  class="spinner-border spinner-border-sm"
                  role="status"
                  aria-hidden="true"
                ></span>
                {{ isLoadingSubmit ? " Submitting..." : "Submit Note" }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </transition>
</template>
<style scoped>
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
  font-weight: 500;
  color: #343a40;
}
.modal-content .form-label {
  font-weight: 500;
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
  border-top: 1px solid #dee2e6;
}
.modal-actions .btn {
  font-size: 0.9rem;
}
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
</style>
