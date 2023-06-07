<template>
  <div class="file-icon">
    <svg-icon
      class="file-icon__background"
      name="paper-list"
      v-bind="paperListProps"
    />
    <svg-icon
      v-if="currentIcon"
      class="file-icon__icon"
      v-bind="iconProps"
    />
  </div>
</template>

<script>
import SvgIcon from "@/components/icons/svgIcon";
import fileIconTypes from "./types";
import addPx from "@/utilities/addPx";

export default {
  name: "FileIcon",
  components: { SvgIcon },
  props: {
    filetype: {
      type: String,
      default: null,
    },
    type: {
      type: String,
      default: null,
    },
    icon: {
      type: String,
      default: null,
    },
    color: {
      type: String,
      default: null,
    },
    small: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    currentIcon() {
      return this.icon ||
        this.currentTypeParams.icon ||
        (this.filetype
              ? ("file-" + this.currentType)
              : (this.type ? ("file-" + this.type) : null));
    },
    currentColor() {
      return this.color || this.currentTypeParams.color;
    },
    paperListProps() {
      return {
        width: this.small ? 20 : 64,
        height: this.small ? 24 : 76,
        color: this.currentColor,
        title: "",
      };
    },
    iconProps() {
      return {
        color: "#FFFFFF",
        name: this.currentIcon,
        width: this.small ? 12 : 30,
        height: this.small ? 12 : 30,
        title: "",
        style: {
          left: addPx(this.small ? 3 : 14),
          bottom: addPx(this.small ? 3 : 14),
        },
      };
    },
    currentType() {
      if (this.type) {
        if (fileIconTypes[this.type]) {
          return this.type;
        }
      }
      if (this.fileType && this.filetype.toLowerCase() !== 'unknown') {
        if (fileIconTypes[this.fileType]) {
          return this.filetype;
        }
      }
      return "default";

      // if (this.filetype && this.filetype.toLowerCase() !== 'unknown') {
      //   return this.getFromTypes(this.filetype);
      // } else if (this.type) {
      //   return this.getFromTypes(this.type);
      // }
      // return "default";
    },
    // параметры для отображения текущего типа иконки, из type.js
    currentTypeParams() {
      return fileIconTypes[this.currentType];
    },
  },
  methods: {
    /**
     * TODO не надо так делать: парсить тип из названия иконки!
     *   Если будут проблемы, переводите на меня /komarov
     *
     * Если есть желание изменять этот код самостоятельно, проверяйте, пожалуйста,
     *   что иконки в /demo/vue/icons не пропадают после правок
     **/
    // getFromTypes(filetype) {
    //   if (fileIconTypes[filetype]) {
    //     if(fileIconTypes[filetype].icon) {
    //       return fileIconTypes[filetype].icon.split('-')[1];
    //     }
    //     return filetype;
    //   }
    // }
  },
  mounted() {
  }
};
</script>

<style lang="scss">
.file-icon {
  position: relative;
  display: flex;

  &__icon {
    position: absolute;
  }
}
</style>
