<template>
  <v-card-text
    class="unselectable pb-2 pt-2 hm-test-classification"
    onselectstart="return false;"
    unselectable="on"
    style="padding-bottom: 47px !important;"
  >
    <v-layout class="hm-test-classification_layout" wrap>
      <v-flex class="hm-test-classification_list" sm6 xs12>
        <draggable
          class="hm-test-classification_draggable"
          v-model="datas"
          :options="dragOptions"
        >
          <transition-group @touchstart="touchstart" @touchend="touchend">
            <test-draggable-item
              v-for="data in datas"
              :key="data.id"
              :text="data.text"
              :variants-as-html="variantsAsHtml"
              :is-mobile="isMobile"
            />
          </transition-group>
        </draggable>
      </v-flex>
      <v-flex class="hm-test-classification_groups" sm6 xs12>
        <div
          class="hm-test-classification_group"
          v-for="(classification, key) in classifications"
          :key="key"
          style="background: #FFFFFF; border: 1px solid #DADADA; box-sizing: border-box; border-radius: 4px; box-shadow: none"
        >
          <div class="hm-test-classification_group-header">
            <span v-text="classification.name" />
          </div>
          <draggable
            class="hm-test-classification_draggable hm-test-classification_group-body"
            v-model="classification.list"
            :options="dragOptions"
          >
            <transition-group @touchstart="touchstart" @touchend="touchend">
              <test-draggable-item
                v-for="data in classification.list"
                :key="data.id"
                :text="data.text"
                :variants-as-html="variantsAsHtml"
                :is-mobile="isMobile"
              />
            </transition-group>
          </draggable>
        </div>
      </v-flex>
    </v-layout>
  </v-card-text>
</template>

<script>
import draggable from "vuedraggable";
import testDraggableItem from "./TestDraggableItem";

export default {
  components: { draggable, testDraggableItem },
        props: ["answers", "selectedAnswer", "variantsAsHtml", "highlight"], // eslint-disable-line
  data() {
    return {
      datas: [],
      classifications: [],
      editable: true,
      questionId: null,
      groups: {},
      scrollable: true
    };
  },
  computed: {
    dragOptions() {
      return {
        // animation: 0,
        group: this.questionId,
        disabled: !this.editable,
        // delay: this.isMobile ? 200 : 0
        // delay: this.isMobile ? 200 : 0
        // delay: 20
        handle: this.isMobile ? ".drag-area" : undefined
      };
    },
    isMobile() {
      return document.body.classList.contains("is-mobile");
    },
    result() {
      let formatted = {};
      this.classifications.forEach(classification => {
        classification.list.forEach(item => {
          formatted[item.id] = this.groups[classification.name];
        });
      });
      return formatted;
    }
  },
  watch: {
    datas() {
      // if (this.datas.length !== 0) return;

      this.sendAnswer();
    },
    classifications: {
      handler: function() {
        // if (this.datas.length !== 0) return;

        this.sendAnswer();
      },
      deep: true
    },
    selectedAnswer(selectedAnswers) {
      if (!selectedAnswers) return;
      for (let answerId in selectedAnswers) {
        if (!selectedAnswers.hasOwnProperty(answerId)) continue;
        let groupName = this.getGroupName(selectedAnswers[answerId]);
        let answer = this.getAnswerById(answerId);
        if (answer && groupName) this.addAnswerToGroup(answer, groupName);
      }

      // this.datas = [];
    }
  },
  mounted() {
    this.init();
  },
  methods: {
    init() {
      this.questionId =
        this.answers.length > 0 ? this.answers[0].question_id : null;

      this.answers.forEach(answer => {
        this.datas.push({
          id: answer.question_variant_id,
          text: answer.data
        });

        if (
          !this.groups.hasOwnProperty(answer.variant) ||
          this.groups[answer.variant] > answer.question_variant_id
        ) {
          this.groups[answer.variant] = answer.question_variant_id;
        }

        if (
          !this.classifications.some(
            classification => classification.name === answer.variant
          )
        ) {
          this.classifications.push({
            name: answer.variant,
            list: []
          });
        }
      });
      document.addEventListener("touchmove", this.touchmove, {
        passive: false
      });
    },
    // prevent(event) {
    //   event.preventDefault();
    //   event.stopPropagation();
    // },
    sendAnswer() {
      this.$nextTick(() => {
        this.$emit("hm:test:answer-chosen", this.result);
      });
    },
    getGroupName(groupId) {
      for (let name in this.groups) {
        if (!this.groups.hasOwnProperty(name)) continue;
        if (this.groups[name] === groupId) return name;
      }
      return null;
    },
    getAnswerById(answerId) {
      return this.datas.find(answer => Number(answer.id) === Number(answerId));
    },
    addAnswerToGroup(answer, groupName) {
      let classification = this.classifications.find(
        group => group.name === groupName
      );
      if (!classification) return;

      classification.list.push(JSON.parse(JSON.stringify(answer)));
      const newDatas = [];
      this.datas.forEach(item => {
        if(item.id === answer.id) return
        else newDatas.push(item)
      });
      this.datas = newDatas;
    },
    touchmove(e) {
      if (!this.scrollable) {
        e.preventDefault();
      }
    },
    touchstart() {
      console.log("touchstart disableScroll: ");
      this.scrollable = false;
    },
    touchend() {
      console.log("touchend enableScroll: ");
      this.scrollable = true;
    }
    // ,
    // disableScroll(event) {
    //   document.addEventListener("touchmove", () => this.prevent(event), false);
    // },
    // enableScroll(event) {
    //   document.removeEventListener(
    //     "touchmove",
    //     () => this.prevent(event),
    //     false
    //   );
    // }
  }
};
</script>

<style lang="scss">
  .hm-test-classification_list {
    margin-bottom: 15px;
  }
  .hm-test-classification_group-header,
  .hm-test-classification_group-body {
    padding: 10px;
  }

  .hm-test-classification_group-header {
    color: #000000;
    font-size: 14px;
    font-weight: 400;
  }

  .hm-test-classification_group-body {
    background: rgba(212, 227,251, .3);
    border-radius: 4px;
    margin: 0 10px 10px 10px;
  }
  /* вся область, содержит много перетаскиваемых строк */
  .hm-test-classification_draggable {
    > span {
      min-height: 50px;
      display: block;
    }
  }

  .hm-test-classification_group {
    display: flex;
    -webkit-box-shadow: 0px 2px 1px -1px rgba(0, 0, 0, 0.2),
    0px 1px 1px 0px rgba(0, 0, 0, 0.14), 0px 1px 3px 0px rgba(0, 0, 0, 0.12);
    box-shadow: 0px 2px 1px -1px rgba(0, 0, 0, 0.2),
    0px 1px 1px 0px rgba(0, 0, 0, 0.14), 0px 1px 3px 0px rgba(0, 0, 0, 0.12);

    flex-direction: column;
    margin-bottom: 15px;
  }
</style>
