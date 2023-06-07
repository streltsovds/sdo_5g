<template>
  <v-card-text class="pb-2 pt-2 hm-test-sorting">
    <!-- <ul class="hm-test-sorting__index-list">
      <li class="hm-test-sorting__index-list-item" :style="{'height': draggableHeight + 'px'}" :key="index" v-for="(data, index) in datas">
        {{ index + 1 }}
      </li>
    </ul> -->
    <draggable class="hm-test-sorting__draggable" v-model="datas" :options="dragOptions">
      <transition-group @touchstart="touchstart" @touchend="touchend">
        <test-draggable-item
          class="hm-test-sorting_item"
          v-for="(data, index) in datas"
          :key="data.id"
          :text="data.text"
          :variants-as-html="variantsAsHtml"
          :is-mobile="isMobile"
          :index="index+1"
        />
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
      scrollable: true,
      draggableHeight: 100,
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

    this.$nextTick(() => {
      this.draggableHeight = this.getDraggableHeight();
    })
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
    getDraggableHeight(){
      let els = document.querySelectorAll('.hm-test-sorting_item');
      const max = Math.max(...Array.from(els).map(item => item.offsetHeight));
      console.log(max)
      return max - 10;
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
.hm-test-sorting{
  display: flex;
  &__index-list{
    list-style-type: none;
    margin-right: 20px;
    padding: 0!important;
  }
  &__index-list-item{
    display: flex;
    align-items: center;
    min-height: 50px;
    font-weight: bold;
    font-size: 18px;
    & + .hm-test-sorting__index-list-item{
      margin-top: 26px;
    }
  }
  &__draggable{
    width: 100%;
  }
}
</style>
