<template>
<!--  <v-alert v-bind="alertProps">-->
<!--    {{ alertText }}-->
<!--  </v-alert> -->
  <hm-empty
    :empty-type="emptyType"
    :label="label"
    :subLabel="subLabel"
  >
  </hm-empty>
</template>

<script>
import HmEmpty from "@/components/helpers/hm-empty";

export default {
  components: {
    HmEmpty,
  },
  props: {
    emptyType: {
      type: String,
      default: 'full'
    },
    label: {
      type: String,
      default: 'Нет данных'
    },
    subLabel: {
      type: String,
      default: 'Попробуйте изменить поисковый запрос'
    },
  },
  computed: {
  }
};
</script>

<style></style>
