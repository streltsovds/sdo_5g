<template>
  <div class="hm-tabs" :style="{background: themeColors.contentColor}">
    <v-tabs :background-color="themeColors.contentColor" v-model="active">
      <v-tab
        v-for="(tab, key) in tabs"
        :key="key"
        :href="`#${key}`"
        :class="{ 'hm-tabs_tab-error error--text': !tab.isValid }"
        :title="tab.description"
        :disabled="isDesabledTab() && defaultTab !== key"
      >
        <svg-icon :name="iconName(key)" color="#4A90E2" style="width: 20px; height: 20px; margin-right: 10px" />
        {{ key }}
      </v-tab>
      <v-tab-item
        v-if="active && activeTab.content"
        :id="active"
        :key="active"
        :value="active"
      >
        <hm-dependency :template="activeTab.content" />
      </v-tab-item>
    </v-tabs>
  </div>
</template>
<script>
import HmDependency from "./../../helpers/hm-dependency";
import SvgIcon from "@/components/icons/svgIcon";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
export default {
  mixins: [VueMixinConfigColors],
  components: {SvgIcon, HmDependency },
  props: {
    tabs: {
      type: Object,
      default: () => {}
    },
    defaultTab: {
      type: String,
      default: null
    },
  },
  data() {
    return {
      active: null,
    };
  },
  computed: {
    activeTab() {
      if (!this.active) return this.tabs.length > 0 ? this.tabs[0] : null;
      return this.tabs[this.active];
    }
  },
  created: function () {
    if(this.defaultTab) this.active = this.defaultTab;
  },
  methods: {
    isDesabledTab() {
      if(this.defaultTab && (this.defaultTab !== "")) return true
      else return false
    },
    iconName(name) {
      let nameIcon = '';
      if(name.toLowerCase() === 'создать материал') {
        nameIcon = 'Add'
      } else if(name.toLowerCase() === 'выбрать из материалов курса') {
        nameIcon = 'education'
      } else if(name.toLowerCase() === 'выбрать из базы знаний') {
        nameIcon = 'knowledge-base'
      }
      return nameIcon;
    },
  },
};
</script>
<style lang="scss">
.hm-tabs {
  & .v-item-group,
  & .v-window__container {
    background-color: inherit !important;
  }
  box-shadow: 0px 3px 1px -2px rgba(0, 0, 0, 0.2),
    0px 2px 2px 0px rgba(0, 0, 0, 0.14), 0px 1px 5px 0px rgba(0, 0, 0, 0.12);
  text-decoration: none;
  background: white;
  .v-card {
    box-shadow: none;
    .v-card__text {
      .v-card__text {
        padding: 0;
      }
    }
  }
}
.hm-tabs_tab-icon {
  display: none;
}
.hm-tabs_tab-error {
  .hm-tabs_tab-icon {
    display: block;
    margin-right: 5px;
  }
}
</style>
