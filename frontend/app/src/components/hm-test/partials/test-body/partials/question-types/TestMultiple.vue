<template>
  <v-card-text class="pb-2 pt-2">
    <div style="margin-left: 9px;" v-for="(answer, i) in answers" :key="answer.question_variant_id">
      <v-checkbox
        hide-details
        :value="selected[i]"
        :class="
          (highlightAnswers && result.indexOf(answer.question_variant_id) !== -1)
            ? answer.is_correct
                ? 'success elevation-1 v-radio--round-border'
                : 'error elevation-1 v-radio--round-border'
            : null
        "
        :color="
          (highlightAnswers && result.indexOf(answer.question_variant_id) !== -1)
            ? 'white'
            : 'primary'
        "
        :dark="
          (highlightAnswers && result.indexOf(answer.question_variant_id) !== -1)
            ? true
            : false
        "
        @change="val => handleChange(i, val)"
      >
        <!-- eslint-disable -->
        <div slot="label" v-if="variantsAsHtml" class="checkbox__label variant-with-html-markup" v-html="answer.variant" />
        <!-- eslint-enable -->
        <div v-else slot="label" class="checkbox__label">
          {{ answer.variant }}
        </div>
      </v-checkbox>
      <!-- <v-divider v-if="i !== answers.length - 1" class="mb-2"></v-divider> -->
    </div>
  </v-card-text>
</template>

<script>
export default {
  props: ["answers", "variantsAsHtml", "selectedAnswer", "highlight"], // eslint-disable-line
  data() {
    return {
      selected: [],
      result: []
    };
  },
  computed: {
    highlightAnswers() {
      return this.highlight;
    },
    selectedOption() {
      if (this.selectedAnswer && !this.selected) {
        return this.selectedAnswer;
      } else if (this.selected) {
        return this.selected;
      } else {
        return null;
      }
    }
  },
  watch: {
    result() {
      this.sendAnswer();
    },
    selectedAnswer(v) {
      if (v && v.length !== this.result.length) this.init();
    }
  },
  methods: {
    init() {
      this.answers.forEach(answer => {
        let isSelected = false;
        if (
          Array.isArray(this.selectedAnswer) &&
          this.selectedAnswer.includes(answer.question_variant_id)
        ) {
          isSelected = true;
          this.result.push(answer.question_variant_id);
        }
        this.selected.push(isSelected);
      });
    },
    handleChange(i, value) {
      this.selected[i] = value;
      let variant_id = this.answers[i].question_variant_id;
      if (value) {
        this.addVariantToResult(variant_id);
      } else {
        this.removeVariantFromResult(variant_id);
      }
    },
    addVariantToResult(variant_id) {
      this.result.push(variant_id);
    },
    removeVariantFromResult(variant_id) {
      let key = this.result.findIndex(item => item === variant_id);
      if (key === -1) return;

      this.result.splice(key, 1);
    },
    sendAnswer() {
      this.$nextTick(() => {
        this.$emit("hm:test:answer-chosen", this.result);
      });
    }
  }
};
</script>

<style lang="scss">
.v-radio {
  &.v-radio--round-border {
    border-radius: 50px;
  }
  .v-label {
    padding-top: 0.2rem;
    padding-bottom: 0.2rem;
    padding-right: 0.5rem;
    @media (max-width: 1366px) {
      padding-right: 0.2rem;
    }
  }
}
.checkbox__label {
  & *:first-child {
    margin-top: 0;
  }
  & *:last-child {
    margin-bottom: 0;
  }
  & img {
    max-width: 99%;
  }
}
</style>
