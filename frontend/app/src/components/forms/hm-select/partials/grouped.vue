<template>
  <v-autocomplete
    class="hm-select-grouped"
    :value="selected"
    :items="itemsFormatted"
    :label="label"
    :hint="description"
    :class="{ required }"
    persistent-hint
    @change="update"
  >
    <template slot="selection" slot-scope="data">
      <v-list-item-content class="hm-select-grouped_value">
        <v-list-item-title>
          {{ data.item.text }} ({{ data.item.group }})
        </v-list-item-title>
      </v-list-item-content>
    </template>
    <template slot="item" slot-scope="data">
      <template v-if="typeof data.item !== 'object'">
        <v-list-item-content v-text="data.item"></v-list-item-content>
      </template>
      <template v-else>
        <v-list-item-content>
          <v-list-item-title v-text="data.item.text"></v-list-item-title>
        </v-list-item-content>
      </template>
    </template>
  </v-autocomplete>
</template>
<script>
export default {
  props: {
    items: {
      type: Object,
      default: () => {}
    },
    label: {
      type: String,
      default: null
    },
    description: {
      type: String,
      default: null
    },
    required: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    },
    selected: {
      type: String,
      default: null
    }
  },
  computed: {
    itemsFormatted() {
      console.log('itemsFormatted called');

      let result = [];

      for (let key in this.items) {
        if (!this.items.hasOwnProperty(key)) continue;

        if (typeof this.items[key] === "object") {
          if (result.length > 0) {
            this.addDivider(result);
          }
          this.addHeader(result, key);
          this.addGroupItems(result, this.items[key], key);
        }
      }

      return result;
    },
  },
  data() {
    return {
      // itemsFormatted: [],
    };
  },
  created() {
    this.init();
  },
  methods: {
    init() {

    },
    update(value) {
      return this.$emit("update", value);
    },
    addHeader(itemsFormatted, header) {
      itemsFormatted.push({ header });
    },
    addGroupItems(itemsFormatted, groupItems, group) {
      for (let key in groupItems) {
        if (!groupItems.hasOwnProperty(key) || !groupItems[key]) continue;
        itemsFormatted.push({
          value: key,
          text: groupItems[key],
          group
        });
      }
    },
    addDivider(itemsFormatted) {
      itemsFormatted.push({ divider: true });
    }
  },
};
</script>
<style lang="scss">
.hm-select-grouped {
  input {
    opacity: 0;
  }
  .v-select__selections {
    width: 0;
  }
  .hm-select-grouped_value {
    display: block;
    max-width: 95%;
  }
}
</style>
