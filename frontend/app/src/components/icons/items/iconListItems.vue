<template>
  <svg-icon-base v-bind="$props">
    <path d="M1.75 2.5C2.16421 2.5 2.5 2.16421 2.5 1.75C2.5 1.33579 2.16421 1 1.75 1C1.33579 1 1 1.33579 1 1.75C1 2.16421 1.33579 2.5 1.75 2.5Z" :stroke="color" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M1.75 10C2.16421 10 2.5 9.66421 2.5 9.25C2.5 8.83579 2.16421 8.5 1.75 8.5C1.33579 8.5 1 8.83579 1 9.25C1 9.66421 1.33579 10 1.75 10Z" :stroke="color" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M1.75 17.5C2.16421 17.5 2.5 17.1642 2.5 16.75C2.5 16.3358 2.16421 16 1.75 16C1.33579 16 1 16.3358 1 16.75C1 17.1642 1.33579 17.5 1.75 17.5Z" :stroke="color" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M5.5 1.75H19" :stroke="color" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M5.5 9.25H19" :stroke="color" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M5.5 16.75H19" :stroke="color" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
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
      default: "ListItems",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Список",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 20 19",
    },
    // width и height это размеры, в которые преобразуется координатная система (происходит zoom)
    width: {
      type: [Number, String],
      default: 18,
    },
    height: {
      type: [Number, String],
      default: 18,
    },
    color: {
      type: String,
      default: "#000",
    },
  },
};
</script>
