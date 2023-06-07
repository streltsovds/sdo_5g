import MixinChartLegends from "./../partials/legends";

export const Legend = {
  components: { MixinChartLegends },
  data() {
    return {
      mixinLegend: null
    };
  },
  methods: {
    setLegend(items) {
      if (items.length === 0) return;
      this.mixinLegend = items.map(item => {
        return {
          id: item.id,
          label: item.label,
          color: item.color,
          enabled: item.enabled
        };
      });
    },
    toggleEnabled(id, items) {
      let itemId = items.findIndex(item => item.id === id);
      if (itemId === -1) return;

      items[itemId].enabled = !items[itemId].enabled;
      this.mixinLegend[itemId].enabled = items[itemId].enabled;
    }
  }
};
