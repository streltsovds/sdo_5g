<template>
  <svg-icon-base v-bind="svgAttrs">
    <path d="M8 0L8 24" v-bind="shapeAttrs" />
    <path d="M16 0L16 24" v-bind="shapeAttrs" />
    <path d="M0 8L24 8" v-bind="shapeAttrs" />
    <path d="M0 16H24" v-bind="shapeAttrs" />
    <rect x="0.5" y="0.5" width="23" height="23" rx="2.5" fill="none" v-bind="shapeAttrs" />
  </svg-icon-base>
</template>

<script>
import VueMixinSvgIcon from "./VueMixinSvgIcon";

export default {
  mixins: [VueMixinSvgIcon],
  props: {
    // название файла иконки после icon, id (на англ.)
    name: {
      type: String,
      default: "ModeTable",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Таблица",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 24 24",
    },
    // См. width, height, color, strokeWidth в VueMixinSvgIcon
  },
};
</script>
