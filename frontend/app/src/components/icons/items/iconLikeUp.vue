<template>
    <svg-icon-base v-bind="$props">
        <path d="M23.0156 9.98438L22.9688 10.0781H23.0156V12C23.0156 12.25 22.9688 12.5 22.875 12.75L19.8281 19.7812C19.5156 20.5938 18.9062 21 18 21H9C8.46875 21 8 20.7969 7.59375 20.3906C7.1875 19.9844 6.98438 19.5156 6.98438 18.9844V9C6.98438 8.46875 7.1875 8 7.59375 7.59375L14.1562 0.984375L15.2344 2.0625C15.5156 2.34375 15.6562 2.6875 15.6562 3.09375V3.42188L14.6719 8.01562H21C21.5312 8.01562 22 8.21875 22.4062 8.625C22.8125 9 23.0156 9.45312 23.0156 9.98438ZM0.984375 21V9H5.01562V21H0.984375Z" 
            :fill="color"/>
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
                default: "Close",
            },
            // название иконки, рус.
            title: {
                type: String,
                default: "Закрыть",
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
                default: "#000",
            },
        },
    };
</script>

<style scoped>

</style>
