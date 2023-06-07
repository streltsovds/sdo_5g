<template>
  <v-menu offset-y>
    <template v-slot:activator="{ on }">
      <v-btn slot="activator" v-on="on" small icon primary class="hm-grid-checkbox-all">
        <v-fade-transition leave-absolute>
          <v-icon
            v-if="noSelected"
            key="check_box_outline_blank"
            color="secondary lighten-1"
          >
            check_box_outline_blank
          </v-icon>
          <v-icon v-if="all" key="check_box" color="primary">
            check_box
          </v-icon>
          <v-icon
            v-if="indeterminate"
            key="indeterminate_check_box"
            color="secondary lighten-1"
          >
            indeterminate_check_box
          </v-icon>
        </v-fade-transition>
      </v-btn>
    </template>

    <v-list dense>

      <v-list-item
        v-if="!noSelected"
        @click="handleDeselectAll"
      >
        <v-list-item-action>
          <v-icon color="primary">done_all</v-icon>
        </v-list-item-action>
        <v-list-item-content>
          <v-list-item-title v-text="deselectAllText" />
        </v-list-item-content>
      </v-list-item>

      <v-list-item
        @click="handleToggleAllCurrent"
      >
        <v-list-item-action>
          <v-icon color="accent">done</v-icon>
        </v-list-item-action>
        <v-list-item-content>
          <v-list-item-title v-text="toggleAllCurrentText" />
        </v-list-item-content>
      </v-list-item>

      <v-list-item
        v-if="!allRowsSelected"
        @click="handleSelectAll"
      >
        <v-list-item-action>
          <v-icon color="primary">done_all</v-icon>
        </v-list-item-action>
        <v-list-item-content>
          <v-list-item-title v-text="selectAllText" />
        </v-list-item-content>
      </v-list-item>

    </v-list>
  </v-menu>
</template>

<script>
import { TOGGLE_ALL_EVENT, TOGGLE_ALL_CURRENT_EVENT } from "../constants";

export default {
  props: {
    noCurrentRowsSelected: Boolean,
    allCurrentRowsSelected: Boolean,
    notAllCurrentRowsSelected: Boolean,
    allRowsSelected: Boolean
  },
  computed: {
    all() {
      return this.allCurrentRowsSelected;
    },
    indeterminate() {
      return !this.noCurrentRowsSelected && this.notAllCurrentRowsSelected;
    },
    noSelected() {
      return this.noCurrentRowsSelected;
    },
    selectAllText() {
      return "Выбрать всё";
    },
    deselectAllText() {
      return "Снять всё";
    },
    toggleAllCurrentText() {
      return this.allCurrentRowsSelected
        ? "Снять выделение на текущей странице"
        : "Выбрать всё на текущей странице";
    },
    toggleAllValue() {
      return this.all || this.allCurrent;
    }
  },
  methods: {
    handleToggleAllCurrent() {
      this.$emit(TOGGLE_ALL_CURRENT_EVENT, !this.allCurrentRowsSelected);
    },
    handleSelectAll() {
      this.$emit(TOGGLE_ALL_EVENT, true);
    },
    handleDeselectAll() {
      this.$emit(TOGGLE_ALL_EVENT, false);
    },
  },
};
</script>

<style lang="scss">
.hm-grid-checkbox-all {
  .v-icon {
    /** в head.tpl добавлена проверка загрузки шрифта  */
    //overflow: hidden;
  }
}
</style>
