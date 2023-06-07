<template>
  <v-card-text class="pb-2 pt-2">
    <v-radio-group
      :value="selectedOption"
      class="mt-0"
      style="padding-left: 14px"
      hide-details
      @change="handleChange"
    >
      <div v-for="(answer, i) in answers" :key="answer.question_variant_id">
        <v-radio
          :value="answer.question_variant_id"
          :class="
            highlightAnswers && answer.question_variant_id === selected
              ? answer.is_correct
                ? 'success elevation-1 v-radio--round-border'
                : 'error elevation-1 v-radio--round-border'
              : null
          "
          :color="
            highlightAnswers && answer.question_variant_id === selected
              ? 'white'
              : 'primary'
          "
          :dark="
            highlightAnswers && answer.question_variant_id === selected
              ? true
              : false
          "
          class="testings__options-between"
        >
          <!-- eslint-disable -->
          <div v-if="variantsAsHtml" class="variant-with-html-markup test__answer-options" slot="label" v-html="answer.variant">
          </div>
          <!-- eslint-enable -->
          <div v-else slot="label" class="test__answer-options">
            {{ answer.variant }}
          </div>
        </v-radio>
        <!--        <v-divider v-if="i !== answers.length - 1" class="mb-2"></v-divider>-->
      </div>
    </v-radio-group>
    <template v-if="showComment">
      <v-textarea v-model="comment" label="Комментарий"></v-textarea>
    </template>
  </v-card-text>
</template>

<script>
    export default {
        props: ["answers", "variantsAsHtml", "selectedAnswer", "highlight", "showComment"], // eslint-disable-line
        data() {
            return {
                selected: null,
                comment: null
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
            selected(val) {
                if (!isNaN(val)) {
                    this.$nextTick(() => {
                        this.$emit("hm:test:answer-chosen", val);
                    });
                }
            },
            comment(val) {
                this.$nextTick(() => {
                    this.$emit("hm:test:answer-comment", val);
                });
            }
        },
        methods: {
            handleChange(value) {
                this.selected = value;
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
</style>
