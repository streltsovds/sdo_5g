<template>
  <div class="hm-form-element hm-multi-set">
    <label
      v-if="label"
      class="v-label theme--light"
      :class="{ required }"
      v-text="label"
    ></label>
    <p v-if="description" v-text="description"></p>
    <hm-errors :errors="errors"></hm-errors>
    <div v-if="
    sets.length > 0" class="hm-multi-set_items">
      <v-list>
        <div v-for="dependencesValue in sets" :key="dependencesValue.id">
          <v-list-item v-if="dependencesValue.body" class="hm-multi-set_item">
            <v-list-item-content>
              <div
                v-for="(dependence, keyDependence) in dependencesValue.body"
                :key="keyDependence"
                :class="setsBodyClasses[keyDependence]"
                class="hm-multi-set_dependence"
              >
                <hm-dependency :template="dependence" />
              </div>
            </v-list-item-content>
            <v-list-item-action class="hm-multi-set_item-actions">
              <v-tooltip bottom>
                <v-btn
                  slot="activator"
                  text
                  icon
                  color="error"
                  @click="remove(dependencesValue.id)"
                >
                  <v-icon dark>clear</v-icon>
                </v-btn>
                <span>Удалить</span>
              </v-tooltip>
            </v-list-item-action>
          </v-list-item>
        </div>
      </v-list>
    </div>
    <div class="hm-multi-set_items-actions">
      <v-tooltip bottom>
        <v-btn slot="activator" fab dark small color="primary" @click="add">
          <v-icon dark>add</v-icon>
        </v-btn>
        <span>Добавить</span>
      </v-tooltip>
    </div>
  </div>
</template>
<script>
import HmDependency from "./../../helpers/hm-dependency";
import HmErrors from "./../hm-errors";

export default {
  name: "HmMultiSet",
  components: { HmDependency, HmErrors },
  props: {
    name: {
      type: String,
      required: true
    },
    value: {
      type: Array,
      default: () => []
    },
    dependences: {
      type: Array,
      default: () => []
    },
    dependencesClasses: {
      type: Array,
      default: () => []
    },
    emptyDependence: {
      type: Array,
      default: () => []
    },
    attribs: {
      type: Object,
      default: () => {}
    },
    errors: {
      type: Object,
      default: () => {}
    }
  },
  data() {
    return {
      label: this.attribs.label || null,
      description: this.attribs.description || null,
      required: this.attribs.required,
      sets: [],
      setsBodyClasses: [],
      emptySet: this.emptyDependence || []
    };
  },
  created() {
    this.sets = this.dependences.map((item, key) => {
      return {
        id: key,
        body: item
      };
    });

    this.dependencesClasses.map((item, key) => {
      this.setsBodyClasses[key] = item;
    });
  },
  methods: {
    add() {
      if (!this.emptySet.length) return;

      let set = {
        id: this.getNextId(),
        body: this.emptySet
      };

      this.sets.push(set);
    },

    remove(id) {
      let index = this.sets.findIndex(set => set.id === id);

      if (index === -1) return;

      this.sets.splice(index, 1);
    },

    getNextId() {
      let max = 0;

      this.sets.forEach(set => {
        if (set.id > max) max = set.id;
      });

      return max + 1;
    }
  }
};
</script>
<style lang="scss">
.hm-multi-set_item {
  // margin-top: 10px;
  // margin-bottom: 20px;
  max-width: 550px;
  .v-list__tile {
    padding-left: 0;
    height: auto;
    align-items: flex-start;
  }
  .v-list__tile__content {
    overflow: unset;
  }
  .v-list-item__content {
    padding: 0 !important;
  }
  &-actions {
    position: absolute;
    right: -6px;
    bottom: 18px;
  }
}
.hm-multi-set_dependence {
  width: 100%;
  .v-input--checkbox {
    margin-top: 0;
    .v-input__slot {
      margin-bottom: 0;
    }
    .v-messages {
      display: none;
    }
  }
  // .hm-form-element.brief{
  //   width: 20%;
  // }
}
</style>
