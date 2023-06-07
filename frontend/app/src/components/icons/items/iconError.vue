<template>
  <svg-icon-base v-bind="$props">
      <rect x="2" y="2" width="20" height="20" rx="1" :fill="color"/>
      <path d="M12 13.4453C11.1179 13.4453 10.4028 14.1694 10.4028 15.0625C10.4028 15.9556 11.1179 16.6797 12 16.6797C12.8821 16.6797 13.5972 15.9556 13.5972 15.0625C13.5972 14.1694 12.8821 13.4453 12 13.4453Z" fill="white"/>
      <path d="M10.4836 7.63237L10.7412 12.4136C10.7532 12.6374 10.9359 12.8125 11.1572 12.8125H12.8428C13.0641 12.8125 13.2468 12.6374 13.2589 12.4136L13.5164 7.63237C13.5295 7.3907 13.3394 7.1875 13.1004 7.1875H10.8996C10.6606 7.1875 10.4706 7.3907 10.4836 7.63237Z" fill="white"/>
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
      default: "error",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "сообщение с ошибкой",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 24 24",
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
      default: "#E31F28",
    },
  },
};
</script>
