import ApiService from "./ApiService";

const getUserNotes = async () => {
  return ApiService.get("/get-user-notes.php");
};

export default {
  getUserNotes,
};
