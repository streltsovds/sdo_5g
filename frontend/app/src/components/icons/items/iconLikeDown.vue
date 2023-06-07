<template>
    <svg-icon-base v-bind="$props">
        <path d="M18.9844 0H23.0156V12H18.9844V0ZM15 0C15.5312 0 16 0.203125 16.4062 0.609375C16.8125 1.01562 17.0156 1.48438 17.0156 2.01562V12C17.0156 12.5312 16.8125 13 16.4062 13.4062L9.84375 20.0156L8.76562 18.9375C8.48438 18.6562 8.34375 18.3125 8.34375 17.9062V17.5781L9.32812 12.9844H3C2.46875 12.9844 2 12.7969 1.59375 12.4219C1.1875 12.0156 0.984375 11.5469 0.984375 11.0156L1.03125 10.9219H0.984375V9C0.984375 8.75 1.03125 8.5 1.125 8.25L4.17188 1.21875C4.48438 0.40625 5.09375 0 6 0H15Z" 
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
