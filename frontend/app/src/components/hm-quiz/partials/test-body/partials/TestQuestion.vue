<template>
  <v-card>
    <component
      :is="questionTypeName"
      v-bind="questionProps"
      @hm:test:answer-chosen="onAnswerChosen"
      @hm:test:answer-comment="$emit('hm:test:answer-comment', $event)"
    />
    <v-divider v-if="!isLegacy" />
    <v-card-actions v-if="!isLegacy">
      <v-btn
        :disabled="isBtnDisabled"
        @click="onAnswerConfirm"
        color="primary"
        rounded
      >
        <v-slide-y-transition appear>
          <span v-if="isAnswered">Обновить ответ</span>
          <span v-else>Сохранить ответ</span>
        </v-slide-y-transition>
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import TestSingle from "./question-types/TestSingle";
import TestMultiple from "./question-types/TestMultiple";
import TestText from "./question-types/TestText";
import TestMapping from "./question-types/TestMapping";
import TestSorting from "./question-types/TestSorting";
import TestClassification from "./question-types/TestClassification";
import TestImagemap from "./question-types/TestImagemap";
import TestPlaceholder from "./question-types/TestPlaceholder/index";

export default {
  components: {
    TestSingle,
    TestMultiple,
    TestText,
    TestMapping,
    TestSorting,
    TestClassification,
    TestImagemap,
    TestPlaceholder
  },
  props: ["answers", "showVariants", "variantsAsHtml", "questionType", "qId", "isAnswered", "chosenAnswers", "highlight", "fileId", "showComment","questText"], //eslint-disable-line
  data() {
    return {
      isBtnDisabled: true,
      selectedAnswers: []
    };
  },
  computed: {
    questionTypeName() {
      const type = this.questionType === "Free" ? "Text" : this.questionType;
      return `Test${type}`;
    },
    questionProps() {
      const props = {
        highlight: this.highlight,
        "selected-answer": this.chosenAnswers,
        answers: this.answers,
        showComment: this.showComment,
        questText: this.questText
      };

      if (this.fileId !== undefined) {
        props[`fileId`] = this.fileId;
      }
      props[`variantsAsHtml`] = this.variantsAsHtml;
      props[`showVariants`] = this.showVariants;
      return props;
    },
    isLegacy() {
      return this.$root.isLegacy;
    }
  },
  methods: {
    onAnswerChosen(payload) {
      this.isBtnDisabled = false;
      this.selectedAnswers = payload;
      if (this.isLegacy) {
        this.onAnswerConfirm();
      }
    },
    onAnswerConfirm() {
      this.$log.debug(this.selectedAnswers);
      this.$nextTick(() => {
        this.$emit("hm:test:answer-confirmed", {
          question: this.qId,
          answers: this.selectedAnswers
        });
      });
    }
  }
};
</script>

<style lang="scss">
.variant-with-html-markup {
  img {
    max-width: 100%;
    height: auto;
  }
  & p:only-child {
    margin: 0;
    padding: 0;
  }
}
</style>
