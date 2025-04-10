import ApiService from "./ApiService";

const createNote = async (noteData) => {
  return ApiService.post("/create-note.php", noteData);
};

export default {
  createNote,
};
