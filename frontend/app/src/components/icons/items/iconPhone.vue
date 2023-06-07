<template>
  <svg-icon-base v-bind="$props">
      <path d="M1.81812 1.97303L4.52645 1.34811C4.82109 1.28012 5.12226 1.4328 5.24146 1.7109L6.49146 4.62749C6.60115 4.88343 6.52828 5.18108 6.31276 5.35741L4.73419 6.64897C5.67099 8.64572 7.3107 10.308 9.35005 11.2648L10.6416 9.68626C10.8179 9.47074 11.1156 9.39787 11.3715 9.50756L14.2881 10.7576C14.5661 10.8767 14.7189 11.1779 14.6509 11.4726L14.026 14.1809C13.961 14.4623 13.71 14.6654 13.417 14.6654C6.74807 14.6654 1.33367 9.26087 1.33367 2.58202C1.33367 2.29098 1.53455 2.03845 1.81812 1.97303Z" :fill="color"/>
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
      default: "Phone",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Телефон",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 16 16",
    },
    // См. width, height, color, strokeWidth в VueMixinSvgIcon
  },
};
</script>
