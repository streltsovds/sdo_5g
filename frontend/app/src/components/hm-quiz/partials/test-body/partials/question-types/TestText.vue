<template>
  <v-card-text class="pb-2 pt-2">
    <v-text-field
      v-model="text"
      label="Введите ответ"
      :class="highlight ? (isCorrect ? 'success--text' : 'error--text') : null"
    ></v-text-field>
  </v-card-text>
</template>

<script>
    import DOMPurify from "dompurify";
    export default {
        props: ["answers", "selectedAnswer", "highlight"], // eslint-disable-line
        data() {
            return {
                text: this.selectedAnswer ? this.selectedAnswer : null
            };
        },
        computed: {
            cleanText() {
                return DOMPurify.sanitize(this.text);
            },
            isCorrect() {
                return this.answers.some(answer => answer.variant === this.cleanText);
            }
        },
        watch: {
            cleanText(val) {
                if (val.length <= 0) return;

                this.$nextTick(() => {
                    this.$emit("hm:test:answer-chosen", val);
                });
            },
            selectedAnswer(v) {
                if (v && v !== this.text) this.text = this.selectedAnswer;
            }
        }
    };
</script>

<style></style>
