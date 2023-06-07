/**
 * Suppresses "style tag in template" error
 *
 * https://stackoverflow.com/a/49517585
 **/
export default {
  name: "VStyle",
  render: function(createElement) {
    return createElement("style", this.$slots.default);
  },
};
