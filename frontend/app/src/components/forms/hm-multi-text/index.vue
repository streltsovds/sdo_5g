<template>
  <div class="hm-form-element hm-multi-text" :class="`hm-form-element_${name}`">
    <label
      v-if="label"
      class="v-label theme--light"
      :class="{ required }"
      v-text="label"
    ></label>
    <p v-if="description" v-text="description"></p>
    <hm-errors :errors="errors"></hm-errors>
    <div v-for="(set, key) in sets" :key="set.id" class="hm-multi-text_item">
      <v-text-field
        v-model="set.value"
        :name="name"
        :label="`${key + 1}.`"
        @change="update(key)"
      ></v-text-field>
    </div>
    <div class="hm-multi-text_actions">
      <v-tooltip bottom>
        <v-btn
          slot="activator"
          fab
          dark
          small
          color="primary"
          @click="addNewSet"
        >
          <v-icon dark>add</v-icon>
        </v-btn>
        <span>Добавить</span>
      </v-tooltip>
    </div>
  </div>
</template>
<script>
import HmErrors from "./../hm-errors";

export default {
  name: "HmMultiText",
  components: { HmErrors },
  props: {
    name: {
      type: String,
      required: true
    },
    value: {
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
      label: this.attribs.label || "",
      description: this.attribs.description || "",
      required: this.attribs.required || false,
      sets: [],
      formId: this.attribs.formId || null
    };
  },
  created() {
    this.init();
  },
  methods: {
    init() {
      if (!this.value.length) return;

      this.value.forEach((item, key) => {
        this.addSet(key, item);
      });
    },
    hasEmptySet() {
      return !this.sets.every(set => set.value.length > 0);
    },
    update(key) {
      if (!this.hasEmptySet()) return;

      if (!this.sets[key].value.length) {
        this.removeSetByKey(key);
      }
    },
    addSet(id, value) {
      this.sets.push({
        id: id,
        value: value
      });
    },
    addNewSet() {
      if (this.hasEmptySet()) return;

      let key = 0;
      this.sets.forEach(set => {
        if (set.id > key) key = set.id;
      });

      this.addSet(key + 1, "");
    },
    removeSetByKey(key) {
      this.sets.splice(key, 1);
    }
  }
};
</script>
<style lang="scss">
.hm-multi-text {
  margin-bottom: 30px;
}
</style>
