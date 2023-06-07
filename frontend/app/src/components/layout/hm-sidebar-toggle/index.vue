<template>
  <v-btn
    class="hm-sidebar-toggle"
    :class="{'open-is-show': isShown}"
    v-bind="{ ...componentProperties }"
    @click="toggleSidebar"
    ref="templateRoot"
    large
  >
    <slot />
    <slot name="notification"></slot>
    <v-tooltip class="hm-sidebar-toggle__tooltip"
               v-if="title"
               :activator="$refs.templateRoot"
               bottom
    >
      {{ title }}
    </v-tooltip>
  </v-btn>
</template>

<script>
export default {
  props: {
    sidebarName: {
      type: String,
      required: true
    },
    title: {
      type: String,
      default: '',
    },
    hasAvatar: Boolean,
  },
  computed: {
    isShown() {
      return (
        this.$store.getters["sidebars/sidebarItems"][this.sidebarName] !== undefined &&
        this.$store.getters["sidebars/sidebarItems"][this.sidebarName].opened
      );
    },
    componentProperties() {
      let props = {
        icon: true
      };
      if (this.hasAvatar) {
        props["class"] = { "": this.isShown };
      } else {
        props["color"] = this.isShown ? "accent lighten-1" : null;
      }
      return props;
    }
  },
  methods: {
    toggleSidebar() {
      this.$store.dispatch("sidebars/changeSidebarState", {
        name: this.sidebarName,
        options: {
          opened: !this.isShown
        }
      });
    }
  },
};
</script>

<style lang="scss">
.open-is-show {
    background: #942336;
}
</style>
