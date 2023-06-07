export default {
  functional: true,
  props: {
    items: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  render: (h, context) => {
    return context.props.items
      .filter(x => x.tag)
      .map(elm => h("swiper-slide", {}, [elm]));
  }
};
