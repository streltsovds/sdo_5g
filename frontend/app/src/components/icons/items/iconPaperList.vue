<template>
  <svg-icon-base v-bind="$props">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M38 0L63.3333 25.3333V67.1333C63.3333 72.0303 59.3636 76 54.4667 76H8.86667C3.96974 76 0 72.0303 0 67.1333V8.86667C0 3.96974 3.96974 0 8.86667 0H38Z" :fill="color"/>
    <path fill-rule="evenodd" clip-rule="evenodd" d="M63.334 25.332H47.3152C42.4182 25.332 38.0006 20.9145 38.0006 16.0175V-0.00130463L63.334 25.332Z" fill="white" fill-opacity="0.4"/>
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
      default: "PaperList",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Лист",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 64 76",
    },
    // См. width, height, color, strokeWidth в VueMixinSvgIcon
  },
};
</script>
