<script setup>
import Sidebar from "../components/Sidebar.vue";
import MyNotesContent from "../components/Notes/MyNotesContent.vue";
</script>

<template>
  <div class="app-container">
    <Sidebar />
    <div class="main-content-wrapper">
      <MyNotesContent />
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
    margin-left: calc(70px + 1%);
    width: calc(100% - (70px + 1%));
    padding-left: 2%;
    padding-right: 2%;
  }
}

@media (max-width: 576px) {
  .main-content-wrapper {
    margin-left: 0;
    width: 100%;
    padding-left: 1%;
    padding-right: 1%;
  }
}
</style>
