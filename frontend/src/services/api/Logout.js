import ApiService from "./ApiService";

const logout = async () => {
  return ApiService.post("/logout.php");
};

export default {
  logout,
};
