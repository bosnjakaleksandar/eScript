import ApiService from "./ApiService";

const getTopRatedUserNotes = async (userId = null) => {
  const endpoint = userId
    ? `/get-user-top-notes.php?user_id=${userId}`
    : "/get-user-top-notes.php";
  return ApiService.get(endpoint);
};

export default {
  getTopRatedUserNotes,
};
