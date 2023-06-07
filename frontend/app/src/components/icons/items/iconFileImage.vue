<template>
  <svg-icon-base v-bind="$props">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M27.4111 0.667725H2.07639C1.29905 0.667725 0.668945 1.29797 0.668945 2.07542V24.5945C0.668945 25.3715 1.29905 26.0011 2.07639 26.0011H27.4111C28.1885 26.0011 28.8186 25.3715 28.8186 24.5945V2.07545C28.8186 1.29797 28.1884 0.667725 27.4111 0.667725ZM24.5954 21.7772H4.8909V14.7405L9.11327 10.5178L16.1508 17.5552L20.373 13.3323L24.5954 17.5552V21.7772ZM18.9667 7.70439C18.9667 9.25901 20.2268 10.5191 21.7815 10.5191C23.3362 10.5191 24.5964 9.25901 24.5964 7.70439C24.5964 6.14921 23.3361 4.88957 21.7815 4.88957C20.2268 4.88957 18.9667 6.14918 18.9667 7.70439Z" :fill="color"/>
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
      default: "FileImage",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Файл изображения",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 29 26",
    },
    // См. width, height, color, strokeWidth в VueMixinSvgIcon
  },
};
</script>
