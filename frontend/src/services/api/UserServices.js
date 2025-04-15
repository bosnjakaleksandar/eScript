import ApiService from "./ApiService";

const getUserProfile = async () => {
  return ApiService.get("/get-user-profile.php");
};

const uploadProfilePicture = async (formData) => {
  if (!(formData instanceof FormData)) {
    console.error("Data for uploadProfilePicture must be FormData.");
    return Promise.reject(new Error("Invalid data type for upload."));
  }
  return ApiService.upload("/uploud-profile-picture.php", formData);
};

export default {
  getUserProfile,
  uploadProfilePicture,
};
