import ApiService from "./ApiService";

const getUserRankings = async () => {
  return ApiService.get("/get-user-rankings.php");
};

export default {
  getUserRankings,
};
