<template>
  <v-card-text class="pb-2 pt-2 hm-test-placeholder">
    <v-layout>
      <v-flex sm6 xs12>
        <body-placeholder :text="questText"
                          :components="sortingDatas"
                          @setValue="setValue"
                          :result="result"
        />
      </v-flex>
    </v-layout>
  </v-card-text>
</template>

<script>
import Vue from "vue";
import TextPlaceholder from "./partials/TextPlaceholder";
import SelectPlaceholder from "./partials/SelectPlaceholder";
import BodyPlaceholder from "./partials/BodyPlaceholder";

export default { // eslint-disable-line
  components: { 
    TextPlaceholder,
    SelectPlaceholder,
    BodyPlaceholder
  },
  props: ["answers", "selectedAnswer", "highlight","questText"],
  data() {
    return {
      datas: [],
      mode: {
        0: {
          component: "TextPlaceholder"
        },
        1: {
          component: "SelectPlaceholder"
        },
        2: {
          component: "SelectPlaceholder",
          options: {
            multiple: true
          }
        }
      },
      result: {}
    };
  },
  computed: {
    sortingDatas() {
      let datas = JSON.parse(JSON.stringify(this.datas));
      return datas.sort((a, b) => {
        if (a.id < b.id) return -1;
        if (a.id > b.id) return 1;

        return 0;
      });
    },
    allPlaceholderSelected() {
      return this.datas.every(
        data =>
          this.result.hasOwnProperty(data.id) &&
          this.result[data.id] &&
          this.result[data.id].length > 0
      );
    }
  },
  watch: {
    allPlaceholderSelected(v) {
      if (!v) return;
      this.sendAnswer();
    },
    selectedAnswer(selectedAnswer) {
      if (selectedAnswer !== this.result) this.result = selectedAnswer;
    }
  },
  mounted() {
    this.init();
  },
  methods: {
    init() {
      this.answers.forEach(answer => {
        this.datas.push({
          id: answer.question_variant_id,
          variants: this.variantsFormatting(answer.variant),
          mode: this.getMode(answer.data)
        });

        this.$set(this.result, answer.question_variant_id, null);
      });
    },
    sendAnswer() {
      this.$nextTick(() => {
        this.$emit("hm:test:answer-chosen", this.result);
      });
    },
    variantsFormatting(variants) {
      return variants.split(";").map(variant => {
        let isCorrect = variant.charAt(0) !== "^";
        return {
          value: isCorrect ? variant : variant.substring(1),
          isCorrect: isCorrect
        };
      });
    },
    getMode(data) {
      // TODO: исправить получение mode
      let modeId = data.substr(30, 1);
      return this.mode.hasOwnProperty(modeId) ? this.mode[modeId] : null;
    },
    setValue(id, value) {
      this.result[id] = value;
    }
  }
};
</script>

<style lang="scss">
.hm-test-placeholder {
  ul {
    list-style: none;
    padding-left: 0;
  }
  li + li {
    margin-top: 15px;
  }
}
</style>
