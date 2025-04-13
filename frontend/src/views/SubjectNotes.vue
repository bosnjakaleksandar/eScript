<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";
import Sidebar from "../components/Sidebar.vue";
import SubjectNotesContent from "../components/SubjectNoteContent.vue";

const route = useRoute();

const subjectId = computed(() => {
  const id = route.params.subjectId;
  return id ? parseInt(id, 10) : null;
});
</script>

<template>
  <div class="app-container">
    <Sidebar />
    <div class="main-content-wrapper flex-grow-1">
      <SubjectNotesContent v-if="subjectId !== null" :subject-id="subjectId" />
      <div v-else class="alert alert-danger m-3">
        Error: Subject ID not found in URL.
      </div>
    </div>
  </div>
</template>

<style scoped>
.main-content-wrapper {
  margin-left: 19%;
  width: calc(100% - 19%);
  padding-top: 20px;
  padding-bottom: 20px;
  padding-right: 5%;
  min-height: 100vh;
}

@media (max-width: 768px) {
  .main-content-wrapper {
    padding-left: 2%;
    padding-right: 2%;
  }
}
</style>
