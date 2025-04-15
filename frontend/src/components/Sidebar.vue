<script>
import LogoutService from "../services/api/Logout";

export default {
  name: "Sidebar",
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
        const response = await LogoutService.logout();
        if (response && response.success) {
          localStorage.removeItem("user");
          if (this.$router) {
            const authRouteName = "Auth";
            const authRouteExists = this.$router.hasRoute(authRouteName);
            if (authRouteExists) {
              this.$router.push({ name: authRouteName });
            } else {
              console.warn(
                `Route '${authRouteName}' not found. Redirecting to root '/'.`
              );
              this.$router.push("/");
            }
          } else {
            console.error("Vue Router instance ($router) is not available.");
            window.location.href = "/";
          }
        } else {
          this.errorMessage = response?.error || "Logout failed on the server.";
          console.error(
            "Logout API Error:",
            response ? response.error : "Unknown error"
          );
        }
      } catch (err) {
        console.error("Logout system error:", err);
        this.errorMessage =
          "Network or system error during logout. Please try again.";
      } finally {
        this.isLoggingOut = false;
      }
    },
  },
};
</script>
<template>
  <div>
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

    <div class="sidebar d-none d-md-flex">
      <div class="image">
        <img
          src="/img/logo.png"
          alt="Company Logo"
          class="logo img-fluid rounded-4"
        />
      </div>
      <ul class="nav-list">
        <li>
          <router-link :to="{ name: 'Dashboard' }"
            ><i class="fa-solid fa-gauge-high"></i
            ><span class="nav-text">Dashboard</span></router-link
          >
        </li>
        <li>
          <router-link :to="{ name: 'Subjects' }"
            ><i class="fa-solid fa-folder"></i
            ><span class="nav-text">Subjects</span></router-link
          >
        </li>
        <li>
          <router-link :to="{ name: 'Rankings' }"
            ><i class="fa-solid fa-square-poll-vertical"></i
            ><span class="nav-text">Rankings</span></router-link
          >
        </li>
        <li>
          <router-link :to="{ name: 'MyNotes' }"
            ><i class="fa-solid fa-file"></i
            ><span class="nav-text">My Notes</span></router-link
          >
        </li>
        <li>
          <router-link :to="{ name: 'MyProfile' }">
            <i class="fa-solid fa-circle-user"></i>
            <span class="nav-text">My Profile</span>
          </router-link>
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

    <div class="bottom-nav d-md-none">
      <router-link :to="{ name: 'Dashboard' }" class="bottom-nav-item">
        <i class="fa-solid fa-gauge-high"></i>
        <span class="bottom-nav-text">Dashboard</span>
      </router-link>
      <router-link :to="{ name: 'Subjects' }" class="bottom-nav-item">
        <i class="fa-solid fa-folder"></i>
        <span class="bottom-nav-text">Subjects</span>
      </router-link>
      <router-link :to="{ name: 'Rankings' }" class="bottom-nav-item">
        <i class="fa-solid fa-square-poll-vertical"></i>
        <span class="bottom-nav-text">Rankings</span>
      </router-link>
      <router-link :to="{ name: 'MyNotes' }" class="bottom-nav-item">
        <i class="fa-solid fa-file"></i>
        <span class="bottom-nav-text">My Notes</span>
      </router-link>
      <router-link :to="{ name: 'MyProfile' }" class="bottom-nav-item">
        <i class="fa-solid fa-circle-user"></i>
        <span class="bottom-nav-text">My Profile</span>
      </router-link>
      <button
        class="bottom-nav-item logout-btn"
        @click="logoutUser"
        :disabled="isLoggingOut"
      >
        <i class="fa-solid fa-right-from-bracket"></i>
        <span class="bottom-nav-text">Logout</span>
      </button>
    </div>
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
  flex-direction: column;
  padding: 25px 0;
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
  z-index: 1000;
}
.image {
  text-align: center;
  flex-shrink: 0;
  padding: 25px 0;
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
  transition: background-color 0.2s ease-in-out, opacity 0.2s ease-in-out;
}
.nav-list li > a.router-link-exact-active {
  opacity: 1;
  background-color: rgba(255, 255, 255, 0.1);
  box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.1);
}
.nav-list li:not(:has(> a.router-link-exact-active)):hover {
  opacity: 1;
  background-color: rgba(255, 255, 255, 0.06);
  cursor: pointer;
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

.bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  background-color: #ffffff;
  border-top: 1px solid #e0e0e0;
  box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
  display: flex;
  justify-content: space-around;
  align-items: stretch;
  padding: 5px 0;
  z-index: 1000;
  height: 60px;
}
.bottom-nav-item,
.logout-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex-grow: 1;
  text-decoration: none;
  color: #6c757d;
  font-size: 0.7rem;
  text-align: center;
  padding: 4px 2px;
  border: none;
  background: none;
  cursor: pointer;
  position: relative;
}
.bottom-nav-item i {
  font-size: 1.4rem;
  margin-bottom: 3px;
}
.bottom-nav-item.router-link-exact-active {
  color: rgba(0, 74, 173, 1);
  font-weight: 500;
}
.logout-btn {
  color: #dc3545;
}
.logout-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@-webkit-keyframes spinner-border {
  to {
    transform: rotate(360deg);
  }
}
@keyframes spinner-border {
  to {
    transform: rotate(360deg);
  }
}
</style>
