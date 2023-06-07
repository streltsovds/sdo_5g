<template>
  <div class="hm-form-element hm-time-slider">
    <v-text-field
      :value="timeSliderText"
      :label="label"
      :error-messages="errorsArray"
      :error="errorsExist"
      :hint="description"
      disabled
      :class="{ required }"
      persistent-hint
    ></v-text-field>
    <v-range-slider
      v-if="!loading"
      :value="range"
      class="hm-time-slider_times"
      :step="step"
      :tick-labels="labels"
      thumb-label="always"
      :max="max"
      :min="min"
      @change="updateRange"
    >
      <template slot="thumb-label" slot-scope="props">
        <span> {{ labels[props.value] }} </span>
      </template>
    </v-range-slider>
    <input type="hidden" :name="name" :value="labels[range[0]]" />
    <input type="hidden" :name="name" :value="labels[range[1]]" />
  </div>
</template>
<script>
import MixinState from "./../mixins/MixinState";

export default {
  name: "HmTimeSlider",
  mixins: [MixinState],
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
      errorsData: this.errors,
      range: [0, 96],
      labels: [],
      min: 0,
      max: 96,
      step: 1,
      loading: false
    };
  },
  computed: {
    timeSliderText() {
      let start = this.labels[this.range[0]] || "00:00";
      let end = this.labels[this.range[1]] || "23:59";

      return `с ${start} по ${end}`;
    },
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
      this.labelsGenerate();
      if (this.value) this.initRange();

      this.loading = false;
    },

    updateRange(value) {
      this.mixinStateUpdate("range", value);
    },

    initRange() {
      let start = this.value[0] ? this.getKeyLabel(this.value[0]) : this.min;
      let end = this.value[1] ? this.getKeyLabel(this.value[1]) : this.max;

      this.updateRange([start, end]);
    },

    getKeyLabel(value) {
      let label = this.getFormattedTime(value);
      return this.labels.findIndex(l => l === label);
    },

    getFormattedTime(time) {
      return time.length > 4 ? time : `0${time}`;
    },

    labelsGenerate() {
      for (let i = this.min; i <= this.max; i++) {
        let hours = Math.floor(i / 4);
        let minute;

        switch (i % 4) {
          case 0:
            minute = "00";
            break;
          case 1:
            minute = "15";
            break;
          case 2:
            minute = "30";
            break;
          case 3:
            minute = "45";
            break;
        }

        if (i === this.max) {
          hours = "23";
          minute = "59";
        }
        this.labels[i] = this.getFormattedTime(`${hours}:${minute}`);
      }
    }
  }
};
</script>
<style lang="scss">
.hm-time-slider {
  .hm-time-slider_times {
    margin-top: 30px;
  }
}
</style>
