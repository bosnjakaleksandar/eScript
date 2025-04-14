<script>
export default {
  name: "StatCard",
  props: {
    iconClass: {
      type: String,
      required: true,
    },
    value: {
      type: [String, Number],
      required: true,
    },
    label: {
      type: String,
      required: true,
    },
  },
};
</script>
<template>
  <div class="col-md-4 mb-4">
    <div
      class="card dash-card border-0 rounded-3 text-center justify-content-center"
    >
      <i :class="iconClass" class="mb-2"></i>
      <h4 class="fs-2">{{ value }}</h4>
      <h5 class="fs-5">{{ label }}</h5>
    </div>
  </div>
</template>
<style scoped>
.dash-card {
  box-shadow: 0 0.4rem 1rem rgba(0, 0, 0, 0.08);
  aspect-ratio: 1/1;
  transition: all 0.3s ease;
  background-color: #fff;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 20px;
  height: 100%;
}

.dash-card i {
  font-size: clamp(40px, 8vw, 60px);
  color: rgba(0, 74, 173, 1);
  margin-bottom: 15px;
}

.dash-card h4 {
  font-size: clamp(1.8rem, 5vw, 2.2rem);
  font-weight: 600;
}

.dash-card h5 {
  font-size: clamp(0.9rem, 2vw, 1rem);
  opacity: 0.7;
  font-weight: 400;
}

.dash-card:hover {
  transform: translateY(-5px) scale(1.02);
  box-shadow: 0 0.75rem 1.5rem rgba(0, 74, 173, 0.15);
}

@media (max-width: 768px) {
  .dash-card i {
    font-size: 45px;
  }
  .dash-card h4 {
    font-size: 1.8rem;
  }
  .dash-card h5 {
    font-size: 0.9rem;
  }
  .dash-card {
    aspect-ratio: unset;
  }
}
</style>
