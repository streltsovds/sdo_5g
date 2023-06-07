<template >
  <svg-icon-base v-bind="$props" >
      <path fill-rule="evenodd" clip-rule="evenodd" d="M2.99707 24H15.8584C17.5109 24 18.8552 22.6555 18.8552 21.0032V13.2861V6.72693C18.8552 6.33333 18.6456 5.82693 18.3672 5.54853L13.3072 0.488267C13.0291 0.209867 12.5221 0 12.1288 0H5.56907H2.9968C1.34427 0 0 1.34453 0 2.9968V21.0029C0 22.6555 1.34453 24 2.99707 24ZM13.0677 1.44959L13.068 1.44986V4.92666C13.068 5.40159 13.4541 5.78773 13.9293 5.78773H17.4064L13.068 1.44986V1.44959H13.0677ZM2.99712 0.847656C1.81259 0.847656 0.849121 1.81139 0.849121 2.99566H0.849388V21.0018C0.849388 22.1863 1.81312 23.1498 2.99739 23.1498H15.8587C17.0435 23.1498 18.0067 22.1861 18.0067 21.0018V13.285V6.72552C18.0067 6.69806 18.0027 6.66766 17.9971 6.63566H13.9297C12.9862 6.63566 12.219 5.86819 12.219 4.92499V0.857256C12.187 0.851923 12.1563 0.847656 12.1291 0.847656H5.56939H2.99712Z" :fill="color"/>
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
                default: "Material",
            },
            // название иконки, рус.
            title: {
                type: String,
                default: "Материалы",
            },
            // viewBox это координатная система minX, minY, width, height
            viewBox: {
                type: String,
                default: "0 0 20 24",
            },
            // width и height это размеры, в которые преобразуется координатная система (происходит zoom)
            width: {
                type: [Number, String],
                default: 20,
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

<style lang="scss">

</style>

