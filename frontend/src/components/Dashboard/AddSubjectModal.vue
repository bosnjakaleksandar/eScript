<script>
import addSubjectService from "../../services/api/AddSubject";

export default {
  name: "AddSubjectModal",
  props: {
    show: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["close", "subject-added"],
  data() {
    return {
      newSubjectName: "",
      selectedYear: null,
      modalErrorMessage: "",
      modalSuccessMessage: "",
      formSubmitted: false,
      isLoading: false,
    };
  },
  methods: {
    async submitForm() {
      this.formSubmitted = true;
      if (!this.newSubjectName || !this.selectedYear) {
        this.modalErrorMessage = "Please fill in all required fields";
        this.modalSuccessMessage = "";
        return;
      }
      try {
        this.isLoading = true;
        this.modalErrorMessage = "";
        this.modalSuccessMessage = "";

        const response = await addSubjectService.addSubject({
          name: this.newSubjectName,
          year: this.selectedYear,
        });

        if (response && response.success) {
          this.modalSuccessMessage = "Subject added successfully!";
          this.$emit(
            "subject-added",
            response.subject || {
              name: this.newSubjectName,
              year: this.selectedYear,
            }
          );

          setTimeout(() => {
            this.closeModal(true);
          }, 1500);
        } else {
          this.modalErrorMessage = response?.error
            ? response.error
            : "Failed to add subject.";
          if (response?.debug) {
            console.log("Debug info:", response.debug);
          }
        }
      } catch (err) {
        console.error("Submit error in addSubject modal:", err);
        this.modalErrorMessage = `Network or processing error: ${err.message}`;
      } finally {
        this.isLoading = false;
      }
    },
    resetModalFormFields() {
      this.newSubjectName = "";
      this.selectedYear = null;
      this.modalErrorMessage = "";
      this.modalSuccessMessage = "";
      this.formSubmitted = false;
    },
    closeModal(submitted = false) {
      if (!submitted) {
        this.resetModalFormFields();
      }
      this.$emit("close");
    },
  },
  watch: {
    show(newValue, oldValue) {
      if (!newValue && oldValue && !this.modalSuccessMessage) {
        this.resetModalFormFields();
      }
      if (newValue && this.modalSuccessMessage) {
        this.modalSuccessMessage = "";
      }
    },
  },
};
</script>
<template>
  <transition name="modal-fade">
    <div class="modal-overlay" v-if="show" @click.self="closeModal">
      <div
        class="modal-content"
        role="dialog"
        aria-modal="true"
        aria-labelledby="addSubjectModalTitle"
      >
        <button
          class="modal-close-btn"
          @click="closeModal"
          aria-label="Close modal"
        >
          &times;
        </button>

        <div class="add-new-subject-modal-content">
          <h5 class="mb-3 modal-title" id="addSubjectModalTitle">
            Add New Subject
          </h5>

          <div
            v-if="modalErrorMessage"
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
          >
            {{ modalErrorMessage }}
            <button
              type="button"
              class="btn-close"
              @click="modalErrorMessage = ''"
              aria-label="Close error message"
            ></button>
          </div>
          <div
            v-if="modalSuccessMessage"
            class="alert alert-success"
            role="status"
          >
            {{ modalSuccessMessage }}
          </div>

          <form @submit.prevent="submitForm" novalidate>
            <div class="mb-3">
              <label for="modalSubjectNameInput" class="form-label"
                >Subject Name:</label
              >
              <input
                id="modalSubjectNameInput"
                v-model.trim="newSubjectName"
                type="text"
                class="form-control"
                placeholder="Enter subject name"
                required
                aria-required="true"
              />
            </div>
            <div class="mb-3">
              <label class="form-label">Select Year:</label>
              <div class="checks modal-checks mb-3 border rounded p-2 bg-light">
                <div
                  v-for="year in [1, 2, 3, 4]"
                  :key="year"
                  class="form-check"
                >
                  <input
                    type="radio"
                    class="form-check-input"
                    :id="'addSubjectModalYear' + year"
                    :value="year"
                    v-model="selectedYear"
                    name="modalSubjectYear"
                    required
                    aria-required="true"
                  />
                  <label
                    class="form-check-label"
                    :for="'addSubjectModalYear' + year"
                  >
                    Year {{ year }}
                  </label>
                </div>
              </div>
              <div
                v-if="!selectedYear && formSubmitted"
                class="text-danger small mt-1"
              >
                Please select a year.
              </div>
            </div>

            <div class="modal-actions text-end">
              <button
                type="button"
                class="btn btn-secondary me-2"
                @click="closeModal"
              >
                Cancel
              </button>
              <button
                class="btn btn-success"
                type="submit"
                :disabled="isLoading"
              >
                <span
                  v-if="isLoading"
                  class="spinner-border spinner-border-sm"
                  role="status"
                  aria-hidden="true"
                ></span>
                {{ isLoading ? " Adding..." : "Add Subject" }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </transition>
</template>
<style scoped>
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
  z-index: 1050;
}
.modal-content {
  background-color: #fff;
  padding: 25px 30px 30px 30px;
  border-radius: 0.5rem;
  box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.2);
  position: relative;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}
.modal-close-btn {
  position: absolute;
  top: 12px;
  right: 15px;
  background: none;
  border: none;
  font-size: 2rem;
  font-weight: bold;
  color: #adb5bd;
  cursor: pointer;
  line-height: 1;
  padding: 0;
}
.modal-close-btn:hover {
  color: #495057;
}
.add-new-subject-modal-content .modal-title {
  margin-bottom: 1.5rem !important;
  font-weight: 500;
  color: #343a40;
  text-align: center;
}
.modal-content .form-label {
  font-weight: 500;
  margin-bottom: 0.3rem;
  font-size: 0.9rem;
}
.modal-content .form-control,
.modal-content .form-check-input {
  font-size: 0.95rem;
}
.modal-content .form-check-label {
  font-size: 0.9rem;
}
.modal-checks {
  border: 1px solid #dee2e6 !important;
  padding: 10px 15px !important;
  margin-bottom: 1rem !important;
  background-color: #f8f9fa !important;
  border-radius: 0.25rem !important;
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}
.modal-checks .form-check {
  margin-bottom: 0;
}
.modal-actions {
  margin-top: 1.5rem;
  padding-top: 1rem;
  border-top: 1px solid #dee2e6;
}
.modal-actions .btn {
  font-size: 0.9rem;
  padding: 0.4rem 1rem;
}
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
.alert-dismissible {
  padding-right: 3rem;
}
.alert-dismissible .btn-close {
  position: absolute;
  top: 0;
  right: 0;
  z-index: 2;
  padding: 1.25rem 1rem;
}
.btn-close {
  box-sizing: content-box;
  width: 1em;
  height: 1em;
  padding: 0.25em 0.25em;
  color: #000;
  background: transparent
    url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg%3e")
    center/1em auto no-repeat;
  border: 0;
  border-radius: 0.25rem;
  opacity: 0.5;
}
.btn-close:hover {
  opacity: 0.75;
}
.spinner-border-sm {
  display: inline-block;
  width: 1rem;
  height: 1rem;
  vertical-align: -0.125em;
  border: 0.15em solid currentColor;
  border-right-color: transparent;
  border-radius: 50%;
  animation: 0.75s linear infinite spinner-border;
}
@keyframes spinner-border {
  to {
    transform: rotate(360deg);
  }
}
</style>
