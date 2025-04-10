import ApiService from "./ApiService";

const addSubject = async () => {
  return ApiService.get("/create-subject.php");
};

export default {
  addSubject,
};
