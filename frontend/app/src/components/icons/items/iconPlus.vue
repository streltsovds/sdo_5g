<template>
  <svg-icon-base v-bind="$props">
    <path d="M10 10H1H10ZM10 10V1V10ZM10 10H19H10ZM10 10V19V10Z" :stroke="color" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
  </svg-icon-base>
</template>

<script>
import VueMixinSvgIcon from "@/components/icons/items/VueMixinSvgIcon";

export default {
  mixins: [VueMixinSvgIcon],
  props: {
    // название файла иконки после icon, id (на англ.)
    name: {
      type: String,
      default: "Plus",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Добавить",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 20 20",
    },
    // width и height это размеры, в которые преобразуется координатная система (происходит zoom)
    width: {
      type: [Number, String],
      default: 20,
    },
    height: {
      type: [Number, String],
      default: 20,
    },
    color: {
      type: String,
      default: "#000",
    },
  },
};
</script>
