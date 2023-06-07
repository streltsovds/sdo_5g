<template>
  <div class="hm-form-element hm-radio" :class="`hm-form-element_${name}`">
    <label
      class="v-label theme--light"
      v-if="label"
      :class="{ required }"
      v-text="label"
    />
    <p v-if="description" v-text="description" />
    <v-radio-group
      :value="checked"
      :mandatory="false"
      :name="name"
      :error="errorsExist"
      :error-messages="errorsArray"
      :class="{ required }"
      @change="update"
    >
      <v-radio
        v-for="(radio, key) in options"
        :key="key"
        :label="!radio.title ? radio : ''"
        :value="key"
      >
        <template v-if="radio.title && radio.type" v-slot:label>
          <file-icon :type="radio.type" small />
          <span>{{ radio.title }}</span>
          <v-tooltip v-if="radio.viewUrl" bottom>
            <template v-slot:activator="{ on }">
              <v-btn
                slot="activator"
                v-on="on"
                :href="radio.viewUrl"
                @click.stop
                text
                icon
                color="primary"
              >
                <svg-icon
                  name="openNew"
                  color="#bbb"
                  style="margin-righ: 0px; width: 18px"
                  title="Предварительный просмотр"
                />
              </v-btn>
            </template>
            <span>Просмотр материала</span>
          </v-tooltip>

        </template>
      </v-radio>
    </v-radio-group>
  </div>
</template>
<script>
import MixinState from "./../mixins/MixinState";
import FileIcon from "@/components/icons/file-icon/index";
import SvgIcon from "@/components/icons/svgIcon";

export default {
  name: "HmRadio",
  components: {FileIcon, SvgIcon},
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
    options: {
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
      label: (this.attribs && this.attribs.label) || "",
      description: (this.attribs && this.attribs.description) || "",
      required: (this.attribs && this.attribs.required) || false,
      errorsData: this.errors,
      checked: this.value || "",
      formId: (this.attribs && this.attribs.formId) || null,
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
    },
  },
  watch: {
    options(data) {
      console.log(data)
    }
  },
  created() {
    this.updateChecked(this.value);
  },
  methods: {
    clearErrors() {
      this.errorsData = null;
    },
    update(value) {
      this.updateChecked(value);
      this.clearErrors();
    },
    updateChecked(value) {
      this.mixinStateUpdate("checked", value);
      this.$emit("update", value);
    }
  },
};
</script>
<style lang="scss">
.hm-radio {
  .v-input--radio-group {
    margin-top: 0;
    .v-label {
      &:after {
        content: "";
      }
    }
  }
  .v-radio {
    .v-input--selection-controls__input {
      // TODO: разобраться откуда приходит display: none (пример на странице создания вопроса в тесте, второй радио-баттон скрывается)
      display: inline-flex !important;
    }
  }
}
</style>
