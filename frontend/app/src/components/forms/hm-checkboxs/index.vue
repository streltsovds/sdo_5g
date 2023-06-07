<template>
  <div class="hm-form-element hm-checkboxs" :class="`hm-form-element_${name}`">
    <label
      class="v-label theme--light"
      v-if="label"
      :class="{ required }"
      v-text="label"
    />
    <p v-if="description" v-text="description" />
    <v-checkbox
      v-for="(checkbox, key) in options"
      :name="name"
      :key="key"
      :label="!checkbox.title ? checkbox : ''"
      :value="key"
      :error="errorsExist"
      :error-messages="errorsArray"
      v-model="currentItems"
      @change="update"
    >
      <template v-if="checkbox.title && checkbox.type" v-slot:label>
        <file-icon :type="checkbox.type" small />
        <span>{{ checkbox.title }}</span>
        <v-tooltip v-if="checkbox.viewUrl" bottom>
          <template v-slot:activator="{ on }">
            <v-btn
              slot="activator"
              v-on="on"
              :href="checkbox.viewUrl"
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
    </v-checkbox>
    <p class="hm-checkboxs__count" v-if="currentItems.length > 0">Будет создано <span class="hm-checkboxs__count-number">{{currentItems.length}}</span> {{ declOfNum(currentItems.length) }}</p>
  </div>
</template>
<script>
import MixinState from "./../mixins/MixinState";
import FileIcon from "@/components/icons/file-icon/index";
import SvgIcon from "@/components/icons/svgIcon";

export default {
  name: "HmCheckboxs",
  components: {FileIcon, SvgIcon},
  mixins: [MixinState],
  props: {
    name: {
      type: String,
      required: true
    },
    value: {
      type: Array,
      default: () => {}
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
      currentItems: this.value || [],
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
    },
    declOfNum(number) {
      const words = ['занятие', 'занятия', 'занятий']
      return words[(number % 100 > 4 && number % 100 < 20) ? 2 : [2, 0, 1, 1, 1, 2][(number % 10 < 5) ? Math.abs(number) % 10 : 5]];
    }
  },
};
</script>
<style lang="scss">
  .hm-checkboxs {
    .file-icon {
      margin-right: 10px;
    }

    .v-input--selection-controls {
      margin-top: 0;
    }

    &__count {
      font-size: 18px;
      &-number {
        font-weight: 700;
      }
    }
  }
</style>
