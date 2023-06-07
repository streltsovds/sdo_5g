<template>
  <v-text-field
    class="label-active"
    ref="textFieldEl"
    v-model="value"
    v-bind="passwordFieldProps"
    @change="onChange"
    @input="onChange"
  >
    <!--
        :append-icon="appendIcon"

      <v-icon
        v-if="value"
        slot="append"
      >
        {{ }}
      </v-icon>
    -->

    <template v-if="value" v-slot:append>
      <!--    <template-->
      <!--      v-if="value"-->
      <!--      v-slot:append="slotProps"-->
      <!--    >-->
      <!--      {{ slotProps.color }}-->
      <!-- Иконки показа/скрытия пароля -->
      <div
        @click="showText = !showText"
        style="
          display: flex;
          height: 24px;
          align-items: center;
          cursor: pointer;
        "
      >
        <svg-icon
          :name="visibilityIconName"
          :title="visibilityIconTitle"
          :color="iconColor()"
          :style="visibilityIconStyle"
        >
        </svg-icon>
      </div>
    </template>
  </v-text-field>
</template>

<script>
import svgIcon from "@/components/icons/svgIcon";

export default {
  components: {
    svgIcon
  },
  props: {
    element: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      value: this.element.value,
      showText: false,
      rules: [
        v =>
          !!v ||
          (this.label
            ? `Поле "${this.label}" необходимо заполнить.`
            : "Это поле необходимо заполнить.")
      ]
    };
  },
  computed: {
    visibilityIconName() {
      return this.showText ? "visible" : "unvisible";
    },
    visibilityIconTitle() {
      return this.showText ? "Скрыть" : "Показать";
    },
    visibilityIconStyle() {
      return this.showText ? "" : "margin-top: -2px";
    },
    label() {
      if (!this.element.label) return false;
      let label = this.element.label;

      if (label.indexOf(":")) {
        label = label.split(":");
        label = label.join("");
      }
      return label;
    },
    // appendIcon() {
    //   if (this.value) return this.showText ? "visibility_off" : "visibility";
    //   return null;
    // },
    passwordFieldProps() {
      let props = {
        name: this.element.name,
        label: this.element.label,
        required: this.element.required,
        type: this.showText ? "text" : "password",
        autocomplete: "current-password",
        placeholder: " "
      };
      if (this.element.description) props["hint"] = this.element.description;
      if (this.element.required) props["rules"] = this.rules;
      if (this.element.prependIcon) props["prependIcon"] = this.element.prependIcon;
      return props;
    }
  },
  mounted() {
    let obj = {};
    obj[this.element.name] = this.value;
    this.$emit("field-input", obj);
  },
  methods: {
    onChange() {
      let obj = {};
      obj[this.element.name] = this.value;
      this.$emit("field-input", obj);
    },
    iconColor() {
      let el = this.$refs.textFieldEl.$el;
      let styles = getComputedStyle(el);
      return styles.color || "#000";
    }
  }
};
</script>

<style></style>
