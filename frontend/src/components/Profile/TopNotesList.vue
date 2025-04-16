<script>
import StarRating from "../StarRating.vue";

export default {
  name: "TopNotesList",
  components: { StarRating },
  props: {
    notes: { type: Array, default: () => [] },
    isLoading: { type: Boolean, default: false },
    error: { type: String, default: "" },
    profileUsername: {
      type: String,
      default: "User",
    },
  },
  methods: {
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
    truncateContent(content) {
      const maxLength = 80;
      if (typeof content === "string" && content.length > maxLength) {
        return content.substring(0, maxLength) + "...";
      }
      return content || "";
    },
  },
};
</script>
<template>
  <div class="top-notes-section mt-4">
    <h3 class="mb-3">{{ profileUsername }}'s Top Rated Notes</h3>

    <div v-if="isLoading" class="text-center py-3">
      <span class="spinner-border spinner-border-sm"></span> Loading...
    </div>
    <div v-else-if="error" class="alert alert-warning">{{ error }}</div>
    <div
      v-else-if="!notes || notes.length === 0"
      class="alert alert-light text-center"
    >
      This user doesn't have any rated notes yet.
    </div>
    <div v-else class="list-group">
      <router-link
        v-for="note in notes"
        :key="note.id"
        :to="{ name: 'SubjectNotes', params: { subjectId: note.subject_id } }"
        class="list-group-item list-group-item-action top-note-item"
      >
        <div class="d-flex w-100 justify-content-between mb-1">
          <h6 class="mb-0 note-title">{{ note.title }}</h6>
          <small class="text-muted">{{
            formatNoteDate(note.created_at)
          }}</small>
        </div>
        <div class="d-flex align-items-center justify-content-between">
          <p class="mb-0 small note-content-preview">
            {{ truncateContent(note.content) }}
          </p>
          <div class="text-end ms-2 flex-shrink-0">
            <StarRating
              :modelValue="parseFloat(note.average_grade || 0)"
              :read-only="true"
            />
            <span class="d-block small text-muted rating-count">
              ({{ note.rating_count || 0 }} ratings)
            </span>
          </div>
        </div>
      </router-link>
    </div>
  </div>
</template>
<style scoped>
.top-notes-section h3 {
  font-weight: 600;
  color: #343a40;
}
.top-note-item {
  transition: background-color 0.2s ease;
}
.note-title {
  font-weight: 500;
  color: #212529;
}
.note-content-preview {
  color: #6c757d;
  font-style: italic;
}
.rating-count {
  font-size: 0.8em;
}
.top-note-item :deep(.star-rating) {
  font-size: 0.9rem;
}
.top-note-item :deep(.clear-rating-btn) {
  display: none;
}
</style>
