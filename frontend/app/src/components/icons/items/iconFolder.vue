<template>
    <svg-icon-base v-bind="$props" v-on="$listeners">
        <path d="M 3 2 C 1.34375 2 0 3.34375 0 5 L 0 22 C 0 23.65625 1.34375 25 3 25 L 23 25 C 24.65625 25 26 23.65625 26 22 L 26 8 C 26 6.34375 24.65625 5 23 5 L 11 5 C 11 3.34375 9.65625 2 8 2 Z M 3 7 L 23 7 C 23.550781 7 24 7.449219 24 8 L 24 22 C 24 22.550781 23.550781 23 23 23 L 3 23 C 2.449219 23 2 22.550781 2 22 L 2 8 C 2 7.449219 2.449219 7 3 7 Z" v-bind="pathAttrs"/>
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
                default: "Folder",
            },
            // название иконки, рус.
            title: {
                type: String,
                default: "Папка",
            },
            // viewBox это координатная система minX, minY, width, height
            viewBox: {
                type: String,
                default: "0 0 26 26",
            },
            // См. width, height, color, strokeWidth в VueMixinSvgIcon
        },
    };
</script>