<template>
  <v-card-text class="pb-2 pt-2 hm-test-mapping" style="padding-left: 28px; padding-bottom: 28px!important;">
    <ul>
      <li v-for="data in datas" :key="data.id">
        <v-layout class="hm-test-mapping_layout" wrap>
          <v-flex sm6 xs12>
            <span class="v-label theme--light" v-html="data.value" style="color: #1e1e1e; font-size: 16px; line-height: 24px; font-weight: 400" />
          </v-flex>
          <v-flex sm6 xs12>
            <v-select
              class="hm-test-mapping_select"
              :items="options"
              :value="resultFormatted[data.id]"
              :offset-y="true"
              @change="setAnswer(data.id, $event)"
              height="56"
              outlined
              hide-details
              label="Выберите соответствие"
            >
              <template slot="item" slot-scope="{ item }">
                <!-- eslint-disable -->
                <span v-if="variantsAsHtml" class="variant-with-html-markup" v-html="item.text" />
                <!-- eslint-enable -->
                <span v-else>
                  {{ item.text }}
                </span>
              </template>
            </v-select>
          </v-flex>
        </v-layout>
      </li>
    </ul>
  </v-card-text>
</template>

<script>
import { shuffleArray } from "../../../../utils";

export default {
        props: ["answers", "variantsAsHtml", "selectedAnswer", "highlight"], // eslint-disable-line
  data() {
    return {
      datas: [],
      options: [],
      result: []
    };
  },
  computed: {
    resultFormatted() {
      let formatted = {};
      this.result.forEach(res => {
        formatted[res.id] = res.value;
      });
      return formatted;
    }
  },
  watch: {
    resultFormatted() {
      this.sendAnswer();
    },
    selectedAnswer() {
      this.result.map(res => {
        if (this.selectedAnswer.hasOwnProperty(res.id)) {
          res.value = this.selectedAnswer[res.id];
        }
        return res;
      });
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
          value: answer.data
        });

        this.options.push({
          value: answer.question_variant_id,
          text: answer.variant
        });

        this.options = shuffleArray(this.options);

        this.result.push({
          id: answer.question_variant_id,
          value: null
        });
      });
    },
    setAnswer(id, answer) {
      let res = this.result.find(res => res.id === id);
      console.log(res);
      if (!res) return;

      res.value = answer;
    },
    sendAnswer() {
      this.$nextTick(() => {
        this.$emit("hm:test:answer-chosen", this.resultFormatted);
      });
    }
  }
};
</script>

<style lang="scss">
  .hm-test-mapping {
    ul {
      padding-left: 0;
      list-style: none;
      li {
        padding-top: 10px;
        padding-bottom: 26px;
        /*+ li {*/
        /*  border-top: 1px solid rgb(148, 148, 148);*/
        /*}*/
      }
    }
  }
  .hm-test-mapping_layout {
    align-items: center;
  }
  .hm-test-mapping_select {
    .v-messages {
      display: none;
    }
  }
  .v-list__tile {
    min-height: 48px;
    height: auto;
  }

  .hm-test-mapping_select {
    > .v-input__control {
      > .v-input__slot {
        border-width: 1px!important;
        > .v-select__slot {
          > label {
            padding: 0 12px;
          }
          > .v-select__selections {
            padding-top: 10px !important;
            padding-left: 10px !important;
            cursor: pointer;
          }
        }
      }
    }
  }
</style>
