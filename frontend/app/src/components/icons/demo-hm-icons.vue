<template>
  <v-card class="demo-hm-icons">
    <v-toolbar class="demo-hm-icons__actions mb-4 pt-2">
      <v-text-field v-model="iconNameFilter" label="Поиск" />
      <v-text-field v-model="iconColor" label="Цвет" />
      <v-text-field v-model="iconStrokeWidth" label="Обводка" />
    </v-toolbar>

    <v-row>
      <div
        class="demo-hm-icons__icon__wrapper"
        v-for="iconName in filteredIconsNames"
        :key="iconName"
        @click="iconNameToClipboard(iconName)"
      >
        <svg-icon
          class="demo-hm-icons__icon"
          :name="iconName"
          :color="iconColor"
          :stroke-width="iconStrokeWidth"
        />
        <div class="demo-hm-icons__icon__name">
          {{ iconName }}
        </div>
      </div>
    </v-row>
  </v-card>
</template>

<script>
// import kebabCase from "lodash/kebabCase";

import { componentsName } from "./items/iconsNames";
// import navMenuIcons from "./navMenu/navMenuIcons";
import svgIcon from "@/components/icons/svgIcon";
import copyTextToClipboard from "@/utilities/copyTextToClipboard";
import lowerCase from 'lodash/lowerCase';

export default {
  name: "HmIconsDemo",
  components: {
    svgIcon,
  },
  data: function() {
    return {
      iconNameFilter: "",
      iconColor: "#000000",
      iconStrokeWidth: "0.0",
    };
  },
  computed: {
    filteredIconsNames() {
      return componentsName.filter(name => {
        // console.log(this);
        if (!this.iconNameFilter) {
          return true;
        }
        let searchableName = name.replace(/-/, "");

        let regExp = new RegExp(this.iconNameFilter, 'i');

        return searchableName.match(regExp);
      });
    }
  },
  methods: {
    iconNameToClipboard(iconName) {
      let iconNameChars = iconName.split('');
      iconNameChars[0] = lowerCase(iconNameChars[0]);
      iconName = iconNameChars.join('');

      copyTextToClipboard(iconName);
    },
  }
};
</script>

<style lang="scss">
.demo-hm-icons {

  &__icon__wrapper {
    width: 150px;
    font-size: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    margin: 12px 4px;
  }
  &__icon__name {
    margin-top: 8px;
  }

  &__actions .v-toolbar__content {
    > * + * {
      margin-left: 16px;
    }
  }

  .demo-hm-icons__icon__wrapper {
    cursor: pointer;
  }
}
</style>
