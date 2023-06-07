<template>
  <svg-icon-base v-bind="$props" v-on="$listeners">
      <path fill-rule="evenodd" clip-rule="evenodd" d="M0 12C0 5.38347 5.38347 0 12 0C18.6168 0 24 5.38347 24 12C24 18.6165 18.6165 24 12 24C5.38347 24 0 18.6165 0 12ZM0.849056 12.0002C0.849056 18.1487 5.85119 23.1511 12 23.1511C18.1488 23.1511 23.1509 18.1487 23.1509 12.0002C23.1509 5.85109 18.1488 0.848958 12 0.848958C5.85119 0.848958 0.849056 5.85109 0.849056 12.0002Z"
            v-bind="pathAttrs"/>
      <path d="M12 7.6716C12.4907 7.6716 12.8885 7.27379 12.8885 6.78306C12.8885 6.29234 12.4907 5.89453 12 5.89453C11.5093 5.89453 11.1115 6.29234 11.1115 6.78306C11.1115 7.27379 11.5093 7.6716 12 7.6716Z"
            v-bind="pathAttrs"/>
      <path d="M12 9.54492C11.7224 9.54492 11.4973 9.76999 11.4973 10.0476V17.6367C11.4973 17.9143 11.7224 18.1393 12 18.1393C12.2776 18.1393 12.5026 17.9143 12.5026 17.6367V10.0476C12.5026 9.76999 12.2776 9.54492 12 9.54492Z"
            v-bind="pathAttrs"/>
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
                default: "Info",
            },
            // название иконки, рус.
            title: {
                type: String,
                default: "Информация",
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
