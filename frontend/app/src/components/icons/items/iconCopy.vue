<template>
  <svg-icon-base v-bind="$props">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M18.9245 3.7432H16.7917V2.9968C16.7917 1.34453 15.4475 0 13.7949 0H2.99707C1.34453 0 0 1.34453 0 2.9968V17.26C0 18.9123 1.34453 20.2571 2.9968 20.2571H5.1296V21.0029C5.12987 22.6555 6.47413 24 8.12667 24H18.9245C20.5768 24 21.9213 22.6555 21.9213 21.0032V6.73973C21.9216 5.08747 20.5771 3.7432 18.9245 3.7432ZM2.99707 19.4076C1.81307 19.4076 0.849333 18.4442 0.849333 17.2599V2.9967C0.849333 1.81243 1.8128 0.848698 2.99707 0.848698H13.7949C14.9795 0.848698 15.9429 1.81216 15.9429 2.9967V17.2599C15.9429 18.4439 14.9797 19.4076 13.7949 19.4076H2.99707ZM18.9245 23.1511C20.1088 23.1511 21.0723 22.1874 21.0723 21.0031V6.73992C21.0723 5.55565 20.1085 4.59219 18.9245 4.59219H16.7917V17.2602C16.7917 18.9125 15.4475 20.2573 13.7949 20.2573H5.9784V21.0031C5.9784 22.1877 6.94213 23.1511 8.12667 23.1511H18.9245Z" v-bind="pathAttrs"/>
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
      default: "Copy",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Копировать",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 22 24",
    },
    // См. width, height, color, strokeWidth в VueMixinSvgIcon
  },
};
</script>
