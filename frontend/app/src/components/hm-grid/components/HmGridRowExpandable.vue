<template>
  <v-expansion-panels class="hm-grid-row-expandable">
    <v-expansion-panel
      v-model="expanded"
      class="ma-0"
    >
        <div class="hm-grid-row-expandable__colorful" :style="{backgroundColor: backgroundColor}">
            <v-expansion-panel-header
              :style="{
                color: _colorGridRowExpandable,
              }"
              @click.native.stop="handleClick"
              expand-icon="mdi-menu-down"
            >
                <hm-dependency
                    :template="label"
                    class="lessFormatedHtml"
                    :backgroundColor="backgroundColor"
                  ></hm-dependency>
            </v-expansion-panel-header>
        </div>
        <v-expansion-panel-content>
        <template v-for="(item, i) in shownItems">
          <div :key="i" class="lessHtml mt-1" v-html="item.outerHTML"></div>
        </template>
      </v-expansion-panel-content>
    </v-expansion-panel>
  </v-expansion-panels>
</template>

<script>
import configColors from "@/utilities/configColors";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import HmDependency from "@/components/helpers/hm-dependency";

export default {
  components: {
    HmDependency,
  },
  mixins: [VueMixinConfigColors],
  props: {
    items: {
      type: Array,
      default: () => [],
    },
    backgroundColor: {
        type: String,
        default: "primary"
    },
  },
  data() {
    return {
      expanded: null,
    };
  },
  computed: {
    label() {
      return [...this.items].shift().textContent;
    },
    shownItems() {
      return [...this.items].filter((_, i) => i !== 0);
    },
    _colorGridRowExpandable() {
      return this.getColor(configColors.primarySaturated);
    },
  },
  methods: {
    handleClick(e) {
      // console.log(e.target);
    },
  },
};
</script>

<style lang="scss">
.hm-grid-row-expandable {
  .v-expansion-panel:before {
    box-shadow: none;
  }
  .v-expansion-panel {
    background-color: transparent !important;
      &-content {
        padding-left: 10px;
      }
  }
  * {
    transition: none !important;
  }
  .v-expansion-panel-header__icon {
    user-select: none;
  }
  .v-expansion-panel-content__wrap {
    padding: 0 0 12px 0;
    font-size: 12px;
    line-height: 18px;
  }
  .v-expansion-panel__container:not(.v-expansion-panel__container--active) {
    & .v-expansion-panel__body {
      display: none !important;
    }
  }
  .v-expansion-panel__body {
    padding: 0.5rem;
  }
  .v-expansion-panel-header {
    padding: 0;
    font-size: 14px;
    min-height: 40px;
    white-space: pre;
  }
  & .lessHtml {
    & p:only-child {
      margin: 0;
    }
  }
  &__colorful {
      padding: 0 6px;
      border-radius: 6px;
      margin-left: 5px;

      .v-expansion-panel-header {
          min-height: 20px;

          &--active {
              margin-top: 10px
          }
      }
  }
}
</style>
