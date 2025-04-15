<script>
import StarRating from "./StarRating.vue";

export default {
  name: "RankingList",
  components: {
    StarRating,
  },
  props: {
    rankings: { type: Array, required: true, default: () => [] },
    isLoading: { type: Boolean, default: false },
    error: { type: String, default: "" },
  },
};
</script>
<template>
  <div class="ranking-list">
    <div v-if="isLoading" class="loading-message text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2 text-muted">Loading rankings...</p>
    </div>
    <div v-else-if="error" class="alert alert-danger mt-3" role="alert">
      {{ error }}
    </div>
    <div
      v-else-if="!rankings || rankings.length === 0"
      class="alert alert-info mt-3"
      role="alert"
    >
      No ranking data available yet.
    </div>
    <div v-else class="table-responsive">
      <table class="table table-hover align-middle ranking-table">
        <thead>
          <tr>
            <th scope="col" class="text-center">Rank</th>
            <th scope="col">User</th>
            <th scope="col" class="text-center">Notes Created</th>
            <th scope="col" class="text-center">Average Rating Received</th>
            <th scope="col" class="text-center">Total Ratings Received</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(user, index) in rankings" :key="user.user_id">
            <td class="text-center fw-bold rank-cell">#{{ index + 1 }}</td>
            <td>
              <router-link
                :to="{ name: 'UserProfile', params: { userId: user.user_id } }"
              >
                <i class="fa-solid fa-user me-2"></i>
                {{ user.author_name }}
              </router-link>
            </td>
            <td class="text-center">{{ user.note_count }}</td>
            <td class="text-center">
              <StarRating
                v-if="user.note_count > 0"
                :modelValue="parseFloat(user.overall_average_grade || 0)"
                :read-only="true"
                :maxRating="5"
              />
              <span v-else class="text-muted small">-</span>
            </td>
            <td class="text-center">{{ user.total_ratings_received }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
<style scoped>
a {
  text-decoration: none;
  color: #6c757d;
}
.ranking-table th {
  font-weight: 600;
  white-space: nowrap;
  background-color: #f8f9fa;
}
.ranking-table td {
  vertical-align: middle;
}
.rank-cell {
  font-size: 1.1em;
  min-width: 60px;
}
.ranking-table :deep(.star-rating) {
  font-size: 1rem;
  justify-content: center;
  vertical-align: middle;
}
.ranking-table :deep(.clear-rating-btn) {
  display: none;
}
.loading-message {
  padding: 40px 15px;
}
</style>
