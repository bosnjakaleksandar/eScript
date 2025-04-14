import ApiService from "./ApiService";

const rateNote = async (noteId, grade) => {
  if (
    noteId === null ||
    noteId === undefined ||
    grade === null ||
    grade === undefined ||
    grade < 0 ||
    grade > 5
  ) {
    console.error("Invalid arguments provided to rateNote service:", {
      noteId,
      grade,
    });
    return Promise.reject(
      new Error("Invalid input for rating (noteId or grade missing/invalid).")
    );
  }
  return ApiService.post("/rate-note.php", { note_id: noteId, grade: grade });
};

export default {
  rateNote,
};
