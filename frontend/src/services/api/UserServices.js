import ApiService from "./ApiService";

const getUserProfile = async (userId = null) => {
  const endpoint = userId
    ? `/get-user-profile.php?user_id=${userId}`
    : "/get-user-profile.php";
  return ApiService.get(endpoint);
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
