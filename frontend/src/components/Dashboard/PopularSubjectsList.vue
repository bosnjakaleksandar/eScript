<script>
export default {
  name: "PopularSubjectsList",
  props: {
    subjects: {
      type: Array,
      required: true,
      default: () => [],
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
};
</script>
<template>
  <div class="row mt-4">
    <h3 class="mb-3">Popular Subjects</h3>

    <div v-if="isLoading" class="col-12 text-center text-muted py-3">
      <div class="spinner-border spinner-border-sm text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="small mb-0 mt-1">Loading popular subjects...</p>
    </div>

    <div v-else-if="error" class="col-12 alert alert-warning small p-2">
      <i class="fas fa-exclamation-triangle me-1"></i> {{ error }}
    </div>

    <div
      v-else-if="!subjects || subjects.length === 0"
      class="col-12 text-center text-muted py-3"
    >
      <p class="small mb-0">No subjects with notes found yet.</p>
    </div>

    <template v-else>
      <div
        v-for="subject in subjects"
        :key="subject.subject_id"
        class="col-md-6 mb-4"
      >
        <div class="card border-0 rounded-3 p-3 h-100 d-flex flex-column">
          <h5 class="mb-1">{{ subject.subject_name }}</h5>
          <p class="small text-muted mt-auto mb-0">
            <i class="fa-solid fa-file-lines me-1"></i>
            {{ subject.note_count }}
            {{ subject.note_count === 1 ? "note" : "notes" }}
          </p>
        </div>
      </div>
    </template>
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
