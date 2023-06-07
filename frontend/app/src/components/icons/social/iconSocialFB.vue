<template>
  <svg-icon-base v-bind="$props">
      <rect :width="size" :height="size" rx="18" :fill="color"/>
      <path d="M19.2018 29.9999V19.0523H22.8765L23.4267 14.7859H19.2018V12.0619C19.2018 10.8267 19.5449 9.98482 21.3163 9.98482L23.5756 9.98377V6.16793C23.1847 6.11613 21.8437 6 20.2835 6C17.0262 6 14.7961 7.98818 14.7961 11.6396V14.786H11.1119V19.0524H14.796V30L19.2018 29.9999Z" fill="white"/>
  </svg-icon-base>
</template>

<script>
    import svgIconBase from "../items/svgIconBase";

    export default {
        components: {
            svgIconBase,
        },
        props: {
            // название файла иконки после icon, id (на англ.)
            name: {
                type: String,
                default: "FaceBook",
            },
            // название иконки, рус.
            title: {
                type: String,
                default: "Адаптация",
            },
            // viewBox это координатная система minX, minY, width, height
            viewBox: {
                type: String,
                default: "0 0 24 24",
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
                default: "inherit",
            },
        },
    };
</script>

<style scoped>

</style>

