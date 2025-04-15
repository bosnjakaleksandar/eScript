<script>
import { computed } from "vue";

export default {
  name: "ProfileHeader",
  props: {
    user: { type: Object, default: null },
  },
  emits: ["change-picture-clicked"],
  setup(props) {
    const profileName = computed(() => {
      return props.user?.username || "User";
    });

    const profileImageUrl = computed(() => {
      const defaultImage = "/img/DefaultAvatar.jpg";
      const backendBaseUrl = "http://localhost:8002";

      if (props.user && props.user.profile_image_path) {
        const imagePath = props.user.profile_image_path;

        if (imagePath.startsWith("http")) {
          return imagePath;
        } else if (imagePath.startsWith("/")) {
          return `${backendBaseUrl}${imagePath}`;
        } else {
          return `${backendBaseUrl}/${imagePath}`;
        }
      }
      return defaultImage;
    });
    return { profileName, profileImageUrl };
  },
};
</script>
<template>
  <div class="row mb-4">
    <div class="col-12">
      <div class="profile-header-content rounded-4 d-flex align-items-center">
        <div class="profile-image flex-shrink-0 me-3 me-sm-4 position-relative">
          <img
            :src="profileImageUrl"
            class="img-fluid rounded-circle profile-avatar"
            alt="User profile picture"
          />
          <button
            @click="$emit('change-picture-clicked')"
            class="change-picture-btn btn btn-sm btn-light"
            title="Change Picture"
          >
            <i class="fa-solid fa-camera"></i>
          </button>
        </div>
        <div class="profile-info flex-grow-1">
          <h1 class="profile-name mb-0">{{ profileName }}</h1>
          <p v-if="user?.email" class="profile-email mb-0">
            {{ user.email }}
          </p>
          <p v-if="user?.role" class="profile-role small mt-1">
            Trenutni nivo: {{ user.role }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
.profile-header-content {
  background: linear-gradient(to right, rgba(0, 74, 173, 1) 0%, #3a8fd5 100%);
  color: white;
  padding: 1.5rem 2rem;
  min-height: 150px;
}
.profile-image img.profile-avatar {
  width: 150px;
  height: 150px;
  object-fit: cover;
  border: 3px solid rgba(255, 255, 255, 0.8);
}
.profile-image {
  position: relative;
}
.change-picture-btn {
  position: absolute;
  bottom: 0px;
  right: 0px;
  border-radius: 50%;
  line-height: 1;
  padding: 0.3rem 0.4rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}
.change-picture-btn i {
  font-size: 0.8rem;
}
.profile-name {
  font-size: clamp(1.6rem, 2.5vw, 2rem);
  font-weight: 600;
  word-break: break-word;
}
.profile-email {
  font-size: 1rem;
  opacity: 0.9;
  word-break: break-all;
}
.profile-role {
  opacity: 0.8;
  font-style: italic;
}

@media (max-width: 576px) {
  .profile-header-content {
    padding: 1rem;
  }
  .profile-image img.profile-avatar {
    width: 100px;
    height: 100px;
  }
  .profile-info {
    text-align: left;
  }
  .profile-name {
    font-size: 1.2rem;
  }
  .profile-email {
    font-size: 0.85rem;
  }
  .profile-role {
    font-size: 0.8rem;
  }
  .change-picture-btn {
    padding: 0.2rem 0.3rem;
  }
  .change-picture-btn i {
    font-size: 0.7rem;
  }
}
</style>
