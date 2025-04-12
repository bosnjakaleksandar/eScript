<script>
import ConfirmDialog from "./ConfirmDialog.vue";

export default {
  name: "NoteList",
  components: {
    ConfirmDialog,
  },
  props: {
    notes: { type: Array, required: true, default: () => [] },
    isLoading: { type: Boolean, default: false },
    error: { type: String, default: "" },
  },
  emits: ["delete-note"],
  data() {
    return {
      expandedNoteId: null,
      notePreviewLength: 100,
      showConfirmDialog: false,
      noteIdToDelete: null,
      noteTitleToDelete: "",
    };
  },
  computed: {
    processedNotes() {
      return this.notes.map((note) => {
        const isExpanded = this.expandedNoteId === note.id;
        const requiresTruncation = this.shouldTruncate(note.content);
        let displayContent = note.content;
        if (!isExpanded && requiresTruncation) {
          let preview = String(note.content || "").substring(
            0,
            this.notePreviewLength
          );
          if (
            note.content &&
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
        return { ...note, isExpanded, requiresTruncation, displayContent };
      });
    },
    confirmDialogMessage() {
      return `Are you sure you want to delete the note "${
        this.noteTitleToDelete || "this note"
      }"?`;
    },
  },
  methods: {
    toggleNoteExpansion(noteId) {
      this.expandedNoteId = this.expandedNoteId === noteId ? null : noteId;
    },
    shouldTruncate(content) {
      return (
        typeof content === "string" && content.length > this.notePreviewLength
      );
    },
    formatNoteDate(dateString) {
      if (!dateString) return "N/A";
      try {
        const date = new Date(dateString);
        const options = { year: "numeric", month: "short", day: "numeric" };
        return date.toLocaleDateString("en-GB", options);
      } catch (e) {
        return dateString;
      }
    },
    requestDeleteConfirmation(noteId, noteTitle) {
      this.noteIdToDelete = noteId;
      this.noteTitleToDelete = noteTitle || "this note";
      this.showConfirmDialog = true;
    },
    handleConfirmDelete() {
      if (this.noteIdToDelete !== null) {
        this.$emit("delete-note", this.noteIdToDelete);
      }
      this.closeConfirmDialog();
    },
    handleCancelDelete() {
      this.closeConfirmDialog();
    },
    closeConfirmDialog() {
      this.showConfirmDialog = false;
      setTimeout(() => {
        this.noteIdToDelete = null;
        this.noteTitleToDelete = "";
      }, 100);
    },
  },
};
</script>
<template>
  <div class="user-notes">
    <div v-if="isLoading" class="loading-message text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2 text-muted">Loading your notes...</p>
    </div>

    <div
      v-else-if="error"
      class="alert alert-warning mx-auto mt-3"
      role="alert"
      style="max-width: 95%"
    >
      <i class="fas fa-exclamation-triangle me-1"></i> {{ error }}
    </div>

    <div
      v-else-if="!notes || notes.length === 0"
      class="text-muted no-notes-message text-center"
    >
      <i class="fas fa-sticky-note fa-2x my-3"></i>
      <p>
        You haven't created any notes yet.<br />Click "Add Note" to create one.
      </p>
    </div>

    <div v-else class="list-group">
      <div
        v-for="note in processedNotes"
        :key="note.id"
        class="list-group-item list-group-item-action flex-column align-items-start note-item mb-2 rounded border shadow-sm p-3"
        :class="{ 'is-expanded': note.isExpanded }"
        @click="toggleNoteExpansion(note.id)"
        role="button"
        tabindex="0"
        @keydown.enter="toggleNoteExpansion(note.id)"
        @keydown.space.prevent="toggleNoteExpansion(note.id)"
      >
        <div class="d-flex w-100 justify-content-between note-header-line mb-2">
          <h6 class="mb-1 note-title">{{ note.title }}</h6>
          <div class="note-meta d-flex align-items-center">
            <small class="text-muted note-date me-2">{{
              formatNoteDate(note.created_at)
            }}</small>
            <button
              class="btn btn-sm btn-outline-danger delete-btn p-1"
              title="Delete Note"
              @click.stop="requestDeleteConfirmation(note.id, note.title)"
            >
              <i class="fa-solid fa-trash-can"></i>
            </button>
          </div>
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

    <ConfirmDialog
      :show="showConfirmDialog"
      title="Confirm Deletion"
      :message="confirmDialogMessage"
      confirm-button-text="Delete"
      confirm-button-type="danger"
      @confirm="handleConfirmDelete"
      @cancel="handleCancelDelete"
    />
  </div>
</template>
<style scoped>
.loading-message,
.no-notes-message {
  margin-top: 2rem;
}
.no-notes-message {
  background-color: #f8f9fa;
  border: 1px dashed #ced4da;
}
.no-notes-message i {
  color: #adb5bd;
}
.note-item {
  cursor: pointer;
  transition: background-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.note-item:hover {
  background-color: #f8f9fa;
  border-color: #ced4da;
}
.note-item.is-expanded {
  background-color: #f0f2f5;
  border-color: #adb5bd;
}
.note-title {
  font-weight: 600;
  color: #212529;
}
.note-date {
  font-size: 0.85em;
  white-space: nowrap;
}
.note-meta {
  gap: 0.5rem;
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
.delete-btn {
  line-height: 1;
  opacity: 0.6;
  transition: opacity 0.2s ease-in-out;
  font-size: 0.8em;
}
.note-item:hover .delete-btn {
  opacity: 1;
}
.delete-btn:hover {
  opacity: 1;
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
</style>
