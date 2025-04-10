import ApiService from "./ApiService";

const addSubject = async () => {
  return ApiService.post("/create-subject.php");
};

export default {
  addSubject,
};
