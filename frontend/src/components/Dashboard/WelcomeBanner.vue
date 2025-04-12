<script>
export default {
  name: "WelcomeBanner",
  props: {
    userName: {
      type: String,
      required: true,
      default: "User",
    },
    formattedDate: {
      type: String,
      required: true,
    },
  },
};
</script>
<template>
  <div class="row mb-4">
    <div class="col-12">
      <div class="welcome-content rounded-4">
        <div class="date-time">
          {{ formattedDate }}
        </div>
        <div class="title">
          <h1>Welcome back, {{ userName || "User" }}!</h1>
          <p>Always stay updated in your student portal</p>
        </div>
        <img
          src="/img/Student.png"
          class="img-fluid"
          alt="Student illustration"
        />
      </div>
    </div>
  </div>
</template>
<style scoped>
.welcome-content {
  position: relative;
  min-height: 270px;
  height: auto;
  background: linear-gradient(to right, rgba(0, 74, 173, 1) 0%, #3a8fd5 100%);
  width: 100%;
  padding: 30px 40px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  overflow: hidden;
  color: white;
}
.welcome-content img {
  position: absolute;
  bottom: 0;
  right: 20px;
  max-height: 110%;
  width: auto;
  pointer-events: none;
}
.title,
.date-time {
  position: relative;
  z-index: 1;
}
.date-time {
  font-size: 0.9rem;
  opacity: 0.8;
  margin-bottom: 5%;
}
.title h1 {
  font-size: clamp(1.8rem, 3vw, 2.5rem);
  margin-bottom: 5px;
}
.title p {
  font-size: clamp(1rem, 1.5vw, 1.1rem);
  opacity: 0.9;
}

@media (max-width: 767.98px) {
  .welcome-content {
    padding: 20px;
    text-align: center;
  }
  .welcome-content img {
    position: static;
    display: block;
    max-height: 100px;
    opacity: 0.6;
    margin: 15px auto 0;
  }
  .title h1 {
    font-size: 1.6rem;
  }
  .title p {
    font-size: 1rem;
  }
}
</style>
