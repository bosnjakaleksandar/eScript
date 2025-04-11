<script>
import Logout from "../services/api/Logout";

export default {
  data() {
    return {
      errorMessage: "",
      isLoggingOut: false,
    };
  },
  methods: {
    async logoutUser() {
      this.isLoggingOut = true;
      this.errorMessage = "";

      try {
        const response = await Logout.logout();

        if (response && response.success) {
          localStorage.removeItem("user");
          if (this.$router) {
            const loginRouteExists = this.$router.hasRoute("Login");
            if (loginRouteExists) {
              this.$router.push({ name: "Login" });
            } else {
              console.warn("Route 'Login' not found. Redirecting to root '/'.");
              this.$router.push("/");
            }
          } else {
            console.error("Vue Router instance ($router) is not available.");
            window.location.href = "/";
          }
        } else {
          this.errorMessage =
            response && response.error
              ? response.error
              : "Logout failed on the server.";
          console.error(
            "Logout API Error:",
            response ? response.error : "Unknown error"
          );
        }
      } catch (err) {
        console.error("Logout system error:", err);
        this.errorMessage =
          "Network or system error during logout. Please try again.";
        if (err.response) {
          console.error("Server responded with status:", err.response.status);
          console.error("Response data:", err.response.data);
        } else if (err.request) {
          console.error("No response received:", err.request);
        } else {
          console.error("Error setting up request:", err.message);
        }
      } finally {
        this.isLoggingOut = false;
      }
    },
  },
};
</script>

<template>
  <div class="alert-container" v-if="errorMessage">
    <div
      class="alert alert-danger alert-dismissible fade show m-3"
      role="alert"
    >
      {{ errorMessage }}
      <button
        type="button"
        class="btn-close"
        aria-label="Close"
        @click="errorMessage = ''"
      ></button>
    </div>
  </div>

  <div class="sidebar">
    <div class="image">
      <img
        src="../../public/img/logo.png"
        alt="Company Logo"
        class="logo img-fluid rounded-4"
      />
    </div>
    <ul class="nav-list">
      <li class="active">
        <router-link :to="{ name: 'Dashboard' }">
          <i class="fa-solid fa-gauge-high"></i>
          <span class="nav-text">Dashboard</span>
        </router-link>
      </li>
      <li>
        <router-link :to="{ name: 'Subjects' }">
          <i class="fa-solid fa-folder"></i>
          <span class="nav-text">Subjects</span>
        </router-link>
      </li>
      <li>
        <a href="#" @click.prevent>
          <i class="fa-solid fa-square-poll-vertical"></i>
          <span class="nav-text">Rankings</span>
        </a>
      </li>
      <li>
        <router-link :to="{ name: 'MyScripts' }">
          <i class="fa-solid fa-file"></i>
          <span class="nav-text">My Scripts</span>
        </router-link>
      </li>
      <li>
        <a href="#" @click.prevent>
          <i class="fa-solid fa-circle-user"></i>
          <span class="nav-text">My Profile</span>
        </a>
      </li>

      <li class="logout">
        <button
          class="btn btn-link"
          @click="logoutUser"
          :disabled="isLoggingOut"
        >
          <i class="fa-solid fa-right-from-bracket"></i>
          <span class="nav-text">
            <span
              v-if="isLoggingOut"
              class="spinner-border spinner-border-sm me-1"
              role="status"
              aria-hidden="true"
            ></span>
            {{ isLoggingOut ? "Logging out..." : "Logout" }}
          </span>
        </button>
      </li>
    </ul>
  </div>
</template>

<style scoped>
.alert-container {
  position: fixed;
  top: 10px;
  right: 10px;
  z-index: 1050;
  min-width: 300px;
}

.sidebar {
  position: fixed;
  top: 20px;
  bottom: 20px;
  left: 1%;
  width: 18%;
  max-width: 280px;
  background: linear-gradient(to top, rgba(0, 74, 173, 1) 0%, #3a8fd5 100%);
  border-radius: 1rem;
  display: flex;
  flex-direction: column;
  padding: 25px 0;
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
  z-index: 1000;
}

.image {
  text-align: center;
  flex-shrink: 0;
  padding-bottom: 25px;
  padding-top: 25px;
}

.logo {
  width: clamp(100px, 12vw, 140px);
  height: auto;
}

.nav-list {
  flex-grow: 1;
  overflow-y: auto;
  overflow-x: hidden;
  list-style: none;
  padding: 0;
  margin: 0;
}

.nav-list::-webkit-scrollbar {
  width: 5px;
}
.nav-list::-webkit-scrollbar-thumb {
  background-color: rgba(255, 255, 255, 0.25);
  border-radius: 3px;
}
.nav-list::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.1);
}

.nav-list li {
  margin: 0 10%;
  margin-bottom: 12px;
  opacity: 0.75;
  transition: all 0.25s ease-in-out;
  border-radius: 8px;
}

.nav-list li.active {
  opacity: 1;
  background-color: rgba(255, 255, 255, 0.1);
  box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.1);
}

.nav-list li:not(.active):hover {
  opacity: 1;
  background-color: rgba(255, 255, 255, 0.06);
  transform: translateX(3px);
  cursor: pointer;
}

.nav-list li > a,
.nav-list li > button {
  display: flex;
  align-items: center;
  padding: 10px 15px;
  text-decoration: none;
  color: white;
  font-size: 1rem;
  border-radius: 8px;
  width: 100%;
  border: none;
  background: none;
  text-align: left;
  font-family: inherit;
}

.nav-list i {
  width: 25px;
  text-align: center;
  margin-right: 15px;
  font-size: 1.3rem;
  flex-shrink: 0;
}

.nav-text {
  flex-grow: 1;
  white-space: nowrap;
}

.logout {
  margin-top: auto;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.logout button {
  color: #ffdddd;
  font-weight: 500;
}

.logout button:hover {
  color: #ffeded;
  background-color: rgba(255, 100, 100, 0.1);
}

.logout button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 992px) {
  .sidebar {
    width: 20%;
    left: 1%;
  }
}

@media (max-width: 768px) {
  .sidebar {
    width: 70px;
    left: 1%;
    padding: 15px 0;
  }
  .nav-text {
    display: none;
  }
  .logo {
    width: 45px;
  }
  .nav-list i {
    margin-right: 0;
    font-size: 1.5rem;
  }
  .nav-list li > a,
  .nav-list li > button {
    justify-content: center;
    padding: 12px 0;
  }
  .logout {
    border-top: none;
  }
}

@media (max-width: 576px) {
  .sidebar {
    left: -100%;
    transition: left 0.3s ease-in-out;
  }
  .sidebar.is-open {
    left: 0;
  }
}
</style>
