import ApiService from "./ApiService";

const getLoggedUser = async () => {
  return ApiService.get("/session.php?action=getLoggedUser");
};

export default {
  getLoggedUser,
};