<template>
  <svg-icon-base v-bind="$props">
    <path d="M6 18.5315V23.114H18V18.5315V15.4766H6V18.5315Z" v-bind="pathAttrs"/>
    <path d="M18 4.78363V0.201172H6V4.78363V7.83861H18V4.78363Z" v-bind="pathAttrs"/>
    <path d="M21 5H19.5V8.05497V9.58246H4.5V8.05497V5H3C1.5 5 0 6.52749 0 8.05497V15.6924C0 17.2199 1.5 18.7474 3 18.7474H4.5V15.6924V14.1649H19.5V15.6924V18.7474H21C22.5 18.7474 24 17.2199 24 15.6924V8.05497C24 6.52749 22.5 5 21 5ZM22.5 10.499V11.1099H21V10.499V9.58246H22.5V10.499Z" v-bind="pathAttrs"/>
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
      default: "Printer",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Принтер",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 24 24",
    },
    // См. width, height, color, strokeWidth в VueMixinSvgIcon
  },
};
</script>
