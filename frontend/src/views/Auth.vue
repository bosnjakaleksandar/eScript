<script>
import login from "../services/api/Auth";
import register from "../services/api/Auth";

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
      error: "",
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
      this.error = "";

      try {
        let response;

        if (this.isLoginForm) {
          response = await login.login(this.loginForm);
        } else {
          if (
            this.registerForm.password !== this.registerForm.confirmPassword
          ) {
            this.error = "Passwords do not match.";
            return;
          }

          if (!this.registerForm.termsAccepted) {
            this.error = "Please accept the terms and conditions.";
            return;
          }

          response = await register.register(this.registerForm);
        }

        if (response.success) {
          console.log(
            this.isLoginForm ? "Login success:" : "Registration success:",
            response
          );

          localStorage.setItem("user", JSON.stringify(response.user));

          if (response.role === "admin") {
            this.$router.push("/admin-dashboard");
          } else {
            this.$router.push("/dashboard");
          }
        } else {
          this.error =
            response.error ||
            (this.isLoginForm ? "Login failed." : "Registration failed.");
        }
      } catch (error) {
        console.error("Error during login/register:", error);
        this.error = error.message || "An unexpected error occurred.";
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
  <div class="body-color">
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
                <a href="#"
                  ><i class="fa-brands fa-square-facebook fs-3"></i
                ></a>
                <a href="#"
                  ><i class="fa-brands fa-square-instagram fs-3"></i
                ></a>
                <a href="#"><i class="fa-brands fa-linkedin fs-3"></i></a>
              </div>
            </div>

            <div
              class="divider d-flex align-items-center my-1"
              v-if="isLoginForm"
            >
              <p class="text-center fw-bold mx-3 mb-0">Or</p>
            </div>

            <div v-if="error">
              <div
                class="alert alert-danger alert-dismissible fade show"
                role="alert"
              >
                {{ error }}
                <button
                  type="button"
                  class="btn-close"
                  @click="error = ''"
                ></button>
              </div>
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
  </div>
</template>

<style scoped>
.body-color {
  background-color: rgba(0, 74, 173, 1) !important;
}
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
