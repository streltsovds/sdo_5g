<template>
  <div class="hm-form-element hm-select" :class="`${attribsClass} hm-form-element_${name}` ">
    <hm-errors :errors="errors"></hm-errors>
    <hm-grouped-select
      v-if="isGrouped"
      :label="label"
      :description="description"
      :required="required"
      :disabled="disabled"
      :items="options.items"
      :selected="selected"
      @update="updateValue"
    ></hm-grouped-select>
    <hm-single-select
      v-else
      :refresh="refresh"
      :label="label"
      :description="description"
      :required="required"
      :disabled="disabled"
      :items="options.items"
      :selected="selected"
      :multiple="multiple"
      :name="name"
      :key-list="options.keyOrder"
      @update="updateValue"
    ></hm-single-select>
    <input type="hidden" :name="name" :value="selected" ref="hiddenValueInput"/>
  </div>
</template>
<script>
    import HmErrors from "./../hm-errors";
    import HmSingleSelect from "./partials/single";
    import HmGroupedSelect from "./partials/grouped";
    import MixinState from "./../mixins/MixinState";

    export default {
        name: "HmSelect",
        components: { HmErrors, HmSingleSelect, HmGroupedSelect },
        mixins: [MixinState],
        props: {
            name: {
                type: String,
                required: true
            },
            options: {
                type: Object,
                default: () => {}
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
                selected: null,
                label: this.attribs.label || null,
                description: this.attribs.description || null,
                required: this.attribs.required || false,
                disabled: this.attribs.disabled || false,
                multiple: this.attribs.multiple || false,
                formId: this.attribs.formId || null,
                elements: this.options.items || {},
                sortObj: {},
                refresh: this.attribs.refresh,
                attribsClass: this.attribs.class || ''
            };
        },
        computed: {
            isGrouped() {
                if (!this.elements) return false;

                for (let key in this.elements) {
                    if (this.elements.hasOwnProperty(key))
                        return typeof this.elements[key] === "object";
                }

                return false;
            }
        },
        created() {
            this.updateValue(this.options.selected);
        },
        watch: {
          selected: function (value) {
            this.$nextTick(function () {
              const valueInput = this.$refs.hiddenValueInput,
                  event = valueInput.dispatchEvent(new Event("change"));

              if (event) {} //prevent
            });
          }
        },
        methods: {
            updateValue(value) {
                if (!value || !value.length) return;
                this.mixinStateUpdate("selected", value);
            },
        },
        mounted() {
        }
    };
</script>
