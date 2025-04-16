<script>
export default {
  name: "UnauthorizedView",
  methods: {
    goToLogin() {
      this.$router.push({ name: "Auth" });
    },
  },
};
</script>
<template>
  <div
    class="unauthorized-container d-flex align-items-center justify-content-center text-center"
  >
    <div class="content-box p-4 p-md-5 rounded shadow-sm bg-light">
      <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
      <h2 class="mb-3">Access Denied</h2>
      <p class="lead mb-4">You must be logged in to access this page.</p>
      <button @click="goToLogin" class="btn btn-primary btn-lg">
        Go to login
      </button>
    </div>
  </div>
</template>
<style scoped>
.unauthorized-container {
  min-height: 100vh;
  background-color: rgba(0, 74, 173, 1);
}
.content-box {
  max-width: 500px;
  width: 90%;
  background-color: #ffffff;
}
h2 {
  color: #dc3545;
}
.lead {
  font-size: 1.1rem;
  color: #495057;
}
.fas {
  display: block;
}
</style>
