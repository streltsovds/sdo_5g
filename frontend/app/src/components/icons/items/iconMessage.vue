<template>
  <svg-icon-base v-bind="$props" v-on="$listeners">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M2.926 0H17.074C18.6873 0 20 1.31048 20 2.92162V11.6973C20 13.308 18.6873 14.619 17.0738 14.619H16.5378C16.3782 14.619 16.2484 14.7485 16.2484 14.9079V18.7603C16.2484 18.9667 16.2076 19.1384 16.1271 19.2718C16.0182 19.4522 15.8396 19.5556 15.6369 19.5556C15.4356 19.5556 15.2342 19.4566 15.0382 19.2609L10.5889 14.818C10.488 14.7177 10.2496 14.6192 10.1071 14.6192H2.926C1.31244 14.6192 0 13.3083 0 11.6976V2.92184C0 1.31048 1.31244 0 2.926 0ZM17.074 13.9123C18.2971 13.9123 19.2925 12.919 19.2925 11.6974V2.92195C19.2925 1.70044 18.2971 0.70638 17.074 0.70638H2.92602C1.70291 0.70638 0.707574 1.70044 0.707574 2.92195V11.6977C0.707574 12.919 1.70291 13.9126 2.92602 13.9126H10.1071C10.4354 13.9126 10.8571 14.0872 11.0891 14.3184L15.5389 18.7613C15.5398 18.762 15.5407 18.7628 15.5411 18.7637V18.7602V14.9077C15.5411 14.359 15.9882 13.9123 16.538 13.9123H17.074Z" v-bind="pathAttrs"/>
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
        default: "Message",
      },
      // название иконки, рус.
      title: {
        type: String,
        default: "Message",
      },
      // viewBox это координатная система minX, minY, width, height
      viewBox: {
        type: String,
        default: "0 0 20 20",
      },
      // См. width, height, color, strokeWidth в VueMixinSvgIcon
    },
  };
</script>