<template>
  <svg-icon-base v-bind="$props">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M21.0032 0H2.9968C1.34453 0 0 1.34453 0 2.9968V15.2152C0 16.8675 1.34453 18.2123 2.9968 18.2123H8.83867L7.19227 23.1509H4.92587C4.69147 23.1509 4.50133 23.3408 4.50133 23.5755C4.50133 23.8096 4.69147 24 4.92587 24H7.49813H16.5013H19.0736C19.308 24 19.4984 23.8096 19.4984 23.5755C19.4984 23.3408 19.308 23.1509 19.0736 23.1509H16.8075L15.1611 18.2123H21.0029C22.6555 18.2125 24 16.8677 24 15.2155V2.9968C24 1.34453 22.6555 0 21.0032 0ZM15.9128 23.1509H8.08721L9.73361 18.2122H14.2661L15.9128 23.1509ZM21.0029 17.3631C22.1877 17.3631 23.1509 16.3994 23.1509 15.2154V2.9967C23.1509 1.8119 22.1875 0.848437 21.0029 0.848437H2.9968C1.81253 0.848437 0.848796 1.81217 0.848796 2.9967V15.2154C0.848796 16.3996 1.81253 17.3631 2.9968 17.3631H21.0029Z" v-bind="pathAttrs"/>
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
      default: "ComputerMonitor",
    },
    // название иконки, рус.
    title: {
      type: String,
      default: "Компьютерный монитор",
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
