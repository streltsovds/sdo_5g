<template>
  <svg-icon-base v-bind="$props">
    <path d="M17.7441 6.35247V10.8088L25.9995 15.7328V1.44141L17.7441 6.35247Z" :fill="color"/>
    <path fill-rule="evenodd" clip-rule="evenodd" d="M1.52106 0.0117188H18.7597C19.6048 0.0117188 20.2808 0.687314 20.2938 1.51882V15.6544C20.2938 16.4988 19.6048 17.1744 18.7727 17.1744H1.52106C0.676027 17.1744 0 16.4859 0 15.6544V1.53181C0 0.687314 0.689027 0.0117188 1.52106 0.0117188ZM15.2623 8.58692L6.57799 3.26011V13.9137L15.2623 8.58692Z" :fill="color"/>
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
      default: "FileVideo",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Видеофайл",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 26 18",
    },
    // См. width, height, color, strokeWidth в VueMixinSvgIcon
  },
};
</script>
