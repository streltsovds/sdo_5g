<template>
  <v-card
    class="hm-test-body"
    style="position: relative; background: none !important; box-shadow: none !important; overflow: initial;"
    flat
    tile
  >
    <test-assessment
      v-if="isAssessment"
      :criteria="questions"
      :data="dataAssessment"
      :chosen-answers="selectedAnswers"
      @hm:test:answer-confirmed="onAnswerConfirmed"
    />
    <v-expansion-panels
      v-else
      :value="showedIndexes"
      multiple
    >

      <!--           v-model="showed" expand -->
      <v-expansion-panel
        class="v-expansion-panel--wider"
        v-for="(question, i) in questions"
        :key="question.question_id"
        :readonly="isLegacy"
        inset>

        <div class="question__title">
          <div class="question__text card__header-test" v-if="question.type !== 'placeholder'" v-html="question.question"/>
          <div class="question__text card__header-justification" v-if="question.justification"
               @mouseover="setHovered(question.question_id, true)"
               @mouseleave="setHovered(question.question_id, false)"
               :class="{active: getHovered(question.question_id)}"
               >
              <svg-icon
                    width="18"
                    height="18"
                    name="folder"
            />
            <span class="card__header-justification-text">{{ question.justification }}</span>
          </div>
        </div>

        <v-expansion-panel-content
          class="card-testings"
          :expand-icon="
            selectedAnswers[question.question_id]
              ? 'check_circle'
              : 'radio_button_unchecked'
          "
          :class="showedIndexes.includes(i) ? ' white--text' : 'answer-chosen'"
          :hide-actions="showedIndexes.includes(i)"
          style=""
        >
          <test-question
            :highlight="highlightProperAnswers && highlightedAnswers[question.question_id]"
            :chosen-answers="selectedAnswers[question.question_id]"
            :variants-as-html="getVariantsAsHtml(question)"
            :show-variants="getShowVariants(question)"
            :is-answered="selectedAnswers[question.question_id]"
            :q-id="question.question_id"
            :file-id="question.file_id"
            :question-type="getType(question)"
            :answers="getAnswers(question)"
            :quest-text="question.question"
            :show-comment="showCommentForQuestions"
            @hm:test:answer-confirmed="
              p => {
                onAnswerConfirmed(p, i, question);
              }
            "
            @hm:test:answer-comment="
              p => {
                onAnswerComment(p, i, question);
              }
            "
          />
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>

    <v-card-actions v-if="$vuetify.breakpoint.mdAndUp" style="max-width: 100%; margin: 34px 0 26px 0; padding: 0">
      <v-btn
        v-if="modeSelfTest"
        :loading="savingPrev"
        :disabled="savingPrev"
        @click="onCheckBtnClick"
        outlined
        style="border-radius: 4px; padding: 0 26px;"
        color="primary"
      >
        <v-icon class="mr-2" style="color: #1e1e1e; font-size: 17px;">
          help_outline
        </v-icon>
        <span style="color: #1E1E1E; font-size: 14px; font-weight: 300">Проверить</span>
      </v-btn>
      <v-btn
        v-if="isLast && !isMovementRestricted && !isOnly"
        :loading="savingPrev"
        :disabled="savingPrev"
        @click="onPrevBtnClick"
        outlined
        style="border-radius: 4px; padding: 0 26px;"
        color="primary"
      >
        <v-icon class="mr-2" style="color: #1e1e1e; font-size: 17px;">
          arrow_back
        </v-icon>
        <span style="color: #1E1E1E; font-size: 14px; font-weight: 300">Назад</span>
      </v-btn>
      <v-btn
        v-if="!isLast"
        :loading="saving"
        :disabled="saving"
        @click="onNextBtnClick"
        color="#FF9800"
        style="border-radius: 4px; padding: 0 26px;"
      >
        <span style="color: #ffffff; font-size: 14px; font-weight: 300">Вперёд</span>
        <v-icon class="ml-2" style="color: #ffffff; font-size: 16px">
          arrow_forward_ios
        </v-icon>
      </v-btn>
      <v-btn
        v-else
        :loading="saving"
        :disabled="saving"
        @click="onNextBtnClick"
        color="success"
        style="border-radius: 4px; padding: 0 26px;"
      >
        <span style="color: #ffffff; font-size: 14px; font-weight: 300">Готово</span>
        <v-icon class="ml-2" style="font-size: 17px;">
          done
        </v-icon>
      </v-btn>
      <v-spacer />
      <v-btn class="btn-save-test"
             @click="onFinalizeBtnClick"
             outlined
             color="#70889E"
             text
      >
        <span>{{ type == "poll" ? "Прервать опрос" : "Прервать тестирование" }}
        </span>
        <v-icon size="16" color="#DADADA" left>
          cancel
        </v-icon>
      </v-btn>
    </v-card-actions>
    <v-bottom-sheet v-else v-model="areActionsShowed">
      <template v-slot:activator="{ on: onBottomSheet }">
        <v-btn
          class="hm-test_burger"
          :loading="saving || savingPrev"
          v-on="onBottomSheet"
          fab
          fixed
          right
          bottom
          color="primary"
        >
          <v-icon large>
            menu
          </v-icon>
        </v-btn>
      </template>
      <v-card tile>
        <v-list>
          <v-list-item
            v-if="modeSelfTest"
            @click="
              () => {
                areActionsShowed = !areActionsShowed;
                $nextTick().then(onCheckBtnClick);
              }
            "
          >
            <v-list-item-avatar>
              <v-avatar>
                <v-icon color="primary">
                  help_outline
                </v-icon>
              </v-avatar>
            </v-list-item-avatar>
            <v-list-item-title> Проверить </v-list-item-title>
          </v-list-item>
          <v-list-item
            v-if="isLast && !isMovementRestricted && !isOnly"
            @click="
              () => {
                areActionsShowed = !areActionsShowed;
                $nextTick().then(onPrevBtnClick);
              }
            "
          >
            <v-list-item-avatar>
              <v-avatar>
                <v-icon color="primary">
                  arrow_back
                </v-icon>
              </v-avatar>
            </v-list-item-avatar>
            <v-list-item-title> Назад </v-list-item-title>
          </v-list-item>
          <v-list-item
            v-if="!isLast"
            @click="
              () => {
                areActionsShowed = !areActionsShowed;
                $nextTick().then(onNextBtnClick);
              }
            "
          >
            <v-list-item-avatar>
              <v-avatar>
                <v-icon color="primary">
                  arrow_forward
                </v-icon>
              </v-avatar>
            </v-list-item-avatar>
            <v-list-item-title> Вперёд </v-list-item-title>
          </v-list-item>
          <v-list-item
            v-else
            @click="
              () => {
                areActionsShowed = !areActionsShowed;
                $nextTick().then(onNextBtnClick);
              }
            "
          >
            <v-list-item-avatar>
              <v-avatar>
                <v-icon color="success">
                  done
                </v-icon>
              </v-avatar>
            </v-list-item-avatar>
            <v-list-item-title> Готово </v-list-item-title>
          </v-list-item>
          <v-list-item
            @click="
              () => {
                areActionsShowed = !areActionsShowed;
                $nextTick().then(onFinalizeBtnClick);
              }
            "
          >
            <v-list-item-avatar>
              <v-avatar>
                <v-icon color="error">
                  cancel
                </v-icon>
              </v-avatar>
            </v-list-item-avatar>
            <v-list-item-title>
              {{ type == "poll" ? "Прервать опрос" : "Прервать тестирование" }}
            </v-list-item-title>
          </v-list-item>
        </v-list>
      </v-card>
    </v-bottom-sheet>
  </v-card>
</template>

<script>
import TestQuestion from "./partials/TestQuestion";
import TestAssessment from "./partials/TestAssessment";
import { shuffleArray, capitalize } from "../../utils";

import { resultsModel } from "../../TestController";
import SvgIcon from "@/components/icons/svgIcon";

export default {
  components: {
    SvgIcon,
    TestQuestion,
    TestAssessment
  },
  props: ["isAssessment", "dataAssessment", "results", "isOnly", "saving", "savingPrev", "questions", "isLast", "isMovementRestricted", "title", "resultsModel", "showCommentForQuestions", "type", "modeSelfTest"], // eslint-disable-line
  data() {
    let showed = [];

    // начальное состояние
    for (var index = 0; index < this.questions.length; index++) {
      let question = this.questions[index];
      let question_id = question.question_id;
      showed.push(index);
    }

    return {
      currentIndex: 0,
      hoveredQuestions: {},
      selectedAnswers: {},
      cachedAnswers: {},
      areActionsShowed: false,
      // vuetify 2: index-based
      showedIndexes: showed,
      // TODO remove showed
      showed: [...this.questions.map(x => !this.results[x.question_id]) ], // eslint-disable-line
      // eslint-disable-line
      highlightedAnswers: [],
      highlightProperAnswers: false,
      answerComments: {}
    };
  },
  computed: {
    isLegacy() {
      return this.$root.isLegacy;
    }
  },
  mounted() {
    if (this.results) this.selectedAnswers = this.results;
    this.$nextTick().then(() => {
      if (this.isLegacy) this.showed = this.showed.map(_ => true); // eslint-disable-line
    });
  },
  methods: {
    setHovered(question_id, state) {
        this.$set(this.hoveredQuestions, question_id, state);
    },
    getHovered(question_id) {
        return this.hoveredQuestions[question_id];
    },
    getVariantsAsHtml({ variants_use_wysiwyg }) {
      return parseInt(variants_use_wysiwyg, 10) === 1;
    },
    getShowVariants({ show_variants }) {
      return parseInt(show_variants, 10) === 1;
    },
    getType(question) {
      const { type, criterion_id } = question;
      if(type && !criterion_id) return capitalize(type);
      else if(criterion_id) return capitalize('assessment');
    },
    setItemVisibility(item, i) {
      if (item) {
        this.showed[i] = false;
      } else {
        this.showed[i] = true;
      }
      return this.showed[i];
    },
    onFinalizeBtnClick() {
      this.$emit(`finalize`);
    },
    getAnswers(question) {
      if (this.cachedAnswers[question.question_id]) {
        return this.cachedAnswers[question.question_id];
      }
      const { variants = {}, shuffle_variants = {} } = question;
      const varianstArray = Object.values(variants);
      this.$set(
        this.cachedAnswers,
        question.question_id,
        +shuffle_variants ? shuffleArray(varianstArray) : varianstArray
      );
      return this.cachedAnswers[question.question_id];
    },
    onAnswerConfirmed({ question, answers, indicatorInfo }, i, questionInfo) {
      const answer = {
        question_id: null,
        type: '',
        answers: answers
      };

      if(indicatorInfo) {
        this.$set(this.highlightedAnswers, indicatorInfo.question_id, false);
        answer.question_id = indicatorInfo.question_id;
        answer.type = 'assessment';
      } else {
        this.$set(this.highlightedAnswers, questionInfo.question_id, false);
        answer.question_id = questionInfo.question_id;
        answer.type = questionInfo.type;
      }

      this.$set(this.selectedAnswers, question, answers);

      /* ---- answer saving ---- */
      this.$log.debug(`Given Answer is`, answers);

      this.$log.debug(`answer to model is`, answer);

      resultsModel.addAnswer(answer);

      /* ---- answer saving end ---- */

      this.$emit(`update`, this.selectedAnswers);

      if (!this.isLegacy) {
        this.$nextTick(() => {
          this.$set(this.showed, i, false);
        });
      }
    },
    onAnswerComment(text, i, { question_id }) {
      this.$set(this.answerComments, question_id, text);

      const comment = { question_id, text };
      resultsModel.addComment(comment);
    },
    onNextBtnClick() {
      this.$nextTick(() => {
        this.$emit(`next`);
      });
    },
    onPrevBtnClick() {
      this.$nextTick(() => {
        this.$emit(`prev`);
      });
    },
    onCheckBtnClick() {
      this.highlightProperAnswers = true;
      this.highlightedAnswers = this.highlightedAnswers.map(() => true);
    }
  }
};
</script>

<style lang="scss">
.v-expansion-panel--wider {
  .v-expansion-panel__container {
    max-width: 98%;
    &.v-expansion-panel__container--active {
      max-width: 98%;
      margin-bottom: 8px;
      margin-top: 8px;
    }
  }
}

.answer-not-chosen {
  background: linear-gradient(
    to bottom,
    rgba(#ffc107, 0.3),
    rgba(#333, 0.1)
  ) !important;
}
.question__text {
  & *:first-child {
    margin-top: 0 !important;
  }
  & *:last-child {
    margin-bottom: 0 !important;
  }
  img {
    max-width: 100%;
    height: auto;
  }
}
.hm-test_burger {
  &:before {
    border: 1px solid black;
  }
}

.unselectable {
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
.btn-save-test {
  border: 1px solid #70889E !important;
  border-radius: 4px !important;
  color: #1E1E1E !important;
  .v-btn__content {
    > span {
      line-height: 16px !important;
      margin-right: 9px !important;
    }
    .v-icon--left {
      margin: 0 !important;
      padding: 0 !important;
    }
  }
}
.v-expansion-panel--wider {
  > li {
    margin: 0 !important;
  }
}

.question__title {
  display: flex;
  flex-direction: row;
}

.card__header-test, .card__header-justification {
  font-size: 16px;
  font-weight: 500;
  line-height: 24px;
  color: #1E1E1E;
  padding: 14px 0 4px 4px;
  margin-bottom: 16px !important;
  margin-top: 26px !important;
}

.card__header-justification {
  display: flex;
  flex-direction: row;
}

.card__header-justification-text {
  padding-left: 10px;
  display: flex;
  height: 25px;

  max-width: 0;
  width: auto;
  overflow: hidden;
  will-change: max-width, opacity;
  transition: max-width 0.3s ease;

}

.card__header-justification.active {
  .card__header-justification-text {
    max-width: 100%;
  }
}

.v-input--selection-controls__input {
  margin-right: 18px !important;
}

.question__text.card__header-test,
.question__text.card__header-justification {
  padding-left: 20px!important;
  padding-bottom: 0!important;
  padding-top: 0!important;
}

.v-expansion-panel-content__wrap {
  padding-left: 20px!important;
  padding-bottom: 26px!important;
  > div {
    .v-card__text {
      padding-left: 0!important;
      > div {
        padding-left: 0!important;
      }
    }
  }
}


.v-expansion-panel:not(:first-child) {
  margin-top: 26px!important;
}
.v-expansion-panel{
  box-shadow: 0 10px 30px rgba(209, 213, 223, 0.5) !important;
  border-radius: 4px !important;
  &:before {
    content: none;
  }
}


.card-testings {
  .v-expansion-panel-content__wrap {
    > div {
      box-shadow: none!important;
    }
  }
}
/* отключение выделения текста из-за проблем с drag'n'drop на телефонах */
/*body.is-mobile,*/
/*body.is-mobile * {*/
/*  -webkit-touch-callout: none;*/
/*  -webkit-user-select: none;*/
/*  -khtml-user-select: none;*/
/*  -moz-user-select: none;*/
/*  -ms-user-select: none;*/
/*  user-select: none;*/
/*}*/
</style>
