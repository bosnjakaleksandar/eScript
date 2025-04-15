<script>
import UserService from "../../services/api/UserServices";

export default {
  name: "UploadProfilePictureModal",
  props: {
    show: { type: Boolean, default: false },
  },
  emits: ["close", "picture-uploaded"],
  data() {
    return {
      selectedFile: null,
      previewUrl: null,
      isLoading: false,
      errorMessage: "",
    };
  },
  methods: {
    handleFileChange(event) {
      const file = event.target.files ? event.target.files[0] : null;
      this.errorMessage = "";

      if (!file) {
        this.selectedFile = null;
        this.previewUrl = null;
        return;
      }

      const allowedTypes = ["image/png", "image/jpeg", "image/gif"];
      const maxSize = 5 * 1024 * 1024;
      if (!allowedTypes.includes(file.type)) {
        this.errorMessage =
          "Invalid file type. Please select a PNG, JPG, or GIF image.";
        this.selectedFile = null;
        this.previewUrl = null;
        return;
      }
      if (file.size > maxSize) {
        this.errorMessage = "File is too large. Maximum size is 5MB.";
        this.selectedFile = null;
        this.previewUrl = null;
        return;
      }

      this.selectedFile = file;

      if (this.previewUrl) {
        URL.revokeObjectURL(this.previewUrl);
      }
      this.previewUrl = URL.createObjectURL(file);
    },

    async uploadImage() {
      if (!this.selectedFile) return;

      this.isLoading = true;
      this.errorMessage = "";

      const formData = new FormData();
      formData.append("profile_picture", this.selectedFile);

      try {
        const response = await UserService.uploadProfilePicture(formData);

        if (response && response.success) {
          this.$emit("picture-uploaded", response.new_path);
          this.closeModal();
        } else {
          this.errorMessage = response?.error || "Failed to upload picture.";
        }
      } catch (err) {
        this.errorMessage = err.message || "Network error during upload.";
        console.error("Upload error:", err);
      } finally {
        this.isLoading = false;
      }
    },

    closeModal() {
      this.resetState();
      this.$emit("close");
    },

    resetState() {
      this.selectedFile = null;
      if (this.previewUrl) {
        URL.revokeObjectURL(this.previewUrl);
      }
      this.previewUrl = null;
      this.isLoading = false;
      this.errorMessage = "";
      const fileInput = document.getElementById("profilePictureInput");
      if (fileInput) fileInput.value = "";
    },
  },
  watch: {
    show(newValue) {
      if (!newValue) {
        this.resetState();
      }
    },
  },
};
</script>
<template>
  <transition name="modal-fade">
    <div class="modal-overlay" v-if="show" @click.self="closeModal">
      <div
        class="modal-content upload-modal rounded-3 bg-white"
        role="dialog"
        aria-modal="true"
        aria-labelledby="uploadModalTitle"
      >
        <button
          class="modal-close-btn"
          @click="closeModal"
          aria-label="Close modal"
        >
          &times;
        </button>
        <div class="upload-modal-content">
          <h5 class="modal-title text-center mb-4" id="uploadModalTitle">
            Upload New Profile Picture
          </h5>

          <div
            v-if="errorMessage"
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
          >
            {{ errorMessage }}
            <button
              type="button"
              class="btn-close"
              @click="errorMessage = ''"
              aria-label="Close error message"
            ></button>
          </div>

          <div class="mb-3 text-center">
            <label
              for="profilePictureInput"
              class="form-label btn btn-outline-primary"
            >
              <i class="fa-solid fa-camera me-2"></i> Choose Image
            </label>
            <input
              type="file"
              class="form-control d-none"
              id="profilePictureInput"
              accept="image/png, image/jpeg, image/gif"
              @change="handleFileChange"
            />
            <p v-if="selectedFile" class="mt-2 mb-0 small text-muted">
              Selected: {{ selectedFile.name }}
            </p>
          </div>

          <div v-if="previewUrl" class="preview-area my-3 text-center">
            <p class="small mb-2">Preview:</p>
            <img
              :src="previewUrl"
              alt="Selected image preview"
              class="img-thumbnail preview-image"
            />
          </div>

          <div class="modal-actions text-end mt-4 pt-3">
            <button
              type="button"
              class="btn btn-secondary me-2"
              @click="closeModal"
            >
              Cancel
            </button>
            <button
              type="button"
              class="btn btn-success"
              @click="uploadImage"
              :disabled="!selectedFile || isLoading"
            >
              <span
                v-if="isLoading"
                class="spinner-border spinner-border-sm"
                role="status"
                aria-hidden="true"
              ></span>
              {{ isLoading ? " Uploading..." : "Upload & Save" }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>
<style scoped>
.modal-overlay {
  z-index: 1070;
}
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
}
.modal-content.upload-modal {
  max-width: 500px;
  padding: 20px 25px 25px 25px;
}
.modal-close-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: none;
  border: none;
  font-size: 1.8rem;
  font-weight: bold;
  color: #adb5bd;
  cursor: pointer;
  line-height: 1;
  padding: 0;
}
.modal-close-btn:hover {
  color: #495057;
}
.upload-modal-content .modal-title {
  font-weight: 500;
  color: #343a40;
}
.preview-area {
  border: 1px dashed #ccc;
  padding: 10px;
  border-radius: 5px;
}
.preview-image {
  max-width: 100%;
  max-height: 200px;
  display: block;
  margin: 0 auto;
}
.modal-actions {
  border-top: 1px solid #dee2e6;
}
input[type="file"].d-none {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}
.form-label.btn {
  cursor: pointer;
  display: inline-block;
}
</style>
