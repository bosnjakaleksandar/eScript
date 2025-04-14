<script>
import StarRating from "./StarRating.vue";
import RateNoteService from "../services/api/RateNote";

export default {
  name: "SubjectNoteList",
  components: { StarRating },
  props: {
    notes: { type: Array, required: true, default: () => [] },
    isLoading: { type: Boolean, default: false },
    error: { type: String, default: "" },
  },
  emits: ["note-rated"],
  data() {
    return {
      expandedNoteId: null,
      notePreviewLength: 150,
      loggedInUserId: null,
      ratingStates: {},
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
        const options = {
          year: "numeric",
          month: "short",
          day: "numeric",
          hour: "2-digit",
          minute: "2-digit",
          hour12: false,
        };
        return date.toLocaleString("en-GB", options);
      } catch (e) {
        return dateString;
      }
    },
    getLoggedInUserId() {
      try {
        const userData = localStorage.getItem("user");
        this.loggedInUserId = userData ? JSON.parse(userData)?.id : null;
      } catch (e) {
        console.error("Error getting user ID:", e);
        this.loggedInUserId = null;
      }
    },
    async handleRatingSubmit(noteId, authorId, newGrade) {
      if (authorId === this.loggedInUserId || this.loggedInUserId === null)
        return;
      this.ratingStates = {
        ...this.ratingStates,
        [noteId]: { isLoading: true, error: "" },
      };
      try {
        const response = await RateNoteService.rateNote(noteId, newGrade);
        if (response && response.success) {
          this.$emit("note-rated");
          this.ratingStates = {
            ...this.ratingStates,
            [noteId]: { isLoading: false, error: "" },
          };
        } else {
          const errorMsg = response?.error || "Failed to save rating.";
          this.ratingStates = {
            ...this.ratingStates,
            [noteId]: { isLoading: false, error: errorMsg },
          };
          console.error(`Error rating note ${noteId}:`, errorMsg);
        }
      } catch (err) {
        const errorMsg = err.message || "Network error saving rating.";
        this.ratingStates = {
          ...this.ratingStates,
          [noteId]: { isLoading: false, error: errorMsg },
        };
        console.error(`Network error rating note ${noteId}:`, err);
      } finally {
        if (this.ratingStates[noteId]?.error) {
          setTimeout(() => {
            if (this.ratingStates[noteId]) {
              this.ratingStates[noteId].error = "";
            }
          }, 4000);
        }
      }
    },
  },
  created() {
    this.getLoggedInUserId();
  },
};
</script>
<template>
  <div class="subject-note-list">
    <div v-if="isLoading" class="loading-message text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2 text-muted">Loading notes...</p>
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
      class="text-muted no-notes-message text-center py-5"
    >
      <i class="fas fa-folder-open fa-2x mb-3"></i>
      <p>No notes found for this subject yet.</p>
    </div>
    <div v-else class="list-group">
      <div
        v-for="note in processedNotes"
        :key="note.id"
        class="list-group-item list-group-item-action flex-column align-items-start note-item mb-2 rounded border shadow-sm p-3"
        :class="{ 'is-expanded': note.isExpanded }"
        role="button"
        tabindex="0"
        @keydown.enter="toggleNoteExpansion(note.id)"
        @keydown.space.prevent="toggleNoteExpansion(note.id)"
      >
        <div class="d-flex w-100 justify-content-between note-header-line mb-2">
          <h6
            class="mb-1 note-title"
            @click="toggleNoteExpansion(note.id)"
            style="cursor: pointer"
          >
            {{ note.title }}
          </h6>
          <div class="note-meta text-end">
            <span
              v-if="note.author_name"
              class="d-block small text-muted"
              title="Author"
            >
              <i class="fa-regular fa-user me-1"></i>{{ note.author_name }}
            </span>
            <small class="text-muted note-date d-block" title="Creation date">
              <i class="fa-regular fa-calendar-alt me-1"></i
              >{{ formatNoteDate(note.created_at) }}
            </small>
          </div>
        </div>
        <transition name="fade" mode="out-in">
          <p
            class="mb-1 note-content"
            @click="toggleNoteExpansion(note.id)"
            style="cursor: pointer"
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
        <div
          class="note-actions mt-2 pt-2 border-top d-flex align-items-center justify-content-between flex-wrap gap-2"
        >
          <div>
            <StarRating
              :modelValue="parseFloat(note.average_grade || 0)"
              :disabled="note.user_id === loggedInUserId"
              @rating-selected="
                handleRatingSubmit(note.id, note.user_id, $event)
              "
            />
            <span
              v-if="note.user_id === loggedInUserId"
              class="ms-2 small text-muted"
              >(Cannot rate own note)</span
            >
          </div>
          <div class="rating-feedback text-end">
            <span
              v-if="ratingStates[note.id]?.isLoading"
              class="ms-2 small text-muted fst-italic"
              >Saving...</span
            >
            <span
              v-if="ratingStates[note.id]?.error"
              class="ms-2 small text-danger"
              >{{ ratingStates[note.id]?.error }}</span
            >
            <span
              v-if="
                note.rating_count > 0 &&
                !ratingStates[note.id]?.isLoading &&
                !ratingStates[note.id]?.error
              "
              class="ms-2 small text-muted"
            >
              ({{ note.rating_count }}
              {{ note.rating_count === 1 ? "rating" : "ratings" }})
            </span>
            <span
              v-else-if="
                !ratingStates[note.id]?.isLoading &&
                !ratingStates[note.id]?.error
              "
              class="ms-2 small text-muted"
            >
              (No ratings yet)
            </span>
          </div>
        </div>
      </div>
    </div>
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
  border-radius: 0.375rem;
  padding: 30px 20px;
}
.no-notes-message i {
  color: #adb5bd;
}
.note-item {
  cursor: default;
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
  cursor: pointer;
}
.note-date {
  font-size: 0.8em;
}
.note-meta {
  line-height: 1.3;
}
.note-meta .fa-regular {
  margin-right: 4px;
}
.note-content {
  white-space: pre-wrap;
  word-break: break-word;
  color: #495057;
  font-size: 0.95rem;
  line-height: 1.6;
  transition: all 0.3s ease-out;
  cursor: pointer;
}
.read-more-indicator {
  color: #0d6efd;
  font-size: 0.85em;
  margin-left: 5px;
  font-style: italic;
}
.note-actions {
  border-top: 1px solid #eee;
}
.rating-feedback {
  flex-shrink: 0;
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
