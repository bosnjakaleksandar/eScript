<script>
import RankingService from "../services/api/Ranking";
import Sidebar from "../components/Sidebar.vue";
import RankingList from "../components/RankingList.vue";

export default {
  name: "RankingsView",
  components: {
    Sidebar,
    RankingList,
  },
  data() {
    return {
      rankings: [],
      isLoading: false,
      error: "",
    };
  },
  methods: {
    async fetchRankings() {
      this.isLoading = true;
      this.error = "";
      try {
        const response = await RankingService.getUserRankings();
        if (response && response.success) {
          this.rankings = response.rankings || [];
        } else {
          this.error = response?.error || "Failed to load rankings.";
          console.error("Error fetching rankings:", response);
        }
      } catch (err) {
        this.error = `Network error: ${
          err.message || "Could not fetch rankings."
        }`;
        console.error("Network error fetching rankings:", err);
      } finally {
        this.isLoading = false;
      }
    },
  },
  mounted() {
    this.fetchRankings();
  },
};
</script>
<template>
  <div class="app-container">
    <Sidebar />
    <div class="main-content-wrapper">
      <div class="rankings-view-container p-2">
        <h2 class="border-bottom mb-4 pb-2">User Rankings</h2>
        <p class="text-muted mb-4">
          Top 10 users ranked by number of notes created and average rating
          received.
        </p>

        <RankingList
          :rankings="rankings"
          :is-loading="isLoading"
          :error="error"
        />
      </div>
    </div>
  </div>
</template>
<style scoped>
.main-content-wrapper {
  margin-left: 19%;
  width: calc(100% - 19%);
  padding-top: 20px;
  padding-bottom: 20px;
  padding-right: 5%;
  min-height: 100vh;
}
.rankings-view-container {
  max-width: 100%;
  margin: 0 auto;
}
h2 {
  color: #343a40;
  font-weight: 600;
  padding-top: 30px;
}
@media (max-width: 768px) {
  .main-content-wrapper {
    margin-left: calc(70px + 1%);
    width: calc(100% - (70px + 1%));
    padding-left: 2%;
    padding-right: 2%;
    padding-bottom: 70px;
  }
}

@media (max-width: 576px) {
  .main-content-wrapper {
    margin-left: 0;
    width: 100%;
    padding-left: 1%;
    padding-right: 1%;
  }
}
</style>
