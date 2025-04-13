import ApiService from "./ApiService";

const getNotesBySubject = async (subjectId) => {
  return ApiService.get(`/get-notes-by-subject.php?subject_id=${subjectId}`);
};

export default {
  getNotesBySubject,
};
