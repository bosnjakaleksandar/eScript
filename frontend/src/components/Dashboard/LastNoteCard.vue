<script>
export default {
  name: "LastNoteCard",
  props: {
    note: {
      type: Object,
      default: null,
    },
    isLoading: {
      type: Boolean,
      default: false,
    },
    error: {
      type: String,
      default: "",
    },
  },
  computed: {
    truncatedContent() {
      if (this.note && typeof this.note.content === "string") {
        const maxLength = 300;
        if (this.note.content.length > maxLength) {
          return this.note.content.substring(0, maxLength) + "...";
        }
        return this.note.content;
      }
      return "";
    },
    formattedDate() {
      if (!this.note || !this.note.created_at) {
        return "N/A";
      }
      try {
        const date = new Date(this.note.created_at);
        const options = {
          month: "long",
          day: "numeric",
          hour: "2-digit",
          minute: "2-digit",
          hour12: false,
        };
        return date.toLocaleString("sr-Latn-RS", options);
      } catch (e) {
        console.error("Error formatting date:", this.note.created_at, e);
        return this.note.created_at;
      }
    },
  },
};
</script>
<template>
  <div class="row">
    <h5 class="mt-3">Last Added Note</h5>
    <div class="card border-0 rounded-3 p-3">
      <div v-if="isLoading" class="text-center text-muted py-3">
        <div class="spinner-border spinner-border-sm" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="small mb-0 mt-1">Loading script...</p>
      </div>
      <div v-else-if="error" class="text-danger small py-3">
        <i class="fas fa-exclamation-triangle me-1"></i> {{ error }}
      </div>
      <div v-else-if="note">
        <h6 class="mb-1">{{ note.title }}</h6>
        <p class="small text-muted mb-1">{{ truncatedContent }}</p>
        <div
          class="d-flex justify-content-between align-items-center mt-2 border-top pt-2"
        >
          <span class="small text-muted">
            <i class="fa-regular fa-user me-1"></i>
            {{ note.author_name || "Unknown Author" }}
          </span>
          <span class="small text-muted">
            <i class="fa-regular fa-calendar-alt me-1"></i>
            {{ formattedDate }}
          </span>
        </div>
      </div>
      <div v-else class="text-center text-muted py-3">
        <p class="small mb-0">No notes created yet.</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.card {
  box-shadow: 0 0.4rem 1rem rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
}
.card:hover {
  transform: translateY(-5px) scale(1.02);
  box-shadow: 0 0.75rem 1.5rem rgba(0, 74, 173, 0.15);
}
.spinner-border-sm {
  display: inline-block;
  width: 1rem;
  height: 1rem;
  vertical-align: -0.125em;
  border: 0.15em solid currentColor;
  border-right-color: transparent;
  border-radius: 50%;
  animation: 0.75s linear infinite spinner-border;
}
@keyframes spinner-border {
  to {
    transform: rotate(360deg);
  }
}
</style>
