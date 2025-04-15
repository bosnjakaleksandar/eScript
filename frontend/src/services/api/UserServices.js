import ApiService from "./ApiService";

const getUserProfile = async () => {
  return ApiService.get("/get-user-profile.php");
};

export default {
  getUserProfile,
};
