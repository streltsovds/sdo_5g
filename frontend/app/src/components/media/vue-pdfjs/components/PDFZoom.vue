<template>
  <div class="pdf-zoom">
    <a @click.prevent.stop="zoomIn" class="icon" :disabled="isDisabled">
      <v-tooltip-simple :text="_('Увеличить масштаб')">
        <svg-icon name="plus" :color="colorButton" title="" />
      </v-tooltip-simple>
    </a>

    <a @click.prevent.stop="zoomOut" class="icon" :disabled="isDisabled">
      <v-tooltip-simple :text="_('Уменьшить масштаб')">
        <svg-icon name="minus" :color="colorButton" title="" />
      </v-tooltip-simple>
    </a>

    <a @click.prevent.stop="fitWidth" class="icon" :disabled="isDisabled">
      <v-tooltip-simple :text="_('Уместить по ширине')">
        <svg-icon name="expand" :color="colorButton" title="" />
      </v-tooltip-simple>
    </a>

    <a @click.prevent.stop="fitAuto" class="icon" :disabled="isDisabled">
      <v-tooltip-simple :text="_('Уместить по высоте')">
        <svg-icon name="shrink" :color="colorButton" title="" />
      </v-tooltip-simple>
    </a>
  </div>
</template>

<script>
// import ZoomInIcon from '../assets/icon-zoom-in.svg';
// import ZoomOutIcon from '../assets/icon-zoom-out.svg';
// import ExpandIcon from '../assets/icon-expand.svg';
// import ShrinkIcon from '../assets/icon-shrink.svg';
import SvgIcon from '@/components/icons/svgIcon';
import VTooltipSimple from '@/components/helpers/v-tooltip-simple';

export default {
  name: 'PDFZoom',

  components: {
    SvgIcon,
    VTooltipSimple,
    // ZoomInIcon,
    // ZoomOutIcon,
    // ExpandIcon,
    // ShrinkIcon,
  },

  props: {
    scale: {
      type: Number,
    },
    increment: {
      type: Number,
      default: 0.25,
    },
  },

  computed: {
    colorButton() {
      return '#FFFFFF';
    },
    isDisabled() {
      return !this.scale;
    },
  },

  methods: {
    zoomIn() {
      this.updateScale(this.scale + this.increment);
    },

    zoomOut() {
      if (this.scale <= this.increment) return;
      this.updateScale(this.scale - this.increment);
    },

    updateScale(scale) {
      this.$emit('change', {scale});
    },

    fitWidth() {
      this.$emit('fit', 'width');
    },

    fitAuto() {
      this.$emit('fit', 'auto');
    },
  },
}
</script>

<style lang="scss">
.pdf-zoom a {
  float: left;
  cursor: pointer;
  display: block;
  border: 1px #333 solid;
  background: white;
  color: #333;
  font-weight: bold;
  line-height: 1.5em;
  width: 1.5em;
  height: 1.5em;
  font-size: 1.5em;
}
</style>
