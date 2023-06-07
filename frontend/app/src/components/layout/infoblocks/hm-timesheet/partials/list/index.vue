<template>
  <div style="position: relative;">
    <v-dialog
      v-model="isAdderShown"
      max-width="600px"
      :fullscreen="$vuetify.breakpoint.smAndDown"
    >
      <action-adder
        :actions="actions"
        @activity-submit="onActivitySubmit"
        @close-dialog="isAdderShown = !isAdderShown"
      />
    </v-dialog
          >

    <v-tooltip left>
      <template v-slot:activator="{ on: onTooltip }">
        <v-btn
          slot="activator"
          color="primary"
          small
          fab
          @click="isAdderShown = !isAdderShown"
          v-on="onTooltip"
          class="add-circle"
        >
          <v-icon>add</v-icon>
        </v-btn>
      </template>
      <span>Добавить запись</span>
    </v-tooltip>

    <v-list v-if="items.length" dense>
      <v-list-item
        v-for="(item, i) in items"
        :key="i"
        class="unsetTileHeight mb-2"
      >
        <v-list-item-action >
          <v-btn icon @click="$emit('item-remove', item)">
            <v-icon> close </v-icon>
          </v-btn>
        </v-list-item-action>
        <v-list-item-content>
          <v-list-item-action-text>
            {{ getTypeName(item.typeId ? item.typeId : item.type) }} | с
            {{ item.time.from }} по {{ item.time.to }}
          </v-list-item-action-text>
          <v-list-item-title> {{ item.description }} </v-list-item-title>
        </v-list-item-content>
      </v-list-item>
    </v-list>

    <hm-empty v-else empty-type="full" sub-label="Нет записей о затраченном рабочем времени сегодня" />
    <span v-if="items.length"></span>
    <v-tooltip v-else left>
      <template v-slot:activator="{ on: onTooltip }">
        <v-btn
                slot="activator"
                color="primary"
                small
                fab
                @click="isAdderShown = !isAdderShown"
                v-on="onTooltip"
        >
          <v-icon>add</v-icon>
        </v-btn>
      </template>
      <span>Добавить запись</span>
    </v-tooltip>

  </div>
</template>

<script>
import Adder from "./Adder";
import HmEmpty from "@/components/helpers/hm-empty/index";
export default {
  components: {
    "action-adder": Adder, HmEmpty
  },
  props: {
    actions: {
      type: Array,
      default: () => []
    },
    items: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      isAdderShown: false
    };
  },
  computed: {},
  methods: {
    onActivitySubmit(value) {
      let temp = { ...value };
      temp["isDeleteable"] = true;
      this.$emit("item-add", temp);
      this.isAdderShown = !this.isAdderShown;
    },
    getTypeName(value) {
      return this.actions.filter(x => x.classifier_id === value)[0].name;
    }
  }
};
</script>

<style lang="scss">
.infoblock-timesheet-block {
  button {
    position: absolute;
    right: 10px;
    top: 0px;
    z-index: 1;
  }
}
.v-list .unsetTileHeight .v-list__tile {
  height: auto;
}

.add-circle {
  margin-top: -50px;
}
</style>
