<template>
  <div class="hm-form-element hm-slider">
    <v-text-field
      :value="val"
      :label="label"
      :error-messages="errorsArray"
      :error="errorsExist"
      :hint="description"
      disabled
      :class="{ required }"
      persistent-hint
    ></v-text-field>
    <v-slider
      v-if="!loading"
      :value="val"
      class="hm-slider_body"
      :step="step"
      thumb-label="always"
      :max="max"
      :min="min"
      ticks
      @change="updateVal"
    >
    </v-slider>
    <input type="hidden" :name="name" :value="val" />
  </div>
</template>
<script>
import MixinState from "./../mixins/MixinState";

export default {
  name: "HmSlider",
  mixins: [MixinState],
  props: {
    name: {
      type: String,
      required: true
    },
    value: {
      type: String,
      default: null
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
      errorsData: this.errors,
      val: this.value ? +this.value : 0,
      min: this.attribs.min || 0,
      max: this.attribs.max || 10,
      step: this.attribs.step || 1,
      loading: false
    };
  },
  computed: {
    errorsExist() {
      for (let key in this.errorsData) {
        if (this.errorsData.hasOwnProperty(key)) return true;
      }
      return false;
    },
    errorsArray() {
      let rules = [];
      for (let key in this.errorsData) {
        if (this.errorsData.hasOwnProperty(key))
          rules.push(this.errorsData[key]);
      }
      return rules;
    }
  },
  created() {
    this.init();
  },

  methods: {
    init() {
      this.loading = true;

      if (this.value) this.updateVal(this.value);

      this.loading = false;
    },
    updateVal(value) {
      this.mixinStateUpdate("val", value);
    }
  }
};
</script>
<style lang="scss">
.hm-slider {
  .hm-slider_body {
    margin-top: 35px;
  }
}
</style>
