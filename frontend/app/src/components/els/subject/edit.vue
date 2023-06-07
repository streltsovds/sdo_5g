<template>
  <div class="subject-edit"><slot></slot></div>
</template>
<script>
import { mapActions } from "vuex";
export default {
  props: {
    scaleContinuous: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      isShownDateWarning: false,
      beginElValue: null,
      endElValue: null
    };
  },
  computed: {
    scaleId() {
      return this.$store.state.subjects.scale_id;
    },
    autoMark() {
      return this.$store.state.subjects.auto_mark;
    },
    period() {
      return this.$store.state.subjects.period;
    },
    begin() {
      return this.$store.state.subjects.begin;
    },
    end() {
      return this.$store.state.subjects.end;
    }
  },
  watch: {
    begin(newValue) {
      if (!this.beginElValue) return (this.beginElValue = newValue);
      if (this.beginElValue !== newValue) this.showDateWarning();
    },
    end(newValue) {
      if (!this.endElValue) return (this.endElValue = newValue);
      if (this.endElValue !== newValue) this.showDateWarning();
    },
    scaleId(v) {
      this.set_auto_mark({ auto_mark: false });
      this.resetFormula(v);
      // this.resetThreshold(v);
    },
    autoMark(v) {
      this.resetFormula(v);
      // this.resetThreshold(v);

      if (!v) return;

      let showElement =
        this.scaleContinuous === +this.scaleId
          ? ".hm-form-element_formula_id"
          : ".hm-form-element_threshold";
      this.show(showElement);
    }
  },
  mounted() {
    this.reset();
  },
  methods: {
    ...mapActions("subjects", [
      "set_auto_mark",
      "set_formula_id",
      "set_threshold",
      "set_begin",
      "set_end",
      "reset"
    ]),
    resetFormula(v) {
      if(parseInt(v) > 1) {
          this.set_formula_id({ formula_id: null });
          this.hide(".hm-form-element_formula_id");
      }
      else
        this.show(".hm-form-element_formula_id");
    },
    resetThreshold(v) {
      this.hide(".hm-form-element_threshold");
      this.set_threshold({ threshold: null });
    },
    hide(selector) {
      let element = this.$el.querySelector(selector);
      if (element) element.classList.add("hide");
    },
    show(selector) {
      let element = this.$el.querySelector(selector);
      if (element) element.classList.remove("hide");
    },
    showDateWarning() {
      if (this.isShownDateWarning) return;

      this.$confirmModal({
        text:
          "При изменении времени обучения автоматически изменятся все даты занятий, которые вышли за окончание курса. Продолжить?"
      })
        .then(() => (this.isShownDateWarning = true))
        .catch(() => {
          this.set_begin({ begin: this.beginElValue });
          this.set_end({ end: this.endElValue });
        });
    }
  }
};
</script>
