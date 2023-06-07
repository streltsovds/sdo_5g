<template>
    <svg-icon-base v-bind="$props">
      <path d="M11.25 1.8075L10.1925 0.75L6 4.9425L1.8075 0.75L0.75 1.8075L4.9425 6L0.75 10.1925L1.8075 11.25L6 7.0575L10.1925 11.25L11.25 10.1925L7.0575 6L11.25 1.8075Z"
            :fill="color"/>
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
      default: "Close",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Закрыть",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 12 12",
    },
    // width и height это размеры, в которые преобразуется координатная система (происходит zoom)
    width: {
      type: [Number, String],
      default: 24,
    },
    height: {
      type: [Number, String],
      default: 24,
    },
    color: {
      type: String,
      default: "#000",
    },
  },
};
</script>
