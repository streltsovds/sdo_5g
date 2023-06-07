import kebabCase from "lodash/kebabCase";

/**
 * Сделано по аналогии с vuetify-theme-stylesheet
 *
 * для задания стилем цвета из конфига, который общий для всех компонентов (не настраивается)
 */
export default {
  name: "VStyleHeadOnce",
  props: {
    id: {
      type: String,
      default: null,
    },
  },
  computed: {
    /**
     * Если id не задан, получать из родительского компонента
     */
    _id() {
      return this.id || "style-" + kebabCase(this.$parent.$options.name);
    },
    slotContent() {
      return this.$scopedSlots.default()[0].text;
    },
  },
  render() {
    let existingStyle = document.getElementById(this._id);

    /**
     * TODO add checksum comparison ?
     * TODO add data-usage-count attribute and delete on 0?
     **/
    if (!existingStyle) {
      let newHeaderStyleEl = document.createElement("style");
      newHeaderStyleEl.type = "text/css";
      newHeaderStyleEl.id = this._id;

      // текст содержимого
      newHeaderStyleEl.textContent = this.slotContent;

      document.head.appendChild(newHeaderStyleEl);
    }

    // doesn't work
    return '<!-- ' + this.name + ': see <style id="' + this._id + '"> in header -->';
  },
};
