<template>
  <div class="hm-stepper">

    <!--    <v-stepper v-model="stepper" vertical>-->
    <!-- TODO stepper is undefined -->

    <v-stepper
      v-model="active"
      vertical
      :style="{background: themeColors.contentColor}">
      <div v-for="(step, key) in _steps" :key="key" class="hm-stepper_step">
        <v-stepper-step
          editable
          :complete="active > key + 1"
          :step="key + 1"
          :rules="[() => step.isValid]"
        >
          <span v-if="step.label" v-text="step.label"></span>
        </v-stepper-step>

        <v-stepper-content :step="key + 1">
          <div class="hm-stepper_step-content">
            <hm-dependency :template="step.content" />
          </div>
        </v-stepper-content>
      </div>
    </v-stepper>
  </div>
</template>
<script>
import HmDependency from "./../../helpers/hm-dependency";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
export default {
  components: { HmDependency },
  mixins: [VueMixinConfigColors],
  props: {
    steps: {
      type: Object,
      default: () => {}
    }
  },
  computed:{
    _steps(){
      return Object.values(this.steps).filter(step => step.content);
    }
  },
  data() {
    return {
      active: 1
    };
  }
};
</script>
<style lang="scss">
.hm-stepper_step {

  @media screen and (max-width: 599px) {
    .v-stepper__content {
      // Верх и низ остались из vuetify.css, лево-право убираем на малых разрешениях
      margin: -8px 0 -16px !important;
      padding: 16px 25px 16px !important;
    }
  }

  .v-stepper__label {
    font-size: 1.1rem;
    font-weight: 400;
  }

  .hm-stepper_step-content {
    padding-bottom: 26px !important;
  }

  .v-stepper__step {
    padding: 24px;
    background-color: #f5f5f5;
    .v-stepper__step__step {
      .v-icon {
        font-size: 1rem;
      }
    }
  }
}

.hm-stepper_step-content {
  padding-left: 2px;
  padding-right: 2px;
}
</style>
