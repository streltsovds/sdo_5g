import Vue from "vue";

const MixinState = {
  data() {
    return {
      mixinStateNameValueField: null
    };
  },
  computed: {
    mixinStateSetActionName() {
      if (!this.formId || !this.name) return null;
      return `${this.formId}/set_${this.name}`;
    },
    mixinStateSubjectsElementValue() {
      if (!this.formId || !this.name || !this.$store.state[this.formId])
        return null;
      return this.$store.state[this.formId][this.name];
    }
  },
  watch: {
    mixinStateSubjectsElementValue(v) {
      this[this.mixinStateNameValueField] = v;
    }
  },
  methods: {
    mixinStateUpdate(field, v) {
      this.mixinStateNameValueField = field;
      if (
        this.$store &&
        this.$store._actions &&
        this.$store._actions[this.mixinStateSetActionName] === undefined
      ) {
        // return (this[this.mixinStateNameValueField] = v);
        Vue.set(this, this.mixinStateNameValueField, v);
        return v;
      }

      let payload = [];
      payload[this.name] = v;
      this.mixinStateDispatchSetAction(payload);
    },
    mixinStateDispatchSetAction(payload) {
      if (!this.mixinStateSetActionName) return;
      if (!this.$store) return;

      this.$store.dispatch(this.mixinStateSetActionName, payload);
    }
  }
};

export default MixinState;
