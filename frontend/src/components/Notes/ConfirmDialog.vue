<script>
import { computed } from "vue";

export default {
  name: "ConfirmDialog",
  props: {
    show: {
      type: Boolean,
      required: true,
    },
    title: {
      type: String,
      default: "Confirm Action",
    },
    message: {
      type: String,
      required: true,
    },
    confirmButtonText: {
      type: String,
      default: "Confirm",
    },
    cancelButtonText: {
      type: String,
      default: "Cancel",
    },
    confirmButtonType: {
      type: String,
      default: "danger",
      validator: (value) =>
        [
          "primary",
          "secondary",
          "success",
          "danger",
          "warning",
          "info",
          "light",
          "dark",
        ].includes(value),
    },
  },
  emits: ["confirm", "cancel"],
  setup(props, { emit }) {
    const dialogTitleId = computed(
      () => `dialog-title-${Math.random().toString(36).substring(2, 9)}`
    );
    const dialogDescId = computed(
      () => `dialog-desc-${Math.random().toString(36).substring(2, 9)}`
    );
    const confirmButtonClass = computed(() => {
      return `btn btn-${props.confirmButtonType}`;
    });
    const confirmAction = () => {
      emit("confirm");
    };
    const cancelAction = () => {
      emit("cancel");
    };
    return {
      confirmAction,
      cancelAction,
      confirmButtonClass,
      dialogTitleId,
      dialogDescId,
    };
  },
};
</script>
<template>
  <transition name="modal-fade">
    <div class="modal-overlay" v-if="show" @click.self="cancelAction">
      <div
        class="modal-content confirmation-dialog rounded-3"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="dialogTitleId"
        :aria-describedby="dialogDescId"
      >
        <div class="confirmation-content">
          <h5
            v-if="title"
            class="modal-title text-center text-danger"
            :id="dialogTitleId"
          >
            {{ title }}
          </h5>
          <p
            class="confirmation-message mt-3 mb-4 text-center"
            :id="dialogDescId"
          >
            {{ message }}
          </p>

          <div class="confirmation-actions d-flex justify-content-center gap-2">
            <button
              type="button"
              class="btn btn-secondary"
              @click="cancelAction"
            >
              {{ cancelButtonText }}
            </button>
            <button
              type="button"
              :class="confirmButtonClass"
              @click="confirmAction"
            >
              {{ confirmButtonText }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>
<style scoped>
.modal-overlay {
  z-index: 1060;
}
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
}
.modal-content.confirmation-dialog {
  max-width: 450px;
  padding: 20px 25px 25px 25px;
  background-color: #fff;
}
.confirmation-content .modal-title {
  font-weight: 500;
  color: #343a40;
}
.confirmation-message {
  font-size: 1rem;
  color: #495057;
}
.confirmation-actions .btn {
  min-width: 90px;
}
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.25s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
</style>
