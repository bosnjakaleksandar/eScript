<script>
import UserService from "../services/api/UserServices";
import NoteService from "../services/api/UserTopNotes";
import Sidebar from "../components/Sidebar.vue";
import ProfileHeader from "../components/profile/ProfileHeader.vue";
import TopNotesList from "../components/profile/TopNotesList.vue";
import AchievementsSection from "../components/profile/AchievementsSection.vue";
import UploadProfilePictureModal from "../components/profile/UploudProfilePicture.vue";

export default {
  name: "UserProfileView",
  components: {
    Sidebar,
    ProfileHeader,
    TopNotesList,
    AchievementsSection,
    UploadProfilePictureModal,
  },
  props: {
    userId: {
      type: [String, Number],
      required: true,
    },
  },
  data() {
    return {
      userProfile: null,
      topNotes: [],
      isLoadingProfile: false,
      isLoadingNotes: false,
      errorProfile: "",
      errorNotes: "",
      showUploadModal: false,
      loggedInUserId: null,
    };
  },
  methods: {
    async fetchUserProfile(id) {
      if (!id) {
        this.errorProfile = "User ID is missing.";
        return;
      }
      this.isLoadingProfile = true;
      this.errorProfile = "";
      try {
        const response = await UserService.getUserProfile(id);
        if (response && response.success) {
          this.userProfile = response.profile;
        } else {
          this.errorProfile = response?.error || "Failed to load profile.";
          this.userProfile = null;
        }
      } catch (err) {
        this.errorProfile = `Network error: ${err.message}`;
        console.error(err);
        this.userProfile = null;
      } finally {
        this.isLoadingProfile = false;
      }
    },
    async fetchTopNotes(id) {
      if (!id) return;
      this.isLoadingNotes = true;
      this.errorNotes = "";
      try {
        const response = await NoteService.getTopRatedUserNotes(id);
        if (response && response.success) {
          this.topNotes = response.notes || [];
        } else {
          this.errorNotes = response?.error || "Failed to load top notes.";
        }
      } catch (err) {
        this.errorNotes = `Network error: ${err.message}`;
        console.error(err);
      } finally {
        this.isLoadingNotes = false;
      }
    },
    openUploadModal() {
      if (this.userProfile && this.loggedInUserId === this.userProfile.id) {
        this.showUploadModal = true;
      } else {
        alert("You can only change your own profile picture.");
      }
    },
    handlePictureUploaded(newImagePath) {
      if (this.userProfile && newImagePath) {
        this.userProfile.profile_image_path = newImagePath;
      }
      this.showUploadModal = false;
    },
    getLoggedInUserId() {
      try {
        const userData = localStorage.getItem("user");
        this.loggedInUserId = userData ? JSON.parse(userData)?.id : null;
      } catch (e) {
        console.error("Error getting user ID:", e);
        this.loggedInUserId = null;
      }
    },
    loadProfileData() {
      if (this.numericUserId) {
        this.fetchUserProfile(this.numericUserId);
        this.fetchTopNotes(this.numericUserId);
      } else {
        this.errorProfile = "Invalid User ID provided in URL.";
      }
    },
  },
  computed: {
    numericUserId() {
      return this.userId ? parseInt(this.userId, 10) : null;
    },
  },
  created() {
    this.getLoggedInUserId();
    this.loadProfileData();
  },
  watch: {
    numericUserId(newId, oldId) {
      if (newId && newId !== oldId) {
        this.loadProfileData();
      }
    },
  },
};
</script>
<template>
  <div class="app-container">
    <Sidebar />
    <div class="main-content-wrapper">
      <div class="profile-view-container">
        <div
          v-if="isLoadingProfile || (!userProfile && !errorProfile)"
          class="text-center py-5"
        >
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2 text-muted">Loading profile...</p>
        </div>
        <div v-else-if="errorProfile" class="alert alert-danger">
          {{ errorProfile }}
        </div>
        <template v-else-if="userProfile">
          <ProfileHeader
            :user="userProfile"
            @change-picture-clicked="openUploadModal"
          />
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
    <UploadProfilePictureModal
      :show="showUploadModal"
      @close="showUploadModal = false"
      @picture-uploaded="handlePictureUploaded"
    />
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
