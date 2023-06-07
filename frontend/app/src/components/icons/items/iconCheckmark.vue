<template>
    <svg-icon-base v-bind="$props">
        <path d="M20.1626 0.699285L8.84043 12.8047L3.9271 6.80082C3.20271 5.91406 1.83696 5.73825 0.874067 6.40026C-0.0910516 7.06227 -0.287042 8.31629 0.437105 9.19898L6.98236 17.1989C7.37854 17.6851 7.99444 17.978 8.65705 17.9977C8.68052 18 8.704 18 8.72748 18C9.36241 18 9.96966 17.746 10.3829 17.3008L23.4734 3.30089C24.2596 2.46284 24.1531 1.20135 23.2369 0.482693C22.3249 -0.236188 20.9468 -0.142392 20.1626 0.699285Z" :fill="color"/>
    </svg-icon-base>
</template>

<script>
import svgIconBase from "./svgIconBase";

export default {
  components: {
    svgIconBase,
  },
  props: {
    // название файла иконки после icon, id (на англ.)
    name: {
      type: String,
      default: "checkmark",
    },
    // viewBox это координатная система minX, minY, width, height
    viewBox: {
      type: String,
      default: "0 0 24 18",
    },
    // width и height это размеры, в которые преобразуется координатная система (происходит zoom)
    width: {
      type: [Number, String],
      default: 24,
    },
    height: {
      type: [Number, String],
      default: 24,
    },
    color: {
      type: String,
      default: "#000",
    },
  },
};
</script>
