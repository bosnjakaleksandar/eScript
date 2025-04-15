<script>
import { computed } from "vue";

export default {
  name: "AchievementsSection",
  props: {
    noteCount: {
      type: Number,
      default: null,
    },
  },
  setup(props) {
    const achievementsList = [
      { threshold: 10, name: "Note Starter", icon: "fa-solid fa-pencil" },
      { threshold: 50, name: "Note Taker", icon: "fa-solid fa-file-lines" },
      { threshold: 100, name: "Note Scribe", icon: "fa-solid fa-book-open" },
      {
        threshold: 250,
        name: "Note Master",
        icon: "fa-solid fa-graduation-cap",
      },
      {
        threshold: 500,
        name: "Note Guru",
        icon: "fa-solid fa-feather-pointed",
      },
      { threshold: 1000, name: "Note Legend", icon: "fa-solid fa-crown" },
    ];

    const processedAchievements = computed(() => {
      if (props.noteCount === null) return [];
      return achievementsList.map((ach) => ({
        ...ach,
        unlocked: props.noteCount >= ach.threshold,
      }));
    });

    return { processedAchievements };
  },
};
</script>
<template>
  <div class="achievements-section mt-4">
    <h3 class="mb-3">Achievements</h3>
    <div v-if="noteCount === null" class="text-muted small">
      Loading note count...
    </div>
    <div
      v-else-if="processedAchievements.length === 0"
      class="alert alert-light text-center"
    >
      No achievements unlocked yet. Keep writing!
    </div>
    <div v-else class="achievements-grid">
      <div
        v-for="ach in processedAchievements"
        :key="ach.name"
        class="achievement-card"
        :class="{ unlocked: ach.unlocked }"
        :title="
          ach.unlocked
            ? `${ach.name} - Unlocked!`
            : `${ach.name} - Write ${ach.threshold} notes`
        "
      >
        <i :class="['achievement-icon', ach.icon]"></i>
        <span class="achievement-name">{{ ach.name }}</span>
        <span v-if="!ach.unlocked" class="achievement-progress text-muted"
          >({{ noteCount }}/{{ ach.threshold }})</span
        >
      </div>
    </div>
  </div>
</template>
<style scoped>
.achievements-section h3 {
  font-weight: 600;
  color: #343a40;
}
.achievements-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 15px;
}
.achievement-card {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 15px;
  text-align: center;
  background-color: #f8f9fa;
  opacity: 0.6;
  transition: all 0.3s ease;
}
.achievement-card.unlocked {
  border-color: #198754;
  background-color: #d1e7dd;
  opacity: 1;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
}
.achievement-icon {
  font-size: 2.5rem;
  display: block;
  margin-bottom: 10px;
  color: #6c757d;
}
.achievement-card.unlocked .achievement-icon {
  color: #198754;
}
.achievement-name {
  display: block;
  font-size: 0.9rem;
  font-weight: 500;
  margin-bottom: 3px;
}
.achievement-progress {
  display: block;
  font-size: 0.75rem;
}
</style>
