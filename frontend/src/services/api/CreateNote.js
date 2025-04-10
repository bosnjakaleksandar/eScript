import ApiService from "./ApiService";

const createNote = async () => {
  return ApiService.get("/create-note.php");
};

export default {
  createNote,
};
