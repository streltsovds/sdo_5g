<template>
  <svg-icon-base v-bind="$props" >
      <path fill-rule="evenodd" clip-rule="evenodd" d="M4.71167 5.57493C4.71167 2.50133 7.2122 0 10.2863 0C13.3605 0 15.861 2.50107 15.861 5.57493C15.861 8.6488 13.3605 11.1499 10.2863 11.1499C7.2122 11.1499 4.71167 8.6488 4.71167 5.57493ZM5.57568 5.57368C5.57568 8.17101 7.68902 10.2843 10.2864 10.2843C12.8834 10.2843 14.9968 8.17101 14.9968 5.57368C14.9968 2.97635 12.8834 0.863281 10.2864 0.863281C7.68902 0.863281 5.57568 2.97635 5.57568 5.57368Z" :fill="color"/>
      <path fill-rule="evenodd" clip-rule="evenodd" d="M0 18.9553C0 17.3382 1.848 13.1406 10.2864 13.1406C18.7248 13.1406 20.5723 17.3382 20.5725 18.9553C20.5725 20.506 19.2936 21.4334 18.0573 21.4334H2.5152C1.27867 21.4334 0 20.506 0 18.9553ZM2.51519 20.5679H18.0573C18.8576 20.5679 19.708 20.0026 19.7083 18.9543C19.7083 17.67 17.9773 14.0039 10.2864 14.0039C2.59519 14.0039 0.864258 17.67 0.864258 18.9543C0.864258 20.0026 1.71466 20.5679 2.51519 20.5679Z" :fill="color"/>
      <path d="M25.0732 5.05292C24.9183 4.95343 24.7126 4.99837 24.6136 5.15283L20.6221 11.3626C20.5353 11.4972 20.4214 11.5762 20.3014 11.5845C20.1804 11.5921 20.0582 11.5304 19.9534 11.4081L17.5851 8.64542C17.4658 8.50643 17.2557 8.48991 17.1161 8.60947C16.9767 8.72882 16.9606 8.93908 17.0799 9.07849L19.4483 11.8414C19.6734 12.1035 19.9733 12.2505 20.2807 12.2505C20.3029 12.2505 20.3249 12.2498 20.347 12.2479C20.6772 12.2252 20.9811 12.0335 21.1816 11.7219L25.1729 5.51275C25.2726 5.35808 25.2276 5.15241 25.0732 5.05292Z" :fill="color"/>
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
                default: "Destination",
            },
            // название иконки, рус.
            title: {
                type: String,
                default: "Назначения",
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

