<template>
  <div 
    v-if="Object.keys(data).length > 0"
    class="hm-recommended-material"
    :style="{
    backgroundColor: colorBackground,
    color: colorText,
    height: '400px',
    }"
  >
    <div class="hm-recommended-material__image"
         :style="{
           backgroundImage: 'url(' + data.image + ')',
         }"
    >
      <div class="hm-recommended-material__image__gradient"
           :style="{
             background: imageGradient,
           }"
      >

      </div>
    </div>
    <div class="hm-recommended-material__info">
      <div class="hm-recommended-material__title">
        {{ data.title }}
      </div>
      <div class="hm-recommended-material__description">
        {{ data.description }}
      </div>

      <div class="hm-recommended-material__bar">
        <v-btn class="hm-recommended-material__button white--text"
               :color="colorButton"
               :href="data.url"
               target="_blank"
        >
          Читать далее
        </v-btn>
        <div class="hm-recommended-material__tags ml-3">
          <swiper class="swiper" :options="swiperOptions">
            <swiper-slide v-for="(tag, i) in data.tags" :key="i">
              <span class="hm-recommended-material__tag"># {{ tag }}</span>
            </swiper-slide>
          </swiper>
        </div>
      </div>
    </div>
  </div>
  <hm-empty v-else>
    {{ _('Нет данных для отображения') }}
  </hm-empty>
</template>

<script>
import colorAlpha from 'color-alpha'
import getBestContrastColor from 'get-best-contrast-color';
import sample from 'lodash/sample'
import Vue from 'vue';
import Vibrant from 'node-vibrant';
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import { swiper, swiperSlide } from "vue-awesome-swiper";
import HmEmpty from "@/components/helpers/hm-empty"

const Color = require('color');

export default Vue.extend({
  name: 'HmRecommendedMaterial',
  components: {swiper, swiperSlide, HmEmpty},
  mixins: [VueMixinConfigColors],
  props: {
    data: {
      type: Object,
      default: () => {},
    },
  },
  data: () => {
    return {
      colorBackgroundCalculated: null,
      colorsBackground: [
        "#FAF3D8",
        // "#8293A3"
        "#4A6073",
        "#B2DCED",
        "#99D9BD"
      ],
      colorsText: [
        "#1E1E1E",
        "#FFFFFF"
      ],
      swiperOptions: {
        freeMode: true,
        initialSlide: 0,
        slidesPerView: "auto",
        spaceBetween: 10,
        },
    };
  },
  computed: {
    colorButton() {
      return this.colors.buttonDefault || "#999";
    },
    colorText() {
      return getBestContrastColor(this.colorBackground, this.colorsText);
    },
    colorBackground() {
      // return "#4A6073";
      return this.colorBackgroundCalculated || sample(this.colorsBackground) || "#4A6073";
    },
    imageGradient() {
      let col = this.colorBackground;

      return 'linear-gradient(0deg, '
        + col + ' 14%, '
        + colorAlpha(col, 0.8) + ' 44%, '
        + colorAlpha(col, 0.5) + ' 72%, '
        + colorAlpha(col, 0) + ' 100%'
        + ')';
    }
  },
  mounted() {
    this.colorBackgroundUpdate()
  },
  methods: {
    async colorBackgroundUpdate() {
      if (!this.image) {
        return;
      }

      this.colorBackgroundCalculated = await this.colorBackgroundFromImage(this.image);
    },

    /** https://jariz.github.io/vibrant.js/ */
    async colorBackgroundFromImage(image) {
      let palette = await Vibrant.from(image).getPalette();
      console.log('HmRecommendedMaterial: palette from image:', palette);

      let colorObj = palette.DarkVibrant;

      if (!colorObj) {
        return null;
      }

      let colorHex = colorObj.getHex()

      // return colorHex;
      // return Color(colorHex).lighten(1.2).desaturate(0.3).hex();
      return Color(colorHex).desaturate(0.5).hex();
    },
  }
});
</script>

<style lang="sass" src="./styles.sass"/>
