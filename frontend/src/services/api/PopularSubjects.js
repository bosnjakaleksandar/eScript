import ApiService from "./ApiService";

const getPopularSubjects = async () => {
  return ApiService.get("/get-popular-subjects.php");
};

export default {
  getPopularSubjects,
};
