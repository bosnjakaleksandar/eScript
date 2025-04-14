<script>
import { ref, watch, computed } from "vue";

export default {
  name: "StarRating",
  props: {
    modelValue: { type: Number, default: 0 },
    maxRating: { type: Number, default: 5 },
    disabled: { type: Boolean, default: false },
    showClearButton: { type: Boolean, default: false },
  },

  emits: ["rating-selected"],
  setup(props, { emit }) {
    const displayedValue = ref(props.modelValue || 0);
    const hoverRating = ref(0);

    watch(
      () => props.modelValue,
      (newValue) => {
        displayedValue.value = newValue || 0;
      }
    );

    const displayedRatingRounded = computed(() => {
      return Math.round(displayedValue.value * 2) / 2;
    });

    const setHoverRating = (rating) => {
      if (props.disabled) return;
      hoverRating.value = rating;
    };

    const resetHoverRating = () => {
      if (props.disabled) return;
      hoverRating.value = 0;
    };

    const selectRating = (rating) => {
      if (props.disabled) return;
      emit("rating-selected", rating);
    };

    const clearRating = () => {
      if (props.disabled) return;
      emit("rating-selected", 0);
    };

    return {
      displayedValue,
      hoverRating,
      setHoverRating,
      resetHoverRating,
      selectRating,
      clearRating,
      displayedRatingRounded,
    };
  },
};
</script>
<template>
  <div class="star-rating" :class="{ 'is-disabled': disabled }">
    <span
      v-for="star in maxRating"
      :key="star"
      class="star"
      :class="{
        filled:
          star <= displayedRatingRounded ||
          star - 0.5 === displayedRatingRounded,
        half: star - 0.5 === displayedRatingRounded,
        hover: star <= hoverRating && !disabled,
      }"
      @click="selectRating(star)"
      @mouseover="setHoverRating(star)"
      @mouseleave="resetHoverRating"
      role="radio"
      :aria-checked="Math.round(displayedValue) === star"
      :aria-label="`${star} star${star > 1 ? 's' : ''}`"
      :tabindex="disabled ? -1 : 0"
      @keydown.enter.prevent="selectRating(star)"
      @keydown.space.prevent="selectRating(star)"
      :style="{ cursor: disabled ? 'default' : 'pointer' }"
    >
      <i
        v-if="
          star <= displayedRatingRounded ||
          star - 0.5 === displayedRatingRounded
        "
        :class="[
          'fas',
          star - 0.5 === displayedRatingRounded
            ? 'fa-star-half-alt'
            : 'fa-star',
        ]"
      ></i>
      <i v-else class="far fa-star"></i>
    </span>
    <button
      v-if="showClearButton && displayedValue > 0 && !disabled"
      @click.stop="clearRating"
      class="clear-rating-btn"
      title="Clear rating (set to 0)"
    >
      <i class="fas fa-times-circle"></i>
    </button>
  </div>
</template>
<style scoped>
.star-rating {
  display: inline-flex;
  align-items: center;
  gap: 2px;
}
.star {
  color: #ffc107;
  font-size: 1.3rem;
  transition: transform 0.1s ease-in-out;
}
.star i.far {
  color: #e0e0e0;
}
.star.hover i.fas {
  transform: scale(1.15);
}
.star-rating.is-disabled .star {
  cursor: default;
}
.star-rating.is-disabled {
  opacity: 0.6;
}
.clear-rating-btn {
  background: none;
  border: none;
  padding: 0 0 0 5px;
  margin: 0;
  cursor: pointer;
  color: #adb5bd;
  font-size: 0.9em;
  line-height: 1;
}
.clear-rating-btn:hover {
  color: #dc3545;
}
</style>
