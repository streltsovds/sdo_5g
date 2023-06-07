<template>
  <div class="demo-hm-typography">
    <v-toolbar class="demo-hm-typography__actions">
      <v-text-field v-model="sampleText" label="Текст примера" />
    </v-toolbar>

    <table class="demo-hm-typography__table">
      <tr v-for="cssSelector in cssSelectors" :key="cssSelector">
        <td class="demo-hm-typography__css-selector">{{ cssSelector }}</td>
        <td class="demo-hm-typography__sample" v-html="generateHtml(cssSelector)"></td>
      </tr>
    </table>

  </div>
</template>

<script>
import * as satisfy from "satisfy.js"

export default {
  name: "DemoHmTypography",
  props: {
    /** @see https://www.npmjs.com/package/satisfy.js */
    cssSelectors: {
      type: Array,
      default() { return [] },
    }
  },
  data() {
    return {
      sampleText: "Текст примера. Sample Text"
    };
  },
  computed: {
    tst() {
      return satisfy('span[innerHTML="item"]')[0].outerHTML;
    }
  },
  methods: {
    generateHtml(cssSelector) {
      return satisfy(cssSelector + '[innerHTML="' + this.sampleText + '"]')[0].outerHTML;
    }
  },
}
</script>

<style lang="sass">
.demo-hm-typography
  &__css-selector
    min-width: 220px
</style>
