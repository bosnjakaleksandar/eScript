import ApiService from "./ApiService";

const getDashboardData = async () => {
  return ApiService.get("/get-dashboard-data.php");
};

export default {
  getDashboardData,
};
