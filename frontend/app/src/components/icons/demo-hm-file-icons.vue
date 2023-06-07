<template>
  <v-card class="demo-hm-file-icons">
    <v-toolbar class="demo-hm-file-icons__actions mb-4 pt-2">
      <v-checkbox
        v-model="small"
        label="Small"
      ></v-checkbox>
<!--      <v-text-field v-model="iconColor" label="Цвет" />-->
<!--      <v-text-field v-model="iconStrokeWidth" label="Обводка" />-->
<!--      <v-text-field v-model="iconNameFilter" label="Поиск" />-->
    </v-toolbar>

    <v-row>
      <div
        v-for="typeName in typesNames"
        :key="typeName"
        class="demo-hm-file-icons__icon__wrapper"
      >
        <file-icon
          :type="typeName"
          :small="small"
        />
        <div class="demo-hm-file-icons__icon__name"> {{ typeName }} </div>
      </div>
    </v-row>
  </v-card>
</template>

<script>
// import kebabCase from "lodash/kebabCase";

// import itemsIcons from "./items/itemsIcons";
// import navMenuIcons from "./navMenu/navMenuIcons";
import fileIcon from "@/components/icons/file-icon";
import fileIconTypes from "@/components/icons/file-icon/types"

// let allIIcons = { ...itemsIcons, ...navMenuIcons };

export default {
  name: "HmFileIconsDemo",
  components: {
    fileIcon,
  },
  data: function() {
    return {
      small: false,
      // iconNameFilter: "",
      // iconColor: "#000000",
      // iconStrokeWidth: "0.0",
    };
  },
  computed: {
    typesNames() {
      return Object.keys(fileIconTypes);
    },
  },
};
</script>

<style lang="scss">
.demo-hm-file-icons {

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
}
</style>
