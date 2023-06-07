<template>
  <svg-icon-base v-bind="$props">
    <rect x="0.5" y="0.5" width="23" height="5" rx="1.5" fill="none" v-bind="shapeAttrs" />
    <rect x="0.5" y="9.5" width="23" height="5" rx="1.5" fill="none" v-bind="shapeAttrs" />
    <rect x="0.5" y="18.5" width="23" height="5" rx="1.5" fill="none" v-bind="shapeAttrs" />
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
      default: "ModeList",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Список",
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
