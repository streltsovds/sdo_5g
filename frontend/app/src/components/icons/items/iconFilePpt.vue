<template>
  <svg-icon-base v-bind="$props">
    <path opacity="0.4" d="M12.0608 2.55469C5.39797 2.55469 0 7.95556 0 14.6155C0 21.2754 5.40087 26.6763 12.0608 26.6763C18.7207 26.6763 24.1245 21.2754 24.1245 14.6126H12.0608V2.55469Z" :fill="color"/>
    <path d="M13.9395 0.671875V12.7356H26.0002C26.0031 6.07274 20.6023 0.671875 13.9395 0.671875H13.9395Z" :fill="color"/>
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
      default: "FilePpt",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Файл презентации",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 26 27",
    },
    // См. width, height, color, strokeWidth в VueMixinSvgIcon
  },
};
</script>
