<script>
export default {
  data() {
    return {
      isLoginForm: true,
      isTransitioning: false,
      containerClass: "container",
      loginForm: {
        email: "",
        password: "",
        rememberMe: false,
      },
      registerForm: {
        username: "",
        email: "",
        password: "",
        confirmPassword: "",
        termsAccepted: false,
      },
    };
  },
  methods: {
    toggleForm() {
      this.isTransitioning = true;

      setTimeout(() => {
        this.isLoginForm = !this.isLoginForm;

        setTimeout(() => {
          this.isTransitioning = false;
        }, 500);
      }, 300);
    },

    async handleSubmit() {
      if (this.isLoginForm) {
        try {
          const response = await fetch("http://localhost:8002/login.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(this.loginForm),
          });

          const data = await response.json();

          if (data.success) {
            console.log("Login success:", data);

            localStorage.setItem("user", JSON.stringify(data.user));

            if (data.role === "admin") {
              this.$router.push("/admin-dashboard");
            } else {
              this.$router.push("/dashboard");
            }
          } else {
            alert(data.error || "Login failed.");
          }
        } catch (error) {
          console.error("Login error:", error);
          alert("Error logging in.");
        }
      } else {
        if (this.registerForm.password !== this.registerForm.confirmPassword) {
          alert("Passwords do not match.");
          return;
        }

        if (!this.registerForm.termsAccepted) {
          alert("Please accept the terms and conditions.");
          return;
        }

        try {
          const response = await fetch("http://localhost:8002/register.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(this.registerForm),
          });

          const data = await response.json();

          if (data.success) {
            console.log("Registration success:", data);
            this.$router.push("/dashboard");
          } else {
            alert(data.error || "Registration failed.");
          }
        } catch (error) {
          console.error("Registration error:", error);
          alert("Error registering.");
        }
      }
    },

    updateContainerClass() {
      if (window.innerWidth <= 768) {
        this.containerClass = "container-fluid";
      } else {
        this.containerClass = "container";
      }
    },
  },

  mounted() {
    this.updateContainerClass();
    window.addEventListener("resize", this.updateContainerClass);
  },

  beforeDestroy() {
    window.removeEventListener("resize", this.updateContainerClass);
  },
};
</script>

<template>
  <div :class="containerClass">
    <div class="registration">
      <div class="row">
        <div
          class="col-md-6 col-content justify-content-center"
          :class="[
            isLoginForm ? 'order-md-1' : 'order-md-2',
            { 'transition-content': isTransitioning },
          ]"
        >
          <h1 class="pb-2">
            {{ isLoginForm ? "Welcome to eScript!" : "Create an Account!" }}
          </h1>

          <div v-if="isLoginForm" class="d-flex align-items-center mb-2">
            <p class="fs-4 mb-0">Sign in with</p>
            <div class="social-links ms-3 d-flex gap-2 pt-1">
              <a href="#"><i class="fa-brands fa-square-facebook fs-3"></i></a>
              <a href="#"><i class="fa-brands fa-square-instagram fs-3"></i></a>
              <a href="#"><i class="fa-brands fa-linkedin fs-3"></i></a>
            </div>
          </div>

          <div
            class="divider d-flex align-items-center my-1"
            v-if="isLoginForm"
          >
            <p class="text-center fw-bold mx-3 mb-0">Or</p>
          </div>

          <form @submit.prevent="handleSubmit">
            <!-- Login Form -->
            <template v-if="isLoginForm">
              <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input
                  v-model="loginForm.email"
                  type="email"
                  class="form-control"
                  id="email"
                  aria-describedby="emailHelp"
                  required
                />
                <div id="emailHelp" class="form-text">
                  We'll never share your email with anyone else.
                </div>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input
                  v-model="loginForm.password"
                  type="password"
                  class="form-control"
                  id="password"
                  required
                />
              </div>
              <div class="d-flex justify-content-between mb-3 forgot">
                <div class="form-check mb-0">
                  <input
                    v-model="loginForm.rememberMe"
                    class="form-check-input me-2"
                    type="checkbox"
                    id="remember"
                  />
                  <label class="form-check-label" for="remember"
                    >Remember me</label
                  >
                </div>
                <a href="#" class="text-body">Forgot password?</a>
              </div>
            </template>

            <!-- Registration Form -->
            <template v-else>
              <div class="row">
                <div class="col-12 mb-3">
                  <label for="username" class="form-label">Username</label>
                  <input
                    v-model="registerForm.username"
                    type="text"
                    class="form-control"
                    id="username"
                    required
                  />
                </div>
              </div>
              <div class="row">
                <div class="col-12 mb-3">
                  <label for="registerEmail" class="form-label"
                    >Email address</label
                  >
                  <input
                    v-model="registerForm.email"
                    type="email"
                    class="form-control"
                    id="registerEmail"
                    required
                  />
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="registerPassword" class="form-label"
                    >Password</label
                  >
                  <input
                    v-model="registerForm.password"
                    type="password"
                    class="form-control"
                    id="registerPassword"
                    required
                  />
                </div>
                <div class="col-md-6 mb-3">
                  <label for="confirmPassword" class="form-label"
                    >Confirm Password</label
                  >
                  <input
                    v-model="registerForm.confirmPassword"
                    type="password"
                    class="form-control"
                    id="confirmPassword"
                    required
                  />
                </div>
              </div>
              <div class="mb-3 form-check">
                <input
                  v-model="registerForm.termsAccepted"
                  type="checkbox"
                  class="form-check-input"
                  id="termsCheck"
                  required
                />
                <label class="form-check-label" for="termsCheck"
                  >I agree to the Terms of Service</label
                >
              </div>
            </template>

            <button type="submit" class="btn btn-login">
              {{ isLoginForm ? "Login" : "Register" }}
            </button>

            <p class="small fw-bold mt-2 pt-1 mb-0">
              {{
                isLoginForm
                  ? "Don't have an account?"
                  : "Already have an account?"
              }}
              <a href="#" @click.prevent="toggleForm" class="register">
                {{ isLoginForm ? "Register" : "Login" }}
              </a>
            </p>
          </form>
        </div>
        <div
          class="col-md-6 col-logo"
          :class="[
            isLoginForm ? 'order-md-2' : 'order-md-1',
            { 'transition-logo': isTransitioning },
          ]"
        >
          <img
            class="img-fluid logo"
            src="../../public/img/logo.png"
            alt="Logo"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}
.registration {
  background-color: white;
  border-radius: 10px;
  width: 80%;
  padding: 2%;
}
.logo {
  border-radius: 10px;
}
.divider:after,
.divider:before {
  content: "";
  flex: 1;
  height: 1px;
  background: gray;
}
.social-links i,
a {
  color: #ad004a;
}
.btn-login {
  background-color: #ad004a !important;
  color: white !important;
  width: 30%;
}
.forgot a {
  text-decoration: none;
}
.registration a {
  text-decoration: none;
  color: #ad004a;
}
.transition-content,
.transition-logo {
  transition: all 0.8s ease-in-out;
  opacity: 0.5;
}
.col-content {
  transition: opacity 0.3s ease;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}
.col-logo {
  transition: opacity 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}
.col-content h1 {
  margin-bottom: 1.5rem;
}
.form-control {
  padding: 0.375rem 0.75rem;
}
.btn-login {
  padding: 0.375rem 0.75rem;
  display: inline-block;
  text-align: center;
}
form label {
  margin-bottom: 0.25rem;
}
.row {
  transition: transform 0.5s ease-in-out;
}
@media (max-width: 768px) {
  .registration {
    width: 100%;
    margin-top: 5%;
  }
  .transition-content,
  .transition-logo {
    transition: opacity 0.8s ease-in-out;
  }
  .col-content {
    height: auto !important;
    max-height: none;
  }
}
</style>
