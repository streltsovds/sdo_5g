<template>
  <v-expansion-panel class="hm-panels">
    <v-expansion-panel-content
      v-for="(panel, key) in filteredPanels"
      :key="key"
    >
      <div slot="header">{{ panel.name }}</div>
      <hm-dependency :template="panel.content"></hm-dependency>
    </v-expansion-panel-content>
  </v-expansion-panel>
</template>
<script>
import HmDependency from "@/components/helpers/hm-dependency/index";
export default {
  components: { HmDependency },
  props: {
    panels: {
      type: Array,
      default: () => []
    }
  },
  computed: {
    filteredPanels() {
      return this.panels.filter(panel => panel.name && panel.content);
    }
  }
};
</script>
