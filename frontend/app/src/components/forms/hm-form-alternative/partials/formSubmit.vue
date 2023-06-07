<template>
  <v-btn
    :id="element.id"
    :loading="isLoading"
    :color="color"
    type="submit"
    :name="element.name"
    dark
  >
    {{ label }}
  </v-btn>
</template>

<script>
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";

// TODO переделать в FormButton (?)
export default {
  mixins: [VueMixinConfigColors],
  props: {
    element: {
      type: Object,
      default: () => ({})
    },
    isSubmitting: {
      type: Boolean,
      default: () => false
    }
  },
  computed: {
    label() {
      return this.element.label ? this.element.label : "Отправить";
    },
    isLoading() {
      return this.isSubmitting;
    },
    color() {
      // TODO ввести поддержку element.state из HM_VueForm::ELEMENT_STATE_*
      // return "primary";
      // return "warning";
      return this.colors.buttonDefault;
    }
  }
};
</script>

<style></style>
