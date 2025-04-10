import ApiService from "./ApiService";

const getSubjects = async () => {
  return ApiService.get("/get-subjects.php");
};

export default {
  getSubjects,
};
