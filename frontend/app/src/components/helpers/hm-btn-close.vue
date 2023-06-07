<template>
  <v-tooltip bottom>
    <template v-slot:activator="{ on: onTooltip }">
      <v-btn class="hm-rubricator-grid-button__close"
             v-on="{...onTooltip, ...$listeners}"
             v-bind="{...$attrs, ...$props}"
             icon
      >
        <icon-close
          name="close"
          width="14"
          title=""
        ></icon-close>
      </v-btn>
    </template>

    <span>{{ _('Закрыть') }}</span>
  </v-tooltip>
</template>

<script>
import iconClose from "@/components/icons/items/iconClose";

export default {
  name: "HmBtnClose",
  components: { iconClose },
  // v-bind вручную
  inheritAttrs: false,
};
</script>

<style lang="scss">

</style>
