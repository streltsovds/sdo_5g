<template>
  <svg-icon-base v-bind="$props">
      <path fill-rule="evenodd" clip-rule="evenodd" d="M7.99975 0C10.3797 0 12.3159 1.93622 12.3159 4.31616C12.3159 6.69572 10.3797 8.63194 7.99975 8.63194C5.61982 8.63194 3.68359 6.69572 3.68359 4.31616C3.68359 1.93622 5.61982 0 7.99975 0ZM7.99999 0.589844C5.94538 0.589844 4.27409 2.2615 4.27409 4.31611C4.27409 6.37035 5.94538 8.04201 7.99999 8.04201C10.0546 8.04201 11.7259 6.37035 11.7259 4.31611C11.7259 2.2615 10.0548 0.589844 7.99999 0.589844Z" :fill="color"/>
      <path fill-rule="evenodd" clip-rule="evenodd" d="M1.92362 16.6736C0.978393 16.6736 0 15.9646 0 14.7789C0 13.5263 1.4373 10.2754 8 10.2754C14.5627 10.2754 16 13.5261 16 14.7789C16 15.9646 15.0218 16.6736 14.0764 16.6736H1.92362ZM15.4097 14.7793C15.4097 13.764 14.0484 10.8659 7.99977 10.8659C1.95118 10.8659 0.589844 13.764 0.589844 14.7793C0.589844 15.6267 1.277 16.0841 1.92339 16.0841H14.0761C14.7225 16.0841 15.4097 15.6267 15.4097 14.7793Z" :fill="color"/>
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
                default: "User",
            },
            // название иконки, рус.
            title: {
                type: String,
                default: "Пользователь",
            },
            // viewBox это координатная система minX, minY, width, height
            viewBox: {
                type: String,
                default: "0 0 16 18",
            },
            // width и height это размеры, в которые преобразуется координатная система (происходит zoom)
            width: {
                type: [Number, String],
                default: 16,
            },
            height: {
                type: [Number, String],
                default: 16,
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

