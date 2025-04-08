<script setup>
import Sidebar from "../components/Sidebar.vue";
import DashboardItems from "../components/DashboardItems.vue";
</script>
<template>
  <div class="main-container">
    <!-- Sidebar -->
    <Sidebar />

    <!-- Main Content -->
    <DashboardItems />
  </div>
</template>
<style scoped>
.main-container {
  display: flex;
  width: 100%;
  height: 100vh;
}
</style>
