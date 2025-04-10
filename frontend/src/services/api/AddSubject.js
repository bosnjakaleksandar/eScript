import ApiService from "./ApiService";

const addSubject = async (subjectData) => {
  return ApiService.post("/create-subject.php", subjectData);
};

export default {
  addSubject,
};
