<template>
  <v-navigation-drawer
    :key="sidebarName"
    :value="isShown"
    :class="cssClasses"
    :temporary="!shareWidthWithContent"
    :color="themeColors.sidebars"
    @input="handleChange"
    :width="width"
    app
    clipped
    disable-resize-watcher
    hide-overlay
    right
    touchless
  >
    {{ /* :class="$vuetify.breakpoint.mdAndUp ? 'elevation-5' : null" */ }}
    <slot />
  </v-navigation-drawer>
</template>

<script>
// import SvgIcon from "@/components/icons/svgIcon";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
export default {
    components: {
      // SvgIcon
    },
    mixins: [VueMixinConfigColors ],
    props: {
    sidebarName: {
      type: String,
      required: true,
    },
    width: {
      type: [String, Number],
      default: null,
    },
    shareWidthWithContent: {
      type: Boolean,
      default: true,
    },
    hoverable: Boolean,
    opened: {type: Boolean, default: false},
        // флаг больше ли width 1400
    openedWidth: {
        type: Boolean,
        default: false
    }
  },
  computed: {
    cssClasses() {
      let cls = ["hm-sidebar"];

      if (this.shareWidthWithContent) {
        cls.push("share-width-with-content");
      }

      return cls.join(" ");
    },
    isShown() {
      if (
        this.$store.getters["sidebars/sidebarItems"][this.sidebarName] !==
        undefined
      ) {
        return this.$store.getters["sidebars/sidebarItems"][this.sidebarName]
          .opened;
      } else {
        return false;
      }
    },
  },
  mounted() {
    if (this.openedWidth) {
      this.$store.dispatch("sidebars/registerSidebar", {
        name: this.sidebarName,
        options: {
          opened: this.opened
        }
      });
    } else {
      this.$store.dispatch("sidebars/registerSidebar", {
        name: this.sidebarName,
        options: {
          opened: false
        }
      });

    }
  },
  methods: {
    handleChange(isOpen) {
      if (isOpen) return;
      this.$store.dispatch("sidebars/changeSidebarState", {
        name: this.sidebarName,
        options: {
          opened: false
        }
      });
    },
  },

};
</script>

<style lang="scss">
.hm-sidebar.share-width-with-content {
  box-shadow: rgba(0, 0, 0, 0.12) 0 4px 5px, rgba(0, 0, 0, 0.14) 0 2px 4px;
}
</style>
