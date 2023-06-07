<template>
  <li
    class="hm-multi-select_item"
    :class="{
      'hm-multi-select_folder': isFolder,
      'hm-multi-select_root': !isFolder
    }"
  >
    <div class="hm-multi-select_item-header">
      <v-btn
        class="hm-multi-select_item-toggle"
        v-if="isFolder"
        @click.stop="toggle"
        icon
      >
        <v-icon v-text="isOpen ? 'folder_open' : 'folder'" />
      </v-btn>

      <span>{{ model.name }}</span>
      <v-tooltip bottom>
        <template v-slot:activator="{ on }">
          <v-checkbox
            class="hm-multi-select_item-checkbox"
            v-on="on"
            v-model="model.selected"
            @click.stop.prevent="toggleSelect(model)"
            color="success"
          />
        </template>
        <span>{{ model.selected ? "Убрать" : "Выбрать" }}</span>
      </v-tooltip>

      <v-tooltip bottom>
        <template v-slot:activator="{ on }">
          <v-btn
            class="hm-multi-select_item-add"
            v-on="on"
            :color="isSelectedList ? 'warning' : 'success'"
            @click.stop="add(model)"
            icon
            text
          >
            <v-icon v-text="isSelectedList ? 'backspace' : 'add_box'" />
          </v-btn>
        </template>
        <span>{{ isSelectedList ? "Удалить" : "Добавить" }}</span>
      </v-tooltip>
    </div>
    <transition name="fade">
      <ul class="hm-multi-select_sub-list" v-show="isOpen" v-if="isFolder">
        <multi-select-tree
          v-for="(children, index) in model.children"
          :key="index"
          :model="children"
          :is-selected-list="isSelectedList"
          @add="add"
          @toggleSelect="toggleSelect"
        />
      </ul>
    </transition>
  </li>
</template>
<script>
export default {
  name: "MultiSelectTree",
  props: {
    model: {
      type: Object,
      required: true
    },
    isSelectedList: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      isOpen: false
    };
  },
  computed: {
    isFolder() {
      return !!this.model.isFolder;
    }
  },
  methods: {
    toggle() {
      if (!this.isFolder) return;
      this.isOpen = !this.isOpen;
    },
    toggleSelect(model) {
      console.log("toggleSelect");
      this.$emit("toggleSelect", model);
    },
    add(model) {
      this.$emit("add", model);
    }
  }
};
</script>
<style lang="scss">
.hm-multi-select_sub-list {
  margin-left: 30px;
}
.hm-multi-select_item-header {
  display: flex;
  align-items: center;
  cursor: pointer;
  &:hover {
    background-color: rgba(#000, 0.1);
  }
  span {
    flex-grow: 1;
    flex-shrink: 1;
    flex-basis: calc(100% - 60px);
  }
}
.hm-multi-select_item {
  margin-left: 4px;
  padding-left: 0px;
  .hm-multi-select_item-header {
    padding-left: 10px;
  }
}
.hm-multi-select_folder {
  margin-left: -4px;
}
.hm-multi-select_root {
  .hm-multi-select_item-header {
    padding-left: 15px;
    margin-left: -7px;
  }
}
.hm-multi-select_item-checkbox {
  margin-top: 0;
  height: 40px;
  padding-top: 0 !important;
  .v-input__slot {
    margin-bottom: 0;
  }
  .v-input--selection-controls__input {
    margin: 8px !important;
  }
}
.hm-multi-select_item-toggle {
  width: 30px;
  height: 30px;
  margin: 0;
  i {
    font-size: 18px;
  }
}
.hm-multi-select_list-body {
  .hm-multi-select_item-add {
    margin: 0;
  }
}
</style>
