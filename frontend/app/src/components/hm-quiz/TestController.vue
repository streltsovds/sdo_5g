<template>
  <main class="hm-test-controller__main">
    <v-card
      class="hm-test-controller"
      :class="{
        'hm-test-controller--breakpoint-sm-and-down': $vuetify.breakpoint.smAndDown,
      }"
    >
      <!--      :class="{ 'toolbar-content__auto-height': !limitTime }"-->
      <v-toolbar
        class="hm-test-controller__toolbar"
        ref="scrollToTop"
        v-if="computedProgress.length > 1"
        color="white"
        style="background-color: transparent !important; height: auto !important;"
        extended
        flat
      >
        <!-- slot="extension" был как атрибут в test-progress-->
        <test-progress
          class="wrapper-headers-and-content pt-0 pb-0"
          v-if="computedProgress.length > 1"
          :progress="computedProgress"
          :is-movement-restricted="restrictUserTraversal"
          @progress-click="handleProgressClick"
          style="height: auto;"
        />
      </v-toolbar>
      <div
        class="hm-test-controller__body-wrapper"
      >
        <!-- сноска над вопросами -->
        <v-layout
          v-if="
            commentInProcessOfFilling &&
              commentInProcessOfFilling.length > 0 &&
              showComment
          "
        >
          <v-flex>
            <div class="hm-test__comment__wrapper">
              <div style="width: 56px; height: 100%; padding-left: 17px">
                <v-icon color="#2D6BB1">
                  error
                </v-icon>
              </div>
              <div class="hm-test__comment" v-html="commentInProcessOfFilling" />
            </div>
          </v-flex>
        </v-layout>

        <v-scroll-x-transition
          :duration="isIE ? 0 : 250"
          mode="out-in"
          tag="div"
        >
          <test-body
            class="wrapper-headers-and-content"
            :key="currentIndex"
            v-bind="testBodyProps"
            @update="onAnswersUpdate"
            @next="handleNextClick"
            @prev="handlePrevClick"
            @finalize="setFinalizeInHmTestInBody"
          />
        </v-scroll-x-transition>
        <!--        <v-divider></v-divider>-->
      </div>
    </v-card>
    <hm-dialog
      :status="isDialogShown"
      size="small"
      semanticAccent="warning"
      @close="onDialogCancel"
    >
      <template v-slot:content>
        <p>{{ dialogMsg }}</p>
      </template>
      <template v-slot:buttons>
        <div v-if="!doFinalize">
          <v-btn @click="onDialogConfirm" color="primary" rounded>
            Продолжить
          </v-btn>
          <v-divider class="mr-2 ml-3" vertical />
          <v-btn @click="onDialogCancel" color="primary" text>
            Отмена
          </v-btn>
        </div>
        <div v-else>
          <v-btn
            :loading="isEndTestLoading"
            @click="endTest"
            color="warning"
            rounded
            style="margin-right: 10px"
          >
            Завершить
          </v-btn>
          <v-btn v-if="!doFinish"
                  @click="onDialogCancel"
                  color="primary"
                  text
          >
            Отмена
          </v-btn>
        </div>
      </template>
    </hm-dialog>
    <!-- <v-dialog v-model="isDialogShown" :persistent="doFinish" max-width="300">
      <v-card>
        <v-card-title> {{ dialogMsg }} </v-card-title>
        <v-divider />
        <v-card-actions class="justify-center" v-if="!doFinalize">
          <v-btn @click="onDialogConfirm" color="primary" rounded>
            Продолжить
          </v-btn>
          <v-divider class="mr-2 ml-3" vertical />
          <v-btn @click="onDialogCancel" color="primary" text>
            Отмена
          </v-btn>
        </v-card-actions>
        <v-card-actions class="justify-center" v-else>
          <v-btn
            :loading="isEndTestLoading"
            @click="endTest"
            color="warning"
            rounded
          >
            Завершить
          </v-btn>
          <v-divider class="mr-2 ml-3" v-if="!doFinish" vertical />
          <v-btn v-if="!doFinish"
                 @click="onDialogCancel"
                 color="primary"
                 text
          >
            Отмена
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog> -->
  </main>
</template>

<script>
import { easeOutCubic } from "vuetify/lib/services/goto/easing-patterns";
import axios from "axios";

import TestBody from "./partials/test-body/TestBody";
// import TestTimer from "./partials/TestTimer";
import TestProgress from "./partials/TestProgress";

import ResultsModel from "./models/ResultsModel";

import HmDialog from "@/components/controls/hm-dialog/HmDialog";

export const resultsModel = new ResultsModel();

export default {
  components: {
    TestBody,
    TestProgress,
    HmDialog
    // TestTimer
  },
  props: ["results", "finalizeInHmTestInHeader", "saveUrl", "finalizeUrl", "questions", "currentItem", "time", "progress", "restrictUserTraversal", "commentInProcessOfFilling", "limitTime", "showCommentForQuestions", "type", "modeSelfTest"], // eslint-disable-line
  data() {
    return {
      dialogMsg: null,
      isDialogShown: false,
      isSavingInProgress: false,
      isSavingPrevInProgress: false,
      currentId: null,
      isTimeEnded: false,
      recievedProgress: {},
      currentAnswers: {},
      allAnswers: {},
      doFinalize: false,
      doFinish: false,
      isEndTestLoading: false,
      showComment: true,
      idToGo: null,
      finalizeInHeaderBtn: false,
      highlightProperAnswers: false,
      finalizeInHmTestInBody: false
    };
  },
  computed: {
    // divMinHeight() {
    //   // if (commentInProcessOfFilling && commentInProcessOfFilling.length > 0)
    //   return `${this.$vuetify.breakpoint.height - 230}px`;
    // },
    savedResults() {
      let answers = {};
      if (this.results[this.currentIdFromQuestion]) {
        let newResults = {};
        for (const result in this.results[this.currentIdFromQuestion]) {
          if (this.results[this.currentIdFromQuestion].hasOwnProperty(result)) {
            const value = this.results[this.currentIdFromQuestion][result];
            newResults[result] = parseInt(value);
          }
        }
        answers = { ...newResults };
      }
      let answersLocal = {};
      for (const question of this.currentQuestions) {
        if (this.allAnswers[question.question_id]) {
          answersLocal[question.question_id] = this.allAnswers[
            question.question_id
          ];
        }
      }
      return { ...answers, ...answersLocal };
    },
    currentIndex() {
      const currentIdExists =
        this.currentId !== undefined && this.currentId !== null;
      return currentIdExists ? this.currentId : this.currentItem;
    },
    prevLastItemId() {
      return this.progress.length - 2;
    },
    lastItemIdx() {
      return this.progress.length - 1;
    },
    isLast() {
      return this.currentIndex === this.lastItemIdx;
    },
    isIE() {
      return this.$uaparser.getBrowser().name === `IE`;
    },
    computedProgress() {
      return this.progress.map((x, i) => {
        const id = x.itemId;
        if (this.recievedProgress[id]) {
          x[`itemProgress`] = this.recievedProgress[id];
        }
        if (i !== this.currentIndex) {
          x.current = false;
        } else {
          x.current = true;
        }
        return x;
      });
    },
    currentIdFromQuestion() {
      return this.questions[this.currentIndex].id;
    },
    currentQuestionsTitle() {
      return this.questions[this.currentIndex].name;
    },
    currentQuestions() {
      return this.questions[this.currentIndex].questions;
    },
    testBodyProps() {
      return {
        isLast: this.isLast,
        isOnly: this.progress.length === 1,
        isMovementRestricted: this.restrictUserTraversal,
        saving: this.isSavingInProgress,
        savingPrev: this.isSavingPrevInProgress,
        results: this.savedResults,
        questions: this.currentQuestions,
        title: this.currentQuestionsTitle,
        resultsModel: resultsModel,
        showCommentForQuestions: this.showCommentForQuestions,
        type: this.type,
        finalizeInHeaderBtn: this.finalizeInHmTestInHeader,
        modeSelfTest: this.modeSelfTest,
        highlightProperAnswers: this.highlightProperAnswers
      };
    }
  },
  watch: {
    isDialogShown(val) {
      if (this.isSavingInProgress && !val) this.isSavingInProgress = false;
    },
    // currentIdFromQuestion(val) {
    //   this.changeUrlOnItemIdChange(val);
    // },
    // отлавливаем клик в header, вызываем функцию сохранения
    finalizeInHeaderBtn() {
      if(this.finalizeInHeaderBtn) {
        this.saveAnswersAndFinalize();
      }
    }
  },
  // mounted() {
  //   this.changeUrlOnItemIdChange(this.currentIdFromQuestion);
  // },
  beforeUpdate() {
    if(this.finalizeInHmTestInHeader) {
      this.finalizeInHeaderBtn = this.finalizeInHmTestInHeader;
    }
  },
  methods: {
    changeUrlOnItemIdChange(id) {
      let { href } = window.location;
      if (href.includes(`/item_id/`)) {
        let index;
        href = href
          .split(`/`)
          .map((part, i) => {
            if (part === `item_id`) {
              index = i + 1;
              return part;
            }
            if (i === index) {
              return id;
            }
            return part;
          })
          .join(`/`);
      } else {
        href = `${href}/item_id/${id}`;
      }
      try {
        this.$log.debug(`Pushing new href to browser URL "${href}"`);
        history.pushState({}, ``, href);
      } catch (err) {
        this.$log.error(
          `Looks like using Histori API not working here :(`,
          err
        );
      }
    },
    onTimerEnd() {
      this.isTimeEnded = true;
      this.doFinalize = true;
      this.handleNextClick().then(() => {
        setTimeout(() => this.endTest(), 1000 * 30);
      });
    },
    handleProgressClick(idx) {
      this.idToGo = idx;
      this.initSaving().then(() => {
        this.saveAnswers()
          .then(result => result.data)
          .then(this.handleReciveMessageWithId);
      });
    },
    endTest() {
      fetch(this.finalizeUrl)
        .then(response => {
          if(response.status === 200) {
            // if (this.isSavingInProgress) this.isSavingInProgress = false;
            // this.isEndTestLoading = true;
            window.location.reload();
          }
        })
      // window.location.href = this.finalizeUrl;
    },
    // scrollToTop() {
    //   // Return it cause it is a promise
    //   // and we can do stuff after the animation
    //   return this.$vuetify.goTo(this.$refs.scrollToTop, {
    //     duration: this.isIE ? 0 : 1000,
    //     offset: 0,
    //     easing: easeOutCubic
    //   });
    // },
    nextScreen() {
      this.$nextTick(() => {
        if (!this.isLast) this.currentId = this.currentIndex + 1;
      });
    },
    triggerNextScreen() {
      return this.$nextTick().then(() => {
        if (this.isSavingInProgress) this.isSavingInProgress = false;
        // this.scrollToTop().then(() => {
        //   this.nextScreen();
        // });
        this.nextScreen();
      });
    },
    setFinalizeInHmTestInBody() {
      this.finalizeInHmTestInBody = true;
      this.saveAnswersAndFinalize();
    },
    saveAnswers() {
      const params = {};
      if (this.isLast) {
        params[`finalize`] = 1;
      }
      params[`stop`] = 0;

      if(this.finalizeInHmTestInHeader || this.finalizeInHmTestInBody)
        params[`stop`] = 1;

      this.$emit('finalizeInTestHeaderStatus', false);
      this.finalizeInHmTestInBody = false;

      // параметр item_id уже есть в строке урла!
      params[`real_item_id`] = this.progress[this.currentIndex].itemId;
      if (this.isTimeEnded) {
        params[`timestop`] = 1;
      }
      const currentQids = this.currentQuestions.map(q => String(q.question_id));
      const bodyFormData = resultsModel.collectAnswers(params, currentQids);

      console.log(this.saveUrl)
      return axios({
        method: "post",
        url: this.saveUrl,
        data: bodyFormData
      });
    },
    onAnswersUpdate(answers) {
      this.allAnswers = { ...this.allAnswers, ...answers };
      this.currentAnswers = { ...answers };
    },
    triggerDialog(message) {
      this.dialogMsg = message;
      this.isDialogShown = true;
    },
    emptyDialog() {
      return this.$nextTick().then(() => {
        this.dialogMsg = null;
        this.isDialogShown = false;
        this.finalizeInHeaderBtn = false;
      });
    },
    onDialogCancel() {
      this.$nextTick()
        .then(() => {
          if (this.isSavingInProgress) this.isSavingInProgress = false;
        })
        .then(this.emptyDialog);
    },
    onDialogConfirm() {
      this.emptyDialog().then(this.triggerNextScreen);
    },
    handleReciveMessage({ result, confirm, itemId, progress, next, finish }) {
      this.$nextTick()
        .then(() => {
          this.isSavingInProgress = true;
          this.recievedProgress[itemId] = progress;
        })
        .then(() => {
          if (result) {
            this.triggerNextScreen();
            if (this.isLast) {
              console.log(this.isLast)
              console.log(result)
              this.endTest();
            }
          }
          if (confirm) {
            if (!next) {
              this.doFinalize = true;
            } else {
              this.doFinalize = false;
            }
            if (finish) {
              this.doFinalize = true;
              this.doFinish = true;
            } else {
              this.doFinish = false;
            }
            this.triggerDialog(confirm);
          }
        });
    },
    handleReciveMessageWithId({ itemId, progress }) {
      this.$nextTick()
        .then(() => {
          this.isSavingInProgress = true;
          this.recievedProgress[itemId] = progress;
        })
        .then(() => {
          this.$nextTick(() => {
            this.currentId = this.idToGo;
            this.idToGo = null;
            this.isSavingInProgress = false;
          });
        });
    },
    handleReciveMessagePrev({ itemId, progress }) {
      this.$nextTick()
        .then(() => {
          this.isSavingPrevInProgress = true;
          this.recievedProgress[itemId] = progress;
        })
        .then(() => {
          this.$nextTick(() => {
            this.currentId = this.idToGo;
            this.idToGo = null;
            this.isSavingPrevInProgress = false;
          });
        });
    },
    initSaving() {
      return this.$nextTick().then(() => {
        this.isSavingInProgress = true;
      });
    },
    initPrevSaving() {
      return this.$nextTick().then(() => {
        this.isSavingPrevInProgress = true;
      });
    },
    handleNextClick() {
      return this.initSaving().then(() => {
        this.saveAnswers()
          .then(result => result.data)
          .then(this.handleReciveMessage);
      });
    },
    handlePrevClick() {
      this.idToGo = this.prevLastItemId;
      this.initPrevSaving().then(() => {
        this.saveAnswers()
          .then(result => result.data)
          .then(this.handleReciveMessagePrev);
      });
    },
    saveAnswersAndFinalize() {
      this.initSaving().then(() => {
        this.saveAnswers()
          .then(result => result.data)
          .then(this.handleReciveMessage);
          //.then(() => this.endTest());
      });
    }
  }
};
</script>

<style lang="scss">
.hm-test {

  & .v-expansion-panels .v-expansion-panel {
    background: inherit !important;
  }
  & .hm-test-controller {
    box-shadow: none !important;
  }

  &__comment {
    & p:only-child {
      padding: 0;
      margin: 0;
    }

    &__wrapper {
      align-items: start;
      background: #FAF3D8;
      border-radius: 4px;
      box-sizing: border-box;
      display: flex;
      width: 100%;
      margin: 16px 0;
      min-height: 56px;
      padding: 16px 0 15px 0;
    }
  }
}

.hm-test__comment {
  > p {
    > span {
      font-size: 14px;
      line-height: 21px;
      font-weight: 400;
      color: #1E1E1E;
      letter-spacing: 0.02em;
    }
  }
}

.hm-test-controller__toolbar {
  // margin-top: 15px;
  & .v-toolbar__extension {
    height: auto !important;
    padding: 0;
  }
  & .v-toolbar__content {
    padding: 0 !important;
    height: auto !important;
  }
}
//.toolbar-content__auto-height {
//  .v-toolbar__content {
//    height: auto !important;
//  }
//}

.hm-test-controller {
    background: none !important;
    box-shadow: none !important;
    padding: 0 16px !important;
}

.hm-test-controller.hm-test-controller--breakpoint-sm-and-down {
  padding: 0 !important;
}

.hm-test-controller__body-wrapper {
  position: relative;
  overflow: inherit;
  // margin-top: 12px;
}
</style>
