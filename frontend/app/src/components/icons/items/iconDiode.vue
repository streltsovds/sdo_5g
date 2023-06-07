<template>
  <svg-icon-base v-bind="$props">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M4 8.0542C6.20914 8.0542 8 6.25121 8 4.0271C8 1.80299 6.20914 0 4 0C1.79086 0 0 1.80299 0 4.0271C0 6.25121 1.79086 8.0542 4 8.0542Z" :fill="color"/>
  </svg-icon-base>
</template>

<script>
import svgIconBase from "./svgIconBase";

export default {
  components: {
    svgIconBase,
  },
  props: {
    // название файла иконки после icon, id (на англ.)
    name: {
      type: String,
      default: "Diode",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Индикатор",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 8 9",
    },
    // width и height это размеры, в которые преобразуется координатная система (происходит zoom)
    width: {
      type: [Number, String],
      default: 9,
    },
    height: {
      type: [Number, String],
      default: 9,
    },
    color: {
      type: String,
      default: "#000",
    },
  },
};
</script>
