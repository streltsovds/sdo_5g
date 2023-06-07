<template>
  <svg-icon-base v-bind="$props">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M25.9951 0.70008V0.679688H0.000429687V2.84591H0.00520867V7.10092H0V9.26714H0.00520867V15.8137H0V17.9799H0.00520867V24.5225H0.00501659V26.6887H0.00520867V26.6943H2.17143V26.6887H8.69909V26.6949H10.8653V26.6887H15.1544V26.6949H17.3207V26.6887H23.8331V26.6948H25.9993V26.6887H25.9997V24.5225H25.9993V0.70008H25.9951ZM23.8331 24.5225V17.9799H17.3207V24.5225H23.8331ZM15.1544 24.5225V17.9799H10.8653V24.5225H15.1544ZM8.69909 24.5225V17.9799H2.17143V24.5225H8.69909ZM2.17143 15.8137H8.69909V9.26714H2.17143V15.8137ZM2.17143 7.10092H23.8331V2.84591H2.17143V7.10092ZM23.8331 9.26714H17.3207V15.8137H23.8331V9.26714ZM15.1544 15.8137V9.26714H10.8653V15.8137H15.1544Z" :fill="color"/>
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
      default: "FileTable",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Табличный файл",
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
