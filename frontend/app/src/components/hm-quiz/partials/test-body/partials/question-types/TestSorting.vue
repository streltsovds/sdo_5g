<template>
  <v-card-text class="pb-2 pt-2 hm-test-sorting">
    <draggable v-model="datas" :options="dragOptions">
      <transition-group @touchstart="touchstart" @touchend="touchend">
        <test-draggable-item
          v-for="data in datas"
          :key="data.id"
          :text="data.text"
          :variants-as-html="variantsAsHtml"
          :is-mobile="isMobile"
          class="hm-test-sorting_item"
        >
        </test-draggable-item>

        <!--        <div v-for="data in datas" :key="data.id" >-->
        <!--          <v-icon>drag_indicator</v-icon>-->
        <!--          &lt;!&ndash; eslint-disable &ndash;&gt;-->
        <!--          <span v-if="variantsAsHtml" class="variant-with-html-markup" v-html="data.text" />-->
        <!--          &lt;!&ndash; eslint-enable &ndash;&gt;-->
        <!--          <span v-else>-->
        <!--            {{data.text}}-->
        <!--          </span>-->
        <!--          <v-icon>drag_indicator</v-icon>-->
        <!--        </div>-->
      </transition-group>
    </draggable>
  </v-card-text>
</template>
<script>
import draggable from "vuedraggable";
import testDraggableItem from "./TestDraggableItem";

export default {
  components: { draggable, testDraggableItem },
  props: ["answers", "variantsAsHtml", "selectedAnswer", "highlight"], // eslint-disable-line
  data() {
    return {
      datas: [],
      selectedAnswerWasInit: false,
      scrollable: true
      // drag: false
    };
  },
  computed: {
    result() {
      let formatted = {};
      [...this.datas].forEach((data, key) => {
        formatted[data.id] = key + 1;
      });
      return formatted;
    },
    dragOptions() {
      return {
        // delay: this.isMobile ? 100 : 0
        handle: this.isMobile ? ".drag-area" : undefined
      };
    },
    isMobile() {
      return document.body.classList.contains("is-mobile");
    }
  },
  watch: {
    datas() {
      this.sendAnswer();
    },
    selectedAnswer(selectedAnswer) {
      if (this.selectedAnswerWasInit) return;
      this.selectedAnswerWasInit = true;

      let datas = [];
      console.log('awdadawd');

      Object.entries(selectedAnswer).forEach(([key, value]) => {
        let order = value;
        datas[order - 1] = this.getDataById(key);
      });

      this.datas = datas;
    }
  },
  mounted() {
    console.log("mounted test sorting");
    this.init();
    document.addEventListener("touchmove", this.touchmove, { passive: false });
  },
  methods: {
    init() {
      let newDatas = [];
      this.answers.forEach(answer => {
        newDatas.push({
          id: answer.question_variant_id,
          text: answer.variant
        });
      });
      this.datas = newDatas;
      this.sendAnswer();
    },
    sendAnswer() {
      this.$nextTick(() => {
        this.$emit("hm:test:answer-chosen", this.result);
      });
    },
    getDataById(id) {
      return this.datas.find(data => Number(data.id) === Number(id));
    },
    touchmove(e) {
      if (!this.scrollable) {
        e.preventDefault();
      }
    },
    touchstart() {
      this.scrollable = false;
    },
    touchend() {
      this.scrollable = true;
    }
  }
};
</script>

<style lang="scss">
</style>
