<template>
  <svg-icon-base v-bind="$props">
      <path d="M14.0156 3H21V9.98438H18.9844V6.42188L9.1875 16.2188L7.78125 14.8125L17.5781 5.01562H14.0156V3ZM18.9844 18.9844V12H21V18.9844C21 19.5156 20.7969 19.9844 20.3906 20.3906C19.9844 20.7969 19.5156 21 18.9844 21H5.01562C4.45312 21 3.96875 20.7969 3.5625 20.3906C3.1875 19.9844 3 19.5156 3 18.9844V5.01562C3 4.48438 3.1875 4.01562 3.5625 3.60938C3.96875 3.20312 4.45312 3 5.01562 3H12V5.01562H5.01562V18.9844H18.9844Z"
            v-bind="pathAttrs" />
  </svg-icon-base>
</template>

<script>
import svgIconBase from "./svgIconBase";
import VueMixinSvgIcon from "./VueMixinSvgIcon";

export default {
  components: {
    svgIconBase,
  },
  mixins: [VueMixinSvgIcon],
  props: {
    // название файла иконки после icon, id (на англ.)
    name: {
      type: String,
      default: "OpenNew",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Открыть",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 24 24",
    },
  },
};
</script>
