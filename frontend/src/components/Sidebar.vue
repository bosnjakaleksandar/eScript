<script>
export default {
  name: "SidebarView",
  data() {
    return {
      isSidebarNarrow: false,
      isMobileExpanded: false,
      openSubmenus: [],
      subjects: [],
      subjectsByYear: [[], [], [], []],
      errorLoading: false,
    };
  },
  computed: {
    toggleIcon() {
      return this.isSidebarNarrow ? "fa-chevron-right" : "fa-chevron-left";
    },
  },
  methods: {
    toggleSidebar() {
      this.isSidebarNarrow = !this.isSidebarNarrow;
      if (window.innerWidth <= 768) {
        this.isMobileExpanded = !this.isSidebarNarrow;
        document.body.classList.toggle("sidebar-open", this.isMobileExpanded);
      }
      localStorage.setItem(
        "sidebarState",
        this.isSidebarNarrow ? "narrow" : "expanded"
      );
    },
    toggleSubmenu(event, year) {
      const submenuId = `year-${year}`;

      if (this.openSubmenus.includes(submenuId)) {
        this.openSubmenus = this.openSubmenus.filter((id) => id !== submenuId);
      } else {
        this.openSubmenus = [submenuId];
      }
    },

    async fetchSubjects() {
      try {
        const response = await fetch("http://localhost:8002/get-subjects.php", {
          method: "GET",
          headers: {
            "Content-Type": "application/json",
          },
          credentials: "include",
        });

        const data = await response.json();

        if (data.success) {
          this.groupSubjectsByYear(data.subjects);
        } else {
          console.error("Error fetching subjects:", data.error);
          this.errorLoading = true;

          if (data.debug) {
            console.log("Debug info:", data.debug);
          }
        }
      } catch (error) {
        console.error("Error:", error);
        this.errorLoading = true;
      }
    },

    groupSubjectsByYear(subjects) {
      this.subjectsByYear = [[], [], [], []];

      subjects.forEach((subject) => {
        if (subject.year >= 1 && subject.year <= 4) {
          this.subjectsByYear[subject.year - 1].push(subject);
        }
      });
    },
    restoreSidebarState() {
      const savedState = localStorage.getItem("sidebarState");
      if (savedState === "narrow") {
        this.isSidebarNarrow = true;
      }
    },
  },
  mounted() {
    this.restoreSidebarState();
    this.fetchSubjects();
  },
};
</script>

<template>
  <div
    id="sidebar-wrapper"
    :class="{
      'sidebar-narrow': isSidebarNarrow,
      'mobile-expanded': isMobileExpanded,
    }"
  >
    <div
      class="sidebar position-relative"
      id="sidebar"
      :class="{ 'sidebar-narrow': isSidebarNarrow }"
    >
      <div class="toggle-btn" @click="toggleSidebar">
        <i class="fas" :class="toggleIcon"></i>
      </div>
      <div class="sidebar-content">
        <div class="sidebar-logo">
          <router-link to="/dashboard">
            <span>eScript</span>
          </router-link>
        </div>
        <ul class="p-0">
          <li class="active mt-3">
            <router-link to="/dashboard">
              <i class="fa-solid fa-gauge-high fs-4"></i>
              <span class="nav-text">Dashboard</span>
            </router-link>
          </li>

          <!-- Error message when subjects fail to load -->
          <li v-if="errorLoading" class="text-warning">
            <a href="#" @click.prevent="fetchSubjects()">
              <i class="fa-solid fa-triangle-exclamation fs-4"></i>
              <span class="nav-text">Failed to load. Retry?</span>
            </a>
          </li>

          <!-- Dynamic Subjects Menu -->
          <li
            v-for="(yearSubjects, yearIndex) in subjectsByYear"
            :key="`year-${yearIndex + 1}`"
            class="has-submenu"
            v-show="yearSubjects.length > 0"
          >
            <a
              href="#"
              class="submenu-toggle"
              @click.prevent="toggleSubmenu($event, yearIndex + 1)"
            >
              <i class="fa-solid fa-calendar-year fs-4"></i>
              <span class="nav-text">Godina {{ yearIndex + 1 }}</span>
              <i
                class="fa-solid fa-chevron-down submenu-icon"
                :class="{
                  rotate: openSubmenus.includes(`year-${yearIndex + 1}`),
                }"
              ></i>
            </a>
            <ul
              class="submenu"
              :class="{ open: openSubmenus.includes(`year-${yearIndex + 1}`) }"
            >
              <li
                v-for="subject in yearSubjects"
                :key="`subject-${subject.id}`"
              >
                <router-link :to="`/subject/${subject.id}`">
                  <i class="fa-solid fa-book fs-4"></i>
                  <span class="nav-text">{{ subject.name }}</span>
                </router-link>
              </li>
            </ul>
          </li>

          <li>
            <router-link to="/#">
              <i class="fa-solid fa-circle-dollar-to-slot fs-4"></i>
              <span class="nav-text">Donations</span>
            </router-link>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<style scoped>
body {
  background-color: rgba(0, 74, 173, 1) !important;
  overflow-x: hidden;
}

#sidebar-wrapper {
  width: 16.67%;
  transition: all 0.3s ease;
  position: relative;
  flex-shrink: 0;
}

#sidebar-wrapper.sidebar-narrow {
  width: 70px !important;
}

.sidebar {
  background-color: rgba(0, 74, 173, 1);
  transition: all 0.3s ease;
  width: 100%;
  height: 100vh;
}

.sidebar-content li {
  list-style: none;
  padding-top: 10px;
  padding-left: 10px;
  margin-bottom: 10px;
  margin-right: 10px;
  margin-left: 10px;
  border-radius: 10px;
  transition: all 0.3s ease;
}

.active {
  list-style: none;
  background-color: white;
  padding-top: 10px;
  padding-left: 10px;
  margin-bottom: 10px;
  border-radius: 10px;
}

.active a {
  color: #ad004a !important;
  text-decoration: none;
}

.sidebar-content a {
  text-decoration: none;
  color: white;
  display: flex;
  align-items: center;
  padding-bottom: 7px;
}

.sidebar-content .nav-text {
  margin-left: 10px;
  white-space: nowrap;
}

.sidebar-narrow .sidebar-content .nav-text,
.sidebar-narrow .sidebar-logo span,
.sidebar-narrow .sidebar-content .ad-container {
  display: none;
}

.sidebar-logo {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 15px 0;
}

.sidebar-logo img {
  height: 40px;
}

.sidebar-logo span {
  color: white;
  margin-left: 10px;
  font-weight: bold;
}

.toggle-btn {
  position: absolute;
  top: 10px;
  right: -15px;
  background-color: white;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  display: flex;
  justify-content: center;
  align-items: center;
  color: rgba(0, 74, 173, 1);
  cursor: pointer;
  z-index: 999;
  border: 2px solid rgba(0, 74, 173, 1);
}

.ad-container {
  padding: 10px;
}

.sidebar-narrow .sidebar-content li {
  padding-left: 0;
  text-align: center;
}

.sidebar-narrow .sidebar-content a {
  justify-content: center;
}

.sidebar-narrow .sidebar-logo {
  justify-content: center;
}

.submenu {
  padding-left: 15px;
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease;
  padding-top: 0;
  margin-top: 0;
}

.submenu.open {
  max-height: 500px;
}

.submenu li {
  margin-left: 10px;
  padding-left: 10px;
  margin-bottom: 5px;
  padding-top: 5px;
}

.submenu li a {
  padding: 5px 0;
  font-size: 0.9em;
}

.submenu-toggle {
  width: 100%;
}

.submenu-icon {
  margin-left: 10px;
  margin-right: 10px;
  transition: transform 0.3s ease;
}

.submenu-icon.rotate {
  transform: rotate(-180deg);
}

.sidebar-narrow .submenu {
  position: absolute;
  left: 70px;
  top: 0;
  width: 200px;
  background-color: rgba(0, 74, 173, 0.95);
  border-radius: 0 10px 10px 0;
  padding: 10px;
  z-index: 1000;
  max-height: 0;
  overflow: hidden;
  display: none;
}

.sidebar-narrow .has-submenu:hover .submenu {
  max-height: 500px;
}

.sidebar-narrow .submenu-icon {
  display: none;
}

.has-submenu.active {
  background-color: white;
}

.has-submenu.active > a {
  color: #ad004a !important;
}

.has-submenu.active .submenu-icon {
  color: #ad004a;
}

.has-submenu.active .submenu li a {
  color: white;
}

.has-submenu.active .submenu {
  background-color: transparent;
}

.sidebar-narrow .submenu:not(.open) {
  display: none;
}

@media (max-width: 768px) {
  #sidebar-wrapper {
    position: fixed;
    width: 70px !important;
    z-index: 1030;
  }
  #sidebar-wrapper.mobile-expanded {
    width: 250px !important;
  }
  #main-content {
    margin-left: 70px;
    width: calc(100% - 70px);
    height: 100vh;
  }
  body.sidebar-open::before {
    content: "";
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1020;
  }
  #sidebar-wrapper.mobile-expanded .sidebar-logo span,
  #sidebar-wrapper.mobile-expanded .sidebar-content .nav-text,
  #sidebar-wrapper.mobile-expanded .sidebar-content .ad-container {
    display: block;
  }
  #sidebar-wrapper.mobile-expanded .sidebar-content li {
    padding-left: 10px;
    text-align: left;
  }
  #sidebar-wrapper.mobile-expanded .sidebar-content a {
    justify-content: flex-start;
  }
  #sidebar-wrapper.mobile-expanded .submenu {
    display: block !important;
  }
  .toggle-btn {
    right: 20px;
  }
}
</style>
