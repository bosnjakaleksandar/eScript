<script setup>
import Sidebar from "../components/Sidebar.vue";
import AdminItems from "../components/AdminItems.vue";
</script>
<template>
  <div class="main-container">
    <!-- Sidebar -->
    <Sidebar />

    <!-- Main Content -->
    <AdminItems />
  </div>
</template>
<style scoped>
.main-container {
  display: flex;
  width: 100%;
  height: 100vh;
}
</style>
