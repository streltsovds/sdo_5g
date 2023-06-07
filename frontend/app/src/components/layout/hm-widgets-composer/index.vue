<template>
  <div class="hm-widgets-composer">
    <div
      v-for="(widgetContainer, widgetContainerIndex) in widgetsContainers"
      :key="widgetContainerIndex + widgetContainer.type"
      :class="[
        'hm-widgets-container',
        'hm-widgets-container-' + widgetContainer.type,
      ].join(' ')"
    >
      <div
        v-for="widget in widgetContainer.widgets"
        :key="widgetContainerIndex + widgetContainer.cssClass + '__' + widget.y + widget.name"
        :class="addCssClassesPrefixed(
          'hm-widget-layout',
          '--' + (widget.layout || 'default'),
          {
            ['--no-border']: !widget.showBorder,
          },

          // TODO ???
          'widgets__height',
        )"
      >
        <slot name="widget" :widget="widget" />
      </div>
    </div>
  </div>
</template>

<script>
import WidgetsToContainers from "./WidgetsToContainers";

import addCssClassesPrefixed from "../../../utilities/addCssClassesPrefixed";

export default {
  name: "HmWidgetsComposer",
  props: {
    widgets: {
      type: Array,
      default: () => {
        return [];
      },
    },
  },
  data() {
    return {
      addCssClassesPrefixed
    };
  },
  computed: {
    // сгруппированы по контейнерам
    widgetsContainers() {
      let widgetsToContainers = new WidgetsToContainers();
      return widgetsToContainers.run(this.widgets);
    },
  },
    mounted() {
    // console.warn('HmWidgetsComposer: widgets', this.widgets)
    }
};
</script>

<style lang="scss">

</style>
