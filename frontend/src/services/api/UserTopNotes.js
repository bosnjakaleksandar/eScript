import ApiService from "./ApiService";

const getTopRatedUserNotes = async () => {
  return ApiService.get("/get-user-top-notes.php");
};

export default {
  getTopRatedUserNotes,
};
