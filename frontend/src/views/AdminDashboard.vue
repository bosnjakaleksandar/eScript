<script setup>
import Sidebar from "../components/Sidebar.vue";
import AdminItems from "../components/AdminItems.vue";
</script>
<template>
  <div class="container-custom">
    <!-- Sidebar -->
    <div class="row">
      <Sidebar />
      <!-- Main Content -->
      <AdminItems />
    </div>
  </div>
</template>
<style scoped>
.container-custom {
  margin-left: 3%;
  margin-right: 3%;
}
</style>
