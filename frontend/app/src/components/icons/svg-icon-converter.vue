<template>
  <v-card class="svg-icon-converter">
    <p>
      <small>
        Скопируйте результат в
        <code>frontend/app/src/components/icons/items/icon{{ inputIconName ? inputIconName : '*' }}.vue</code>
      </small>
    </p>
    <v-text-field
      :value="inputIconName"
      @input="inputIconNameChanged"
      label="icon name"
    >
    </v-text-field>
    <v-textarea
      v-model="inputSvg"
      label="svg"
    />
    <v-textarea
      v-model="resultVue"
      label="vue file result"
      rows="20"
    />
  </v-card>
</template>
<script>
import Vue from "vue";
import debounce from 'lodash/debounce';
import lodashStartCase from 'lodash/startCase';
import * as xml2js from 'xml2js';

export const DEBOUNCE_RESULT_DELAY_MS = 700;

export default {
  name: "SvgIconConverter",
  data() {
    return {
      inputSvg: '',
      inputIconName: '',
      resultVue: '',
    }
  },
  watch: {
    inputSvg() {
      this.updateResult();
    },
    inputIconName() {
      this.updateResult();
    },
  },
  methods: {
    inputIconNameChanged(newIconName) {
      /** no whitespace */
      this.inputIconName = lodashStartCase(newIconName).replace(/\s/g, '');
    },
    _updateResult() {
      let prefix = 'SvgIconConverter.updateResult:';

      let {inputSvg, inputIconName} = this;

      xml2js.parseString(this.inputSvg, (svgParseError, svgParseResult) => {
        console.log(svgParseResult)
        if (svgParseError) {
          console.error()
          return;
        }

        /** copy */
        let svgTag = svgParseResult.svg;

        let svgViewBox = svgTag.$.viewBox;

        console.log(prefix, 'svgParseResult:', svgParseResult);
        console.log(prefix, 'svgViewBox:', svgViewBox);

        let pathCopy = svgTag.path.map((pathEl) => {
          let newPathEl = JSON.parse(JSON.stringify(pathEl))
          delete newPathEl.$.fill;
          newPathEl.$['v-bind'] = "pathAttrs";
          return newPathEl;
        });

        let vueTemplate = {
          'template': {
            'svg-icon-base': {
              ...JSON.parse(JSON.stringify(svgTag)),
              $: {
                'v-bind': "$props",
                'v-on': "$listeners",
              },
              path: pathCopy,
            }
          }
        };

        let xmlBuilder = new xml2js.Builder({headless: true});

        let vueTemplateText = xmlBuilder.buildObject(vueTemplate);

        //eslint-disable-line
        this.resultVue = `${vueTemplateText} <script>
import VueMixinSvgIcon from "./VueMixinSvgIcon";

export default {
  mixins: [VueMixinSvgIcon],
  props: {
    // название файла иконки после icon, id (на англ.)
    name: {
      type: String,
      default: "${inputIconName}",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "${inputIconName}",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "${svgViewBox}",
    },
    // См. width, height, color, strokeWidth в VueMixinSvgIcon
  },
};
` +
          /** https://stackoverflow.com/a/4127508 */
          //eslint-disable-next-line
`<\/script>`
      });
    },
    updateResult: debounce(function () {
      this._updateResult();
    }, DEBOUNCE_RESULT_DELAY_MS)
  },
}
</script>
<style lang="scss">
.svg-icon-converter {
  .v-textarea textarea {
    font-size: 12px;
    line-height: 20px
  }
}
</style>
