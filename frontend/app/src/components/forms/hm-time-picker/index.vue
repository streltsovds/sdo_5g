<template>
  <div
    class="hm-form-element hm-time-picker"
    :class="`hm-form-element_${name}`"
  >
    <v-menu
      ref="menu"
      v-model="menu"
      :close-on-content-click="false"
      :nudge-right="40"
      :return-value.sync="time"
      lazy
      transition="scale-transition"
      offset-y
      full-width
      max-width="290px"
      min-width="290px"
    > 
      <template v-slot:activator="{ on }" >
          <v-text-field
            v-on="on"
            :value="time"
            :name="name"
            :label="label"
            :error-messages="errorsArray"
            :error="errorsExist"
            :hint="description"
            :disabled="attribs.disabled"
            :class="{ required }"
            persistent-hint
            prepend-icon="access_time"
            readonly
          ></v-text-field>
      </template>
      <v-time-picker
        v-if="menu"
        :value="time"
        full-width
        format="24hr"
        @change="update"
      ></v-time-picker>
    </v-menu>
  </div>
</template>
<script>
import MixinState from "./../mixins/MixinState";

export default {
  name: "HmTimePicker",
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
      time: this.value || "",
      formId: this.attribs.formId || null,
      menu: false
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
    this.updateTime(this.value);
  },
  methods: {
    clearErrors() {
      this.errorsData = null;
    },
    update(value) {
      this.updateTime(value);
      this.$refs.menu.save(value);
      this.clearErrors();
    },
    updateTime(value) {
      this.mixinStateUpdate("time", value);
    }
  }
};
</script>
