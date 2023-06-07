<template>
  <div class="pdf-paginator">
    <v-tooltip-simple :text="_('Номер страницы')">
      <template v-if="pageCount">
          <input
            :value="value"
            @input="input"
            min="1"
            :max="pageCount"
            type="number"
            :style="{
              width: inputWidth + 'px',
            }"
          />
        <span class="pdf-paginator__separator">/</span>

        <span>{{ pageCount }}</span>
      </template>
      <input v-else type="number" />
    </v-tooltip-simple>
  </div>
</template>

<script>
import VTooltipSimple from "@/components/helpers/v-tooltip-simple";

let ONE_LETTER_WITH_PX = 10;

export default {
  name: 'PDFPaginator',

  components: {
    VTooltipSimple,
  },

  props: {
    value: Number,
    pageCount: Number,
  },

  computed: {
    inputWidth() {
      let { value } = this;
      return 20 + (value + '').length * ONE_LETTER_WITH_PX;
    },
  },

  methods: {
    input(event) {
      this.$emit('input', parseInt(event.target.value, 10));
    },
  },

  watch: {
    pageCount() {
      this.$emit('input', 1);
    },
  }
}
</script>

<style lang="scss">
.pdf-paginator {
  color: white;
  font-weight: bold;

  &__separator {
    padding: 0 6px;
  }

  input {
    /*width: 2em;*/
    /*padding: 0.3em;*/
    text-align: center;
    border-radius: 4px;
  }
}
</style>
