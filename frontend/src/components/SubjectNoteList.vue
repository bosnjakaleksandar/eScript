<script>
export default {
  name: "SubjectNoteList",
  props: {
    notes: { type: Array, required: true, default: () => [] },
    isLoading: { type: Boolean, default: false },
    error: { type: String, default: "" },
  },
  data() {
    return {
      expandedNoteId: null,
      notePreviewLength: 150,
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
        return {
          ...note,
          isExpanded,
          requiresTruncation,
          displayContent,
          author_name: note.author_name,
        };
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
      class="text-muted no-notes-message"
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
        @click="toggleNoteExpansion(note.id)"
        role="button"
        tabindex="0"
        @keydown.enter="toggleNoteExpansion(note.id)"
        @keydown.space.prevent="toggleNoteExpansion(note.id)"
      >
        <div class="d-flex w-100 justify-content-between note-header-line mb-2">
          <h6 class="mb-1 note-title">{{ note.title }}</h6>
          <div class="note-meta text-end">
            <span
              v-if="note.author_name"
              class="d-block small text-muted"
              title="Author"
            >
              <i class="fa-regular fa-user me-1"></i>{{ note.author_name }}
            </span>
            <small class="text-muted note-date d-block mt-1" title="Creation date">
              <i class="fa-regular fa-calendar-alt me-1"></i
              >{{ formatNoteDate(note.created_at) }}
            </small>
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
  </div>
</template>
<style scoped>
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
.list-group-item.note-item {
  cursor: pointer;
  transition: background-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.list-group-item.note-item:last-child {
  margin-bottom: 0 !important;
}
.list-group-item.note-item:hover {
  background-color: #f8f9fa;
  border-color: #ced4da;
}
.list-group-item.note-item.is-expanded {
  background-color: #f0f2f5;
  border-color: #adb5bd;
}
.note-title {
  font-weight: 600;
  color: #212529;
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
}
.read-more-indicator {
  color: #0d6efd;
  font-size: 0.85em;
  margin-left: 5px;
  font-style: italic;
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
