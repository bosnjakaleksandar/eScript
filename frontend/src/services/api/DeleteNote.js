import ApiService from "./ApiService";

const deleteNote = async (noteId) => {
  return ApiService.post("/delete-note.php", { note_id: noteId });
};

export default {
  deleteNote,
};
