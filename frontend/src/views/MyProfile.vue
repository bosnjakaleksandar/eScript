<script>
import UserService from "../services/api/UserServices";
import NoteService from "../services/api/UserTopNotes";
import Sidebar from "../components/Sidebar.vue";
import ProfileHeader from "../components/profile/ProfileHeader.vue";
import TopNotesList from "../components/profile/TopNotesList.vue";
import AchievementsSection from "../components/profile/AchievementsSection.vue";

export default {
  name: "MyProfileView",
  components: {
    Sidebar,
    ProfileHeader,
    TopNotesList,
    AchievementsSection,
  },
  data() {
    return {
      userProfile: null,
      topNotes: [],
      isLoadingProfile: false,
      isLoadingNotes: false,
      errorProfile: "",
      errorNotes: "",
    };
  },
  methods: {
    async fetchUserProfile() {
      this.isLoadingProfile = true;
      this.errorProfile = "";
      try {
        const response = await UserService.getUserProfile();
        if (response && response.success) {
          this.userProfile = response.profile;
        } else {
          this.errorProfile = response?.error || "Failed to load profile.";
        }
      } catch (err) {
        this.errorProfile = `Network error loading profile: ${err.message}`;
        console.error(err);
      } finally {
        this.isLoadingProfile = false;
      }
    },
    async fetchTopNotes() {
      this.isLoadingNotes = true;
      this.errorNotes = "";
      try {
        const response = await NoteService.getTopRatedUserNotes();
        if (response && response.success) {
          this.topNotes = response.notes || [];
        } else {
          this.errorNotes = response?.error || "Failed to load top notes.";
        }
      } catch (err) {
        this.errorNotes = `Network error loading top notes: ${err.message}`;
        console.error(err);
      } finally {
        this.isLoadingNotes = false;
      }
    },
  },
  mounted() {
    this.fetchUserProfile();
    this.fetchTopNotes();
  },
};
</script>
<template>
  <div class="app-container">
    <Sidebar />
    <div class="main-content-wrapper">
      <div class="profile-view-container">
        <div v-if="isLoadingProfile" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2 text-muted">Loading profile...</p>
        </div>
        <div v-else-if="errorProfile" class="alert alert-danger">
          {{ errorProfile }}
        </div>
        <template v-else-if="userProfile">
          <ProfileHeader :user="userProfile" />

          <TopNotesList
            :notes="topNotes"
            :is-loading="isLoadingNotes"
            :error="errorNotes"
          />

          <AchievementsSection :note-count="userProfile.note_count" />
        </template>
        <div v-else class="alert alert-warning">
          Could not load user profile.
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
.main-content-wrapper {
  margin-left: 19%;
  width: calc(100% - 19%);
  padding-top: 20px;
  padding-bottom: 20px;
  padding-right: 5%;
  min-height: 100vh;
}
.profile-view-container {
  max-width: 100%;
  margin: 0 auto;
  padding: 15px;
}
@media (max-width: 768px) {
  .main-content-wrapper {
    margin-left: calc(70px + 1%);
    width: calc(100% - (70px + 1%));
    padding-left: 2%;
    padding-right: 2%;
    padding-bottom: 70px;
  }
}

@media (max-width: 576px) {
  .main-content-wrapper {
    margin-left: 0;
    width: 100%;
    padding-left: 1%;
    padding-right: 1%;
  }
}
</style>
