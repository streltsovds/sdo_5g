<template>
  <component
    :is="iconComponentName"
    v-bind="everything"
    v-on="$listeners"
    style="vertical-align: middle; overflow: visible;"
  >
    {{ /* overflow: visible; - для stroke */ }}
  </component>
</template>

<script>
import lessonsType from "./type/lessonsType"
/**
     *
     Use svg icons by name:

     ```
     <svg-icon
     name="enter"
     :color="color"
     style="margin-right: 10px"
     >
     </svg-icon>
     ```

     */

export default {
  name: "SvgTypeLesson",
  components: {
    ...lessonsType
  },
  props: {
    name: {
      type: String,
      default: "",
    },
  },
  computed: {
    iconComponentName: function() {
      return "icon-les-" + this.name;
    },
    everything: function() {
      return {
        ...this.$props,
        ...this.$attrs,
      };
    },
  },
};
</script>
