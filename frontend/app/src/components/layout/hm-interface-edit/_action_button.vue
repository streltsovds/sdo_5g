<template>
  <v-tooltip bottom>
    <template v-slot:activator="{ on: onTooltip }">
      <v-btn
        class="hm-interface-edit-action-button"
        :color="color"
        :class="targetClass"
        small
        fab
        text
        v-bind="$attrs"
        v-on="{ ...$listeners, ...onTooltip }"
      >
        <v-icon>{{ icon }}</v-icon>
      </v-btn>
    </template>
    <span>{{ _(tooltip) }}</span>
  </v-tooltip>
</template>

<script>
export default {
  name: "HmInterfaceEditActionButton",
  props: {
    tooltip: {
      type: String,
      default: null,
    },
    icon: {
      type: String,
      default: "add",
    },
    color: {
      type: String,
      default: "primary",
    },
    // vue 2 doesn't pass class in $attrs
    targetClass: {
      type: null,
      default: "",
    },
  },
  computed: {

  },
  beforeMount() {
    let sStop = 'here';
  },
};
</script>

<style lang="scss">
.hm-interface-edit-action-button {
  &.v-btn:hover:before {
    opacity: 0.16;
  }
}
</style>
