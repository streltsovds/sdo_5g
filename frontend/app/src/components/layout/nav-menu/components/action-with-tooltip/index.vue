<template>
  <v-tooltip class="hm-main-nav-menu__item__tooltip"
             :open-delay="tooltipDelay"
             top
             content-class="hm-main-nav-menu__item__tooltip__content"
             nudge-bottom="20"
  >
    <template v-slot:activator="{ on: tooltipOn, attrs: tooltipAttrs }">
      <v-list-item-action
        class="hm-main-nav-menu__item__action"
        v-bind="tooltipAttrs"
        v-on="tooltipOn"
      >
        <svg-icon :name="iconName" title=""/>
      </v-list-item-action>
    </template>
    <span class="hm-main-nav-menu__item__tooltip__text">
      {{ label }}
    </span>
  </v-tooltip>
</template>

<script lang="ts">
import Vue from 'vue';
import SvgIcon from "@/components/icons/svgIcon.vue";

/**
 * action-иконка, текст только в подсказке, для свёрнутого меню
 */
export default Vue.extend({
  name: 'HmMainNavMenuActionWithTooltip',
  components: {
    SvgIcon
  },
  props: {
    iconName: {
      type: String,
      default: '',
    },
    label: {
      type: String,
      default: '',
    },
    tooltipDelay: {
      type: Number,
      default: 300,
    }
  },
  computed: {},
  methods: {},
});
</script>

<style lang="sass" src="./styles.sass"/>
