<template>
<!--  :nudge-right="40"-->
<!--  transition="scale-transition"-->
<!--  max-width="290px"-->
<!--  min-width="290px"-->

  <v-menu
    v-model="opened"
    v-bind="menuProps"
    :close-on-content-click="false"
    content-class="hm-rubricator-grid-button__menu-content"
    eager
    min-width="50%"
    nudge-bottom="2"
    offset-y
  >
    <template v-slot:activator="{ on: onMenuActivate }">
      <v-tooltip bottom>
        <template v-slot:activator="{ on: onTooltip }">
<!--          v-on="{...onMenuActivate, ...onTooltip}"-->
<!--          v-on="{...onTooltip}"-->
<!--          :value="opened"-->
          <hm-toggle-button
            class="hm-rubricator-grid-button"
            v-bind="$attrs"
            v-on="{...onMenuActivate, ...onTooltip}"
            :label="buttonText"
            :style="targetStyle"
            svg-icon-name="org-structure"
            max-width="350"
            :value="!!Object.keys(valueCurrent).length"
          ></hm-toggle-button>
        </template>

        <span>{{ tooltipText }}</span>
      </v-tooltip>
    </template>

    <hm-btn-close @click="onBtnCloseClick" />

    <div class="hm-rubricator-grid-button__menu-content__scrollable hm__scrollbar">
      <hm-rubricator
        :auto-wrap="false"
        v-bind="rubricatorProps"
        :value="valueCurrent"
        @input="onRubricatorSelect"
        @update:selected-value-tree-path="selectedValueTreePath = $event"
      />
    </div>
  </v-menu>

</template>

<script>
import HmToggleButton from "@/components/controls/hm-toggle-button/index";
import HmRubricator from "./index";
import HmBtnClose from "@/components/helpers/hm-btn-close"

export default {
  name: "HmRubricatorGridButton",
  components: { HmBtnClose, HmToggleButton, HmRubricator },

  inheritAttrs: false,

  props: {
    autoOpen: {
      type: Boolean,
      default: false,
    },
    value: {
      type: Object,
      default: null,
    },
    label: {
      type: String,
      default: "Выбор элемента иерархии"
    },

    /** @see index.vue */
    rubricatorProps: {
      type: Object,
      default: function() {
        return {};
      },
    },

    menuProps: {
      type: Object,
      default: function() {
        return {};
      },
    },

    // https://github.com/vuejs/vue/issues/6144
    targetStyle: {
      type: null,
      default: null,
    },
  },
  data() {
    return  {
      opened: false,
      valueDirty: null,
      selectedValueTreePath: [],
    };
  },
  computed: {
    tooltipText() {
      // if (!this.valueCurrent) {
      if (!(this.selectedValueTreePath && this.selectedValueTreePath[0])) {
        return this._('Нажмите для выбора текущего элемента в структуре');
      }
      // return this.valueCurrent[0] ? (this._('Выбрано') + ': ' + this.valueCurrent[0].title) : null;

      let treePathParts = this.selectedValueTreePath.map((item) => { return item.title});
      let treePath = treePathParts.join(" > ");

      return this._('Выбрано') + ': ' + treePath;
    },
    buttonText() {
      return this._(this.valueCurrent.title || this.label);
    },
    valueCurrent() {
      return this.valueDirty || this.value || {};
    },
  },
  watch: {
    value(newValue, oldValue) {

      if (newValue && oldValue && newValue.key !== oldValue.key) {
        this.valueDirty = null;
      }
    }
  },
  mounted() {
  },
  created() {
    if (this.autoOpen && !this.value) {
      this.opened = true;
    }
  },
  methods: {
    onBtnCloseClick() {
      this.opened = false;
    },
    onRubricatorSelect(value) {
      this.valueDirty = value;
      this.opened = false;
      this.$emit('input', value);
    }
  },
}
</script>

<style lang="sass">
.hm-rubricator-grid-button
  display: inline-block

  &__menu-content
    width: 100%
    overflow: auto
    background-color: #fff
    max-height: 60vh

    @media (max-height: 499px)
      height: 200px

    @media (max-width: 599px)
      max-width: 100% !important
      left: 0 !important

    &__scrollable
      padding: 26px
      overflow-y: auto
      height: 100%

      @media (max-width: 599px)
        padding: 16px
        padding-right: 34px

  &__close
    position: absolute
    right: 35px
    top: 20px
    padding: 8px
    z-index: 100

    @media (max-width: 599px)
      right: 0

</style>
