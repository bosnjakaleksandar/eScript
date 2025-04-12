import ApiService from "./ApiService";

const getLastNote = async () => {
  return ApiService.get("/get-last-note.php");
};

export default {
  getLastNote,
};
