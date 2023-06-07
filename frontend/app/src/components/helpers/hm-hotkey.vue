<template>
  <v-tooltip class="hm-hotkey" :v-show="slotUsed" bottom>
    <template
      v-slot:activator="{ on: onTooltip }"
    >
      <div v-on="onTooltip">
        <slot />
      </div>
    </template>

    <div class="hm-hotkey__tooltip">{{ _(tooltip) }} [{{ keyFirst }}]</div>
  </v-tooltip>
</template>

<script>
import isHotkey from "is-hotkey";
import debounce from "lodash/debounce"

/**
 * На основе https://github.com/lupas/vue-keypress/blob/master/src/index.vue
 */
export default {
  name: "HmHotkey",
  props: {
    /** see https://www.npmjs.com/package/is-hotkey */
    keys: {
      type: String,
      default: "Ctrl+Shift+F1|Ctrl+F1",
    },
    /** отключить срабатывание на <input>'ах */
    filterTarget: {
      type: Boolean,
      default: false,
    },
    /**
     * Некоторые клавиши нельзя поймать по событию keypress!
     *
     * key event: keydown, keypress, keyup
     **/
    onEventName: {
      type: String,
      default: "keypress",
    },
    /** prevent default event. TODO не работает, как хотелось бы  */
    preventDefault: {
      type: Boolean,
      default: false,
    },
    /** event.key instead of event.which */
    byKey: {
      type: Boolean,
      default: true,
    },
    tooltip: {
      type: String,
      default: null,
    },
    /** TODO не работает, как хотелось бы */
    topPriority: {
      type: Boolean,
      default: false,
    },
    debounceMs: {
      type: Number,
      default: 200,
    },
  },
  data() {
    return {
      keyEventHandler: null,
    }
  },
  computed: {
    slotUsed() {
      return !!this.$slots.default;
    },
    keysArray() {
      return this.keys.split("|");
    },
    keyFirst() {
      return this.keysArray[0];
    },
    fnHotkeyCheck() {
      return isHotkey(this.keysArray, { byKey: this.byKey });
    },
  },
  mounted() {
    this.initKeyEventHandler();
    window.addEventListener(this.onEventName, this.keyEventHandler, {capture: this.topPriority});
  },
  destroyed() {
    window.removeEventListener(this.onEventName, this.keyEventHandler, {capture: this.topPriority});
  },
  methods: {
    initKeyEventHandler() {
      this.keyEventHandler = debounce(
        (event) => {
          // console.log(event);

          if (this.fnHotkeyCheck(event)) {
            if (!this.filterTarget || this.targetAcceptable(event)) {

              if (this.preventDefault) {
                event.preventDefault();
                event.stopPropagation();
              }

              this.$emit("pressed", event);
            }
          }
        },
        this.debounceMs
      );
    },
    targetAcceptable(event) {
      // always uppercase for DOM elements
      let tagName = event.target.tagName;

      // console.log(tagName);

      if (tagName === "INPUT") {
        return false;
      }
      return true;
    },
  },
};
</script>
