<template>
  <component
    :is="iconComponent"
    v-bind="everything"
    v-on="$listeners"
    style="vertical-align: middle; overflow: visible;"
  >
    {{ /* overflow: visible; - для stroke */ }}
  </component>
</template>

<script>
import { capitalize } from './strings';
import Vue from 'vue';
import generateComponentAsync from './asyncComponentIcon';
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

// debug:
// console.log("itemsIcons:");
// console.log(itemsIcons);
// console.log("navMenuIcons:");
// console.log(navMenuIcons);

export default {
  name: "SvgIcon",
  components: {},

  inheritAttrs: false,

  props: {
    name: {
      type: String,
      default: "",
    },
  },
  data() {
    return {
      iconComponent: Vue.component | null
    }
  },
  watch: {
    name() {
      this.generateComponent();
    }
  },
  computed: {
    everything: function() {
      return {
        ...this.$props,
        ...this.$attrs,
      };
    },
  },
  methods: {
    async generateComponent() {
      const name = 'icon' + this.name
        .split('-')
        .map((i) => capitalize(i))
        .join('');

      this.iconComponent = await Vue.component(
        // 'icon',
        name,
        import(`./items/${name}`).then(m => {
          if(m) {
            return m.default
          }
        })
      );
    }
  },
  created() {
    this.generateComponent();
  }
};
</script>
