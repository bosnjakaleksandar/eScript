import ApiService from "./ApiService";

const login = async (formData) => {
  return ApiService.post("/login.php", formData);
};

const register = async (formData) => {
  return ApiService.post("/register.php", formData);
};

export default {
  login,
  register,
};
